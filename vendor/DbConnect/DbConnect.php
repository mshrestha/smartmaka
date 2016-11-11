<?php

class DBConnect
{
	private $conn;

	function __construct(){}

	public function Connect()
	{
		$configs = require __DIR__ . '/../../includes/app.config.php';
		$host = $configs['host'];
		$username = $configs['username'];
		$password = $configs['password'];
		$database = $configs['database'];
		
		$this->conn = new mysqli($host, $username, $password, $database);
		if($this->conn->connect_errno > 0)
		{
			trigger_error('SMARTMAKA ERROR.
				ERROR TITLE: Connection Failed.
				ERROR DESCRIPTION: '.$this->conn->connect_error, E_USER_ERROR);
		}
		else
		{
			// echo 'Ok';
			return $this->conn;
		}
	}
}

?>