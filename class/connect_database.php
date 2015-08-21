<?php

class Connection_database
{
	protected $mysqli = null;
	
	function __construct($dsn='https://keiziban-tomoya-matsumoto-4.c9.io',$name='keiziban',$user='root',$pass='',$dbport=3306)
	{
		$servername = getenv('IP');
	    $username = getenv('C9_USER');
		$password = "";
    	$database = "keiziban";
    	$dbport = 3306;

    	// Create connection
    	$this->mysqli = new mysqli($servername, $username, $password, $database, $dbport);
    	
		if ($this->mysqli->connect_error) {
			echo $this->mysqli->connect_error;
			exit();
		} else {
			$this->mysqli->set_charset("utf8");
		}
	}

	function __destruct()
	{
		//$this->mysqli->close();
	}

	public function getConnection(){
		return $this->mysqli;
	}

}
