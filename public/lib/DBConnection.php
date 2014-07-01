<?php
require_once("LocalConfig.php");
require_once(PATH_TO_ROOT."lib/Constants.php");

class DBConnection
{
	// Hold an instance of the class
	var $instance;
	var $connection;

	var $hostname;
	var $username;
	var $password;
	var $dbname;

	var $previousDB = array();

	// A  constructor; prevents direct creation of object
	function DBConnection()
	{
		$this->connect(DB_HOST,DB_USER,DB_PASS,DB_NAME);
	}

	function connect($hostname,$username,$password,$dbname)
	{
		if($this->isConnected())
		$this->close();

		$this->hostname = $hostname;
		$this->username = $username;
		$this->password = $password;
		$this->dbname   = $dbname;

		$this->connection = mysql_connect($hostname,$username,$password,true);
		if(!$this->connection)
		{
			trigger_error("Unable to connection to MySQL server on $hostname",E_USER_ERROR);
		}

		$selectResult = mysql_select_db($dbname,$this->connection);
		if($selectResult !== true)
		{
			trigger_error("Database $dbname could not be selected",E_USER_ERROR);
		}
	}

	// The singleton method
//	function singleton()
//	{
//		if (!isset(self::$instance))
//		{
//			$c = __CLASS__;
//			self::$instance = new $c;
//		}
//
//		return self::$instance;
//	}

	function isConnected()
	{
		if(is_resource($this->connection))
		{
			return true;
		}

		return false;
	}

	function selectDB($dbname)
	{
		if($this->dbname == $dbname)
		return;

		$selectResult = mysql_select_db($dbname,$this->connection);
		if($selectResult !== true)
		{
			trigger_error("Database $dbname could not be selected",E_USER_ERROR);
		}
		else
		{
			$count = array_push($this->previousDB,$this->dbname);
			if($count > 10)
			{
				array_shift($this->previousDB);
			}

			$this->dbname = $dbname;
		}
	}

	function execQuery($query)
	{
		if(!is_resource($this->connection))
		{
			trigger_error("You must connect to a database before executing a query",E_USER_ERROR);
		}

		$requestURI = $_SERVER['REQUEST_URI'];
		$result = mysql_query($query,$this->connection);
		
		if($result === false)
		{
			trigger_error("Invalid Query: ".mysql_error()."\nWholeQuery: $query");
		}

		return $result;
	}

	function selectRow($query)
	{
		$result = $this->execQuery($query);
		$row = mysql_fetch_assoc($result);
		mysql_free_result($result);

		return $row;
	}

	function selectArray($query)
	{
		$result = $this->execQuery($query);
		$rows = array();

		while(($row = mysql_fetch_assoc($result)) !== false)
		{
			$rows[] = $row;
		}

		mysql_free_result($result);
		return $rows;
	}

	function selectColumn($query,$columnName)
	{
		$result = $this->execQuery($query);
		$column = array();

		while(($row = mysql_fetch_assoc($result)) !== false)
		{
			$column[] = $row[$columnName];
		}

		mysql_free_result($result);
		return $column;
	}

	function selectValue($query,$fieldName)
	{
		$result = $this->execQuery($query);
		$row = mysql_fetch_assoc($result);

		if($row === false)
		{
			return false;
		}

		mysql_free_result($result);
		return $row[$fieldName];
	}

	function insertID()
	{
		if(is_resource($this->connection))
		{
			return mysql_insert_id($this->connection);
		}

		return FALSE;
	}

	function close()
	{
		if(is_resource($this->connection))
		{
			mysql_close($this->connection);
		}
	}

	function revertDB()
	{
		if(sizeof($this->previousDB) > 0)
		{
			$dbname = array_pop($this->previousDB);
			if($this->dbname == $dbname)
			return;

			$selectResult = mysql_select_db($dbname,$this->connection);
			if($selectResult !== true)
			{
				trigger_error("Database $dbname could not be selected",E_USER_ERROR);
			}
			else
			{
				$this->dbname = $dbname;
			}
		}
	}

	function currentSelectedDB()
	{
		$r = mysql_query("SELECT DATABASE()",$this->connection) or die(mysql_error());
		return mysql_result($r,0);
	}

	function getDBName()
	{
		return $this->dbname;
	}

	// Prevent users to clone the instance
	function __clone()
	{
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}
}

?>
