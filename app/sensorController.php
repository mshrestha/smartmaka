<?php
/**
 * SensorController
 * This controller defines the Sensors
 */
//namespace app\Sensor;

//This Device Controller controls all the Devices
 class SensorController extends DeviceController
{
	
	public function getSensorStatus()
	{
		$mqttConfigs = require __DIR__ . '/../includes/mqtt.config.php';

		
		$host = $mqttConfigs['host'];
        $port = $mqttConfigs['port'];
        $username = $mqttConfigs['username'];
        $password = $mqttConfigs['password'];
			  
		$mqtt = new phpMQTT($host, $port, "RESTAPI".rand()); 
		if(!$mqtt->connect()){
			exit(1);
		}
		$topics[$this->mac .'/temp'] = array("qos"=>0, "function"=>"procmsg");

		$mqtt->subscribe($topics,1);
		while($mqtt->proc())
		{
			
		}
		$mqtt->close();
		
	}
}
