<?php require("phpMQTT.php");
 
     $host = "127.0.0.1";
     $port = "1883";
     $username = "";
     $password = "";
     $turnon = "0";
     $mac1 = "5c:cf:7f:80:39:dd/plugsignal";
    //MQTT client id to use for the device. "" will generate a client id automatically
      $mqtt = new phpMQTT($host, $port, "ClientID".rand());
  
        if ($mqtt->connect(true,NULL,$username,$password)) {
                 $mqtt->publish($mac1, $turnon, 0);
                 $mqtt->close();
            }else{
                 echo "Fail or time out<br />";
            }
?>
