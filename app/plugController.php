<?php
use Kazi\Application as Kazi;
/**
 * DeviceController
 * This controller defines the APIs
 */
//namespace app\Device;

//This Device Controller controls all the Devices
 class PlugController extends DeviceController
{
	/**
	 * Turns on the plug device
	 * Sets status to 1
	 */
	public function turnOn()
	{
			$this->switchPlug(0);
			$this->status = 1;
	
	}
	/**
	 * Turns off the plug device
	 * Sets Status to 0
	 */
	public function turnOff()
	{
		$this->switchPlug(1);
		$this->status = 0;
	
	}
	public function switchPlug($status)
	{

		$mqttConfigs = require __DIR__ . '/../includes/mqtt.config.php';

		
		$host = $mqttConfigs['host'];
        $port = $mqttConfigs['port'];
        $username = $mqttConfigs['username'];
        $password = $mqttConfigs['password'];
        $turnon = $status;
		
        $mac1 = $this->mac . "/plugsignal";
        $mqtt = new phpMQTT($host, $port, "ClientID".rand());
  
          if ($mqtt->connect(true,NULL,$username,$password)) {
                   $mqtt->publish($mac1, $turnon, 0);
                   $mqtt->close();
              }else{
                   echo "Fail or time out<br />";
              }
	}
}
