<?php
/**
 * ApiController
 * This controller defines the APIs
 */
namespace app\ApiController;
use app\ApiController as Api;
use DbHandler as DbHandler;
use PlugController as PlugController;
use SensorController as SensorController;

//This API Controller controls all the API Behavior
 class ApiController 
{
	public function setup()
	{
	
	}

	public function listApi()
	{
		$db = new DbHandler();

		$devices = $db->getAllDevices();
		$macs = Api\ApiController::getArp();
		$activeDevices = [];
		foreach ($devices as $device){
			if( in_array($device['device_MAC'], $macs)){

				array_push($activeDevices, $device);
			}
		}
		var_dump($activeDevices);
		exit();
			if(isset($_GET['id'])){
				$id = $_GET['id'];
				
				$mac = $db->getDeviceMac($_GET['id']);

		 		$value = $db->getDeviceInfo($mac);
			}
	 	return $value;
	}
	public function getArp()
	{
		$a=shell_exec("sh /var/www/html/getarp.sh");
		$file_read = fopen(__DIR__."/../getarp.txt", "r") or die("Unable to open the file");
		$value = array();
		while(!feof($file_read)){
			$line_of_file = fgets($file_read);
		    $processed = preg_replace('!\s+!', ' ', $line_of_file);
		    if(strpos($processed, 'incomplete') === false)
			{
		    	$exploded = explode(" ", $processed);
		        if(isset($exploded[2]))
				{
					array_push($value, $exploded[2]);
				}
		        	
		    }
		}
		return $value;
	}
	public function setDeviceStatus($id, $status)
	{
			$db = new DbHandler();
			$plug = new PlugController;
			
			$plug->init($db->getDeviceMac($id));

			if($status == 1){
				//Turning a plug on
				$plug->turnOn();
			}
			else if($status == 0){
				//Turning a plug off
				$plug->turnOff();
			}else{
				echo "DIDNT GET INTO EITHER";
			}
	
	}
	public function getDeviceStatus($id)
	{
		$db = new DbHandler();

		if ($db->getDeviceType($id) == "Sensor"){
			$sensor = new SensorController;
			$sensor->init($db->getDeviceMac($id));
			$sensor->getSensorStatus();
		
		}else{
			echo "Its not a sensor";
		}
		
		
	}
}
