<?php
/**
 * Kazi Framework 
 *
 * @copyright Copyright (c) 2005-2015 Kazi Studios
 */

namespace Kazi\Application;


/**
 * Main application class for invoking applications
 *
 * At this point, this just invokes the following
 *  - Runs the application
 *  - Routes requests
 * The most common workflow is:
 */

class Application
{

	/**
	 * Protected application configs
	 *
	 * @var array
	 */
	public static $configs = [];
	
    /**
     * Constructor
     * @param Config $configs
	 *
     */
    public function __construct() 
	{
	
    }
    /**
     * Retrieve the application configuration
     *
     * @return array|object
     */
    public function getConfig()
    {
		$configs['appConfig'] = require __DIR__ . '/../../includes/app.config.php';
		$configs['mqttConfig'] = require __DIR__ . '/../../includes/mqtt.config.php';
        return $configs;
    }
    /**
     * Run the application
     *
     * @return self
     */
    public function run()
    {
		$config = kazi::getConfig();
        return $config;
    }
	/**
	 * Select All Devices
	 */
	public function getDevices(){
		

		$result = '';
		return $result;
	}
}

