<?php
use Kazi\Application as Kazi;
use app\ApiController as Api;


/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

// Composer autoloading
//include __DIR__ . 'includes/autoload.php';

include __DIR__ . '/vendor/DbConnect/DbHandler.php';
include __DIR__ . '/vendor/kazi/Kazi.Application.php';
include __DIR__ . '/vendor/phpMQTT/phpMQTT.php';
include __DIR__ . '/app/apiController.php';
include __DIR__ . '/app/deviceController.php';
include __DIR__ . '/app/plugController.php';
include __DIR__ . '/app/sensorController.php';



/**** This is an example of registering a plug ***

	$plug1 = new PlugController;
	$plug1->init('5c:cf:7f:80:39:dd');

	//Turning a plug on
	$plug1->turnOn();

	//Getting status of a plug
	echo $plug1->getStatus();
*/

/****************************************************/


$value = Api\ApiController::listApi();

$value = "An error has occurred";
if($_SERVER['REQUEST_METHOD'] === 'GET'){
	$value = Api\ApiController::listApi();
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
	
	$value = "NO ID SPECIFIED";

	if(isset($_POST['id'])){
	$value = "Device Not Accesible";
	
	$id = $_POST['id'];
	
		//Turning Switch on or off
		if(isset($_POST['status'])){
			$status = $_POST['status'];
			$value = Api\ApiController::setDeviceStatus($id, $status);
		}else{
			Api\ApiController::getDeviceStatus($id);
		}
	}
	
}

function procmsg($topic,$msg)
{
	exit(json_encode($msg));
}
//return JSON array
exit(json_encode($value));