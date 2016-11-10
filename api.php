<?php
use app\ApiController;

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
include __DIR__ . '/app/apiController.php';

use app\ApiController as Api;

// This is the API, 2 possibilities: show the list of devices or show a specific device by id.

echo Api\ApiController::listApi();
exit();
function get_app_by_id($id)
{
  $app_info = array();

  // build JSON array.
  switch ($id){
    case 1:
      $app_info = array("app_name" => "Living Room Light", "status" => 0); 
      break;
    case 2:
      $app_info = array("app_name" => "Living Room Temperature", "status" => 26);
      break;
    case 3:
      $app_info = array("app_name" => "Water Pump", "status" => 1);
      break;
    case 4:
      $app_info = array("app_name" => "Water Pump Sensor", "status" => 32 );
      break;
  }

  return $app_info;
}

function get_app_list()
{
  //normally this info would be pulled from a database.
  //build JSON array
  $app_list = array(array("id" => 1, "name" => "Web Demo"), array("id" => 2, "name" => "Audio Countdown"), array("id" => 3, "name" => "The Tab Key"), array("id" => 4, "name" => "Music Sleep Timer")); 

  return $app_list;
}

$possible_url = array("get_app_list", "get_app");

$value = "An error has occurred";
if($_SERVER['REQUEST_METHOD'] === 'GET'){
$value = [["id"=>1, "name"=>"Living Room Lights"], 
          ["id"=>2, "name"=>"Living Room Temperature"],
          ["id"=>3, "name"=>"Water Pump"],
          ["id"=>4, "name"=>"Water Pump Sensor"]
          ];
	if(isset($_GET['id'])){
 		$value = get_app_by_id($_GET['id']);
	}
}
if (isset($_GET["action"]) && in_array($_GET["action"], $possible_url))
{
  switch ($_GET["action"])
    {
      case "GET":
        $value = get_app_list();
        break;
      case "get_app":
        if (isset($_GET["id"]))
          $value = get_app_by_id($_GET["id"]);
        else
          $value = "Missing argument";
        break;
    }
}
if(isset($_POST['id']))
{
    $value = get_app_by_id($_POST['id']);
}
if(isset($_POST['status'])){
	$value = $_POST['status'];
}
//return JSON array
exit(json_encode($value));
?>
