<?php
/**
 * DeviceController
 * This controller defines the APIs
 */
//namespace app\Device;

//This Device Controller controls all the Devices
 class DeviceController
{
	//This variable stores the status of the device
	protected $status;
	
	//This variable stores the device MAC
	protected $mac;
	
	//Device ID
	protected $id;
	
	//Device Name
	protected $name;
	/**
	 * Sets up the status and mac of the device
	 */
	public function init($mac)
	{
		$this->mac = $mac;
		$this->status = 0;
	
	}
	/**
	 * Returns the status of the device
	 */
	public function getStatus()
	{
	 	return $this->status;
	}
}
