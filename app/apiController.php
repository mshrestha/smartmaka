<?php
/**
 * ApiController
 * This controller defines the APIs
 */
namespace app\ApiController;
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

		$value = $db->getAllDevices(); 
			if(isset($_GET['id'])){
				$id = $_GET['id'];
				
				$mac = $db->getDeviceMac($_GET['id']);

		 		$value = $db->getDeviceInfo($mac);
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
