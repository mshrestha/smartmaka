<?php

class DbHandler{
	private $conn;

	function __construct(){
		require_once 'DBConnect.php';
		$db = new DBConnect();
		$this->conn = $db->Connect();
	}
	
	function __destruct(){}

	public function createUser($username, $email, $password, $SSID, $MAC, $IP) {
        require_once '../include/PassHash.php';
        $response = array();
 
        // First check if user already existed in db
        if (!$this->isUserExists($email)) {
            // Generating password hash
            $password_hash = PassHash::hash($password);
 
            // insert query
            $stmt = $this->conn->prepare("INSERT INTO user_login(username, email_id, password) values(?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password_hash);
 
            $result = $stmt->execute();
			$id = $stmt->insert_id;
            $stmt->close();
 
            // Check for successful insertion
            if ($result) {
            	
                // User successfully inserted
                // echo $id;
                if($this->createWIFIProfile($id, $SSID, $MAC, $IP)){
                	return USER_CREATED_SUCCESSFULLY;	
                }else{
                	return USER_CREATE_FAILED;
                }
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }
 
        return $response;
    }

	public function checkLogin($email, $password) {
		require_once '../include/PassHash.php';
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT password FROM user_login WHERE email_id = ?");
 
        $stmt->bind_param("s", $email);
 
        $stmt->execute();
 
        $stmt->bind_result($password_hash);
 
        $stmt->store_result();
 
        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password
 
            $stmt->fetch();
 
            $stmt->close();
 
            if (PassHash::check_password($password_hash, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();
 
            // user not existed with the email
            return FALSE;
        }
    }

	public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT username, email_id FROM user_login WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

	 private function isUserExists($email) {
        $stmt = $this->conn->prepare("SELECT id from user_login WHERE email_id = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    private function isWifiRegistered($MAC){
    	$stmt = $this->conn->prepare("SELECT id from user_wifi WHERE wifi_MAC = ?");
        $stmt->bind_param("s", $MAC);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;	
    }

    public function isTimerRegistered($id){
        // $id = $this->getDeviceID($device_mac);
        $stmt = $this->conn->prepare("SELECT id from user_timer WHERE device_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;   
    }

    public function getWifiID($MAC){
    	$stmt = $this->conn->prepare("SELECT id from user_wifi WHERE wifi_MAC = ?");
        $stmt->bind_param("s", $MAC);
        $stmt->execute();
    	$stmt->bind_result($id);
    	$stmt->store_result();
    	if($stmt->num_rows > 0)
    	{
    		$stmt->fetch();
    	}
        $stmt->close();
        return $id;
    }

    public function getDeviceID($MAC){
        $stmt = $this->conn->prepare("SELECT id from user_device WHERE device_MAC = ?");
        $stmt->bind_param("s", $MAC);
        $stmt->execute();
        $stmt->bind_result($id);
        $stmt->store_result();
        if($stmt->num_rows > 0)
        {
            $stmt->fetch();
        }
        $stmt->close();
        return $id;
    }
	
    public function getDeviceMac($Id){
        $stmt = $this->conn->prepare("SELECT device_MAC from user_device WHERE id = ?");
        $stmt->bind_param("s", $Id);
        $stmt->execute();
        $stmt->bind_result($mac);
        $stmt->store_result();
        if($stmt->num_rows > 0)
        {
            $stmt->fetch();
        }
        $stmt->close();
        return $mac;
    }
	
    public function getDeviceType($Id){
        $stmt = $this->conn->prepare("SELECT device_type from user_device WHERE id = ?");
        $stmt->bind_param("s", $Id);
        $stmt->execute();
        $stmt->bind_result($device_type);
        $stmt->store_result();
        if($stmt->num_rows > 0)
        {
            $stmt->fetch();
        }
        $stmt->close();
        return $device_type;
    }

    public function createWIFIProfile($id, $SSID, $MAC, $IP){
    	if(!$this->isWifiRegistered($MAC)){
    		$stmt = $this->conn->prepare("INSERT INTO user_wifi(wifi_SSID, wifi_MAC, wifi_IP) values(?, ?, ?)");
	        $stmt->bind_param("sss", $SSID, $MAC, $IP);
	        $result = $stmt->execute();
	        $wifi_id = $stmt->insert_id;
	        $stmt->close();
	        if ($result) {
	        	$stmt = $this->conn->prepare("INSERT INTO user_link_wifi(user_id, wifi_id) values(?, ?)");
		        $stmt->bind_param("ii", $id, $wifi_id);
		        $result = $stmt->execute();
		        if($result){
		        	return TRUE;
		        }
		        else{
		        	return FALSE;
		        }
	        }else{
	        	return FALSE;
	        }	
    	}else if($this->isWifiRegistered($MAC)){
    		$wifi_id = $this->getWifiID($MAC);
    		$stmt = $this->conn->prepare("INSERT INTO user_link_wifi(user_id, wifi_id) values(?, ?)");
	        $stmt->bind_param("ii", $id, $wifi_id);
	        $result = $stmt->execute();
	        if($result){
	        	return TRUE;
	        }
	        else{
	        	return FALSE;
	        }
    	}
    	
    }

    private function registerWIFI($SSID, $MAC, $IP){
    	$stmt = $this->conn->prepare("INSERT INTO user_wifi(wifi_SSID, wifi_MAC, wifi_IP) values(?, ?, ?)");
        $stmt->bind_param("sss", $SSID, $MAC, $IP);
        $result = $stmt->execute();
        $wifi_id = $stmt->insert_id;
        $stmt->close();
        if ($result) {
        	return $wifi_id;
        }
    }

    public function updateWifiData($oldMAC, $newSSID, $newMAC, $newIP){
    	$id = $this->getWifiID($oldMAC);
    	$stmt = $this->conn->prepare("UPDATE user_wifi SET wifi_SSID = ?, wifi_MAC = ?, wifi_IP = ? WHERE id = ?");
        $stmt->bind_param("sssi", $newSSID, $newMAC, $newIP, $id);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
        	return TRUE;
        }else{
        	return FALSE;
        }
    }

    public function registerDevice($device_name, $MAC, $status, $device_mac, $device_type, $tank_height, $tank_name){
    	$id = $this->getWifiID($MAC);
    	$stmt = $this->conn->prepare("INSERT INTO user_device(name, wifi_id, status, device_MAC, device_type, tank_height, tank_name) values(?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siissis", $device_name, $id, $status, $device_mac, $device_type, $tank_height, $tank_name);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return TRUE;
        }else{
        	return FALSE;
        }
    }

    public function registerTimer($device_mac, $hour, $minute, $duration, $frequency, $count, $until, $interval, $byday, $end_hour, $end_min){
        $value = array();
        $ind_value = array();
        $id = $this->getDeviceID($device_mac);
        $stmt = $this->conn->prepare("INSERT INTO user_timer(device_id, hour, minute, duration, frequency, count, until, tinterval, byday) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ississsss", $id, $hour, $minute, $duration, $frequency, $count, $until, $interval, $byday);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            if(strlen($hour) == 1){
                $hour = "0".$hour;
            }
            if(strlen($minute) == 1){
                $minute = "0".$minute;
            }
            $ind_value["mqtt_time"]=$hour . ":" . $minute;
            $ind_value["mqtt_endtime"]=$end_hour . ":" . $end_min;
            $ind_value["mqtt_duration"]=$duration;
            $exploded_days = explode(',', $byday);
            for($i = 0; $i < count($exploded_days); $i++){
                $decoded_day = $this->getDay($exploded_days[$i]);
                $ind_value["mqtt_day"][] = $decoded_day;
            }
            $value["value"] = $ind_value;
            $value["error"] = TRUE;
            return $value;
        }else{
            $value['error'] = FALSE;
            return $value;
        }
    }

    public function updateTimer($device_mac, $hour, $minute, $duration, $frequency, $count, $until, $interval, $byday, $end_hour, $end_min){
        $id = $this->getDeviceID($device_mac);
        $stmt = $this->conn->prepare("UPDATE user_timer SET hour = ?, minute = ?, duration = ?, frequency = ?, count = ?, until = ?, tinterval = ?, byday = ?  WHERE device_id = ?");
        // echo $hour.'h<br />'.$minute.'m<br />'.$duration.'dur<br />'.$frequency.'f<br />'.$count.'cnt<br />'.$until.'unt<br />'.$interval.'int<br />'.$byday.'byday<br />'.$id.'d_id<br />';
        $stmt->bind_param("ssisssssi", $hour, $minute, $duration, $frequency, $count, $until, $interval, $byday, $id);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            /*if(strlen($hour) == 1){
                $hour = "0".$hour;
            }
            if(strlen($minute) == 1){
                $minute = "0".$minute;
            }*/
            $ind_value["mqtt_time"]=$this->getCompleteTime($hour, $minute);
            $ind_value["mqtt_endtime"]=$this->getCompleteTime($end_hour, $end_min);
            $ind_value["mqtt_duration"]=$duration;
            $exploded_days = explode(',', $byday);
            for($i = 0; $i < count($exploded_days); $i++){
                $decoded_day = $this->getDay($exploded_days[$i]);
                $ind_value["mqtt_day"][] = $decoded_day;
            }
            $value["value"] = $ind_value;
            $value["error"] = TRUE;
            return $value;
        }else{
            $value['error'] = FALSE;
            return $value;
        }
    }

    private function getCompleteTime($hour, $minute){
        if(strlen($hour) == 1){
            $hour = "0".$hour;
        }
        if(strlen($minute) == 1){
            $minute = "0".$minute;
        }
        return $hour . ":" . $minute;
    }

    private function getDay($token){
        if($token == "SU"){
            return "sun";
        }
        else if($token == "MO"){
            return "mon";
        }
        else if($token == "TU"){
            return "tue";
        }
        else if($token == "WE"){
            return "wed";
        }
        else if($token == "TH"){
            return "thu";
        }
        else if($token == "FR"){
            return "fri";
        }
        else if($token == "SA"){
            return "sat";
        }

    }

    /*public function registerTimer($device_mac, $hour, $minute, $duration, $frequency, $count, $until, $interval, $byday){
        $id = $this->getDeviceID($device_mac);
        echo '<br />'.gettype($id).'-'.gettype($hour).'-'.gettype($minute).'-'.gettype($duration).'-'.gettype($frequency).'-'.gettype($count).'-'.gettype($until).'-'.gettype($interval).'-'.gettype($byday);
        $stmt = $this->conn->prepare("INSERT INTO user_timer(device_id, hour, minute, duration, frequency, count, until, tinterval, byday) values(?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $n = 1;
        $c = "a";
        $stmt->bind_param("ississsss", $n, $c, $c, $n, $c, $c, $c, $c, $c);
        $stmt->bind_param("ississsss", $id, $hour, $minute, $duration, $frequency, $count, $until, $interval, $byday);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            echo $byday;
        }else{
            return FALSE;
        }
    }*/

    public function getTimerInfo($device_mac){
        $id = $this->getDeviceID($device_mac);
        $stmt = $this->conn->prepare("SELECT hour, minute, duration, byday FROM user_timer WHERE device_id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->bind_result($hour, $minute, $duration, $byday);
        $stmt->store_result();
        if($stmt->num_rows == 1){
            $stmt->fetch();
            $ind_value["mqtt_time"]=$this->getCompleteTime($hour, $minute);
            $ind_value["mqtt_duration"]=$duration;
            $ind_value["mqtt_minute"] = $hour * 60 + $minute;
            $exploded_days = explode(',', $byday);
            for($i = 0; $i < count($exploded_days); $i++){
                $decoded_day = $this->getDay($exploded_days[$i]);
                $ind_value["mqtt_day"][] = $decoded_day;
            }
            $value["value"] = $ind_value;
            $value["error"] = FALSE;
            return $value;
        }
        else
        {
            $stmt->close();
            $value['error'] = TRUE;
            return $value;
        }
    }

    public function updateDevice($device_mac, $device_name){
    	$stmt = $this->conn->prepare("UPDATE user_device SET name = ? WHERE device_MAC = ?");
        $stmt->bind_param("ss", $device_name, $device_mac);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
        	return TRUE;
        }else{
        	return FALSE;
        }
    }

	public function updateUser($email, $password){
		require_once '../include/PassHash.php';
		$password_hash = PassHash::hash($password);
 
	    // insert query
	    $stmt = $this->conn->prepare("UPDATE user_login SET password = ? WHERE email_id = ?");
	    $stmt->bind_param("ss", $password_hash, $email);

	    $result = $stmt->execute();
		$id = $stmt->insert_id;
	    $stmt->close();

	    // Check for successful insertion
	    if ($result) {
	    	return TRUE;
	    }else{
	    	return FALSE;
	    }
    }    

    public function getUserInfo($email){
        $response = array();
        $stmt = $this->conn->prepare("SELECT id, username FROM user_login WHERE email_id = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($id, $name);
        
        $stmt->store_result();
        if($stmt->num_rows == 1){
            $stmt->fetch();
            $value = array('id'=>$id, 'name'=>$name, 'email'=>$email);
            $stmt->close();
            // echo json_encode($value);
            return $value;
        }
        else
        {
            $stmt->close();
            return $response;
        }
    }

    public function getDeviceInfo($device_mac){
        $response = array();
        $stmt = $this->conn->prepare("SELECT id, name, status, device_type FROM user_device WHERE device_MAC = ?");
        $stmt->bind_param("i", $device_mac);
		
        $stmt->execute();
		
        $stmt->bind_result($id, $name, $status, $device_type);
        
        $stmt->store_result();

        if($stmt->num_rows > 0){
            $stmt->fetch();
            $value = array('id'=>$id, 'name'=>$name, 'status'=>$status, 'device_mac'=>$device_mac, 'device_type'=>$device_type);
            $stmt->close();
            return $value;
        }
        else
        {
            $stmt->close();
            return $response;
        }
    }
	
	
	public function getAllDevices(){


	        $stmt = $this->conn->prepare("SELECT id, name, status, device_MAC, device_type FROM user_device");
	        $stmt->execute();
	        $stmt->bind_result($id, $name, $status, $device_MAC, $device_type);
	        $stmt->store_result();
	        $value = array();
	        while($stmt->fetch())
	        {
	            $ind_value = array('id'=>$id, 'name'=>$name, 'status'=>$status, 'device_MAC'=>$device_MAC, 'device_type'=>$device_type);
	            array_push($value, $ind_value);
	        }
        
	        $stmt->close();
	        return $value;
	}
	
    public function getAllDeviceInfo($wifiMAC){
        $wifiID = $this->getWifiID($wifiMAC);
        // echo $wifiID;
        $stmt = $this->conn->prepare("SELECT name, status, device_MAC, device_type, tank_height, tank_name FROM user_device WHERE wifi_id = ?");
        $stmt->bind_param("i",$wifiID);
        $stmt->execute();
        $stmt->bind_result($name, $status, $device_MAC, $device_type, $tank_height, $tank_name);
        $stmt->store_result();
        $value = array();
        while($stmt->fetch())
        {
            $ind_value = array('name'=>$name, 'status'=>$status, 'device_MAC'=>$device_MAC, 'device_type'=>$device_type, 'tank_height'=>$tank_height, 'tank_name'=>$tank_name);
            array_push($value, $ind_value);
        }
        
        $stmt->close();
        return $value;
    }

    private function getTankName($device_mac){
        $stmt = $this->conn->prepare("SELECT tank_name from user_device WHERE device_MAC = ?");
        $stmt->bind_param("s", $device_mac);
        $stmt->execute();
        $stmt->bind_result($tank_name);
        $stmt->store_result();
        if($stmt->num_rows > 0)
        {
            $stmt->fetch();
        }
        $stmt->close();
        return $tank_name;   
    }

    public function getSensorPlugMac($device_mac){
        $tank_name = $this->getTankName($device_mac);
        $device_type = "Levelplug";
        $stmt = $this->conn->prepare("SELECT device_MAC FROM user_device WHERE tank_name = ? AND device_type = ?");
        $stmt->bind_param("ss",$tank_name, $device_type);
        $stmt->execute();
        $stmt->bind_result($device_MAC);
        $stmt->store_result();
        $value = array();
        while($stmt->fetch())
        {
            $value = array('plug_mac'=>$device_MAC);
            // array_push($value, $ind_value);
        }
        
        $stmt->close();
        return $value;
    }

    public function switchDeviceStatus($device_MAC, $status){
        $stmt = $this->conn->prepare("UPDATE user_device SET status = ? WHERE device_MAC = ?");
        $stmt->bind_param("is", $status, $device_MAC);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return TRUE;
        }else{
            return FALSE;
        }
    }

    public function deleteDevice($device_MAC){
        $id = $this->getDeviceID($device_MAC);
        $stmt = $this->conn->prepare("DELETE FROM user_device WHERE device_MAC = ?");
        $stmt->bind_param("s", $device_MAC);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            $result1 = $this->deleteTimer($id);
            if($result1){
                return TRUE;
            }
            else{
                return FALSE;
            }
        }else{
            return FALSE;
        }
    }

    public function deleteTimer($id){
        if($this->isTimerRegistered($id)){
            /*$id = $this->getDeviceID($device_MAC);*/
            $stmt = $this->conn->prepare("DELETE FROM user_timer WHERE device_id = ?");
            $stmt->bind_param("i", $id);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return TRUE;
            }else{
                return FALSE;
            }
        }
        else{
            return FALSE;
        }
    }



	/*public function hashSSHA($password)
	{
		$salt = sha1(rand());
		$salt = substr($salt, 0, 10);
		$encrypted = base64_encode(sha1($password.$salt, true).$salt);
		$hash = array("salt" => $salt, "encrypted" => $encrypted);
		return $hash;
	}

	public function checkhashSSHA($salt, $password) {
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
        return $hash;
    }*/
}
?>
