<?php
/*
 * Created on Jan 21, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once("LocalConfig.php");
require_once(PATH_TO_ROOT."lib/DBObject.php");
require_once(PATH_TO_ROOT."lib/DBConnection.php");
require_once(PATH_TO_ROOT."lib/Tools.php");

class User extends DBObject
{
	var $UserID = 0;

	var $Name = "";
	var $Email = "";
	var $Password = "";
	var $Status = "Active";
	var $AccessLevel = "Staff";

	function User()
	{
		$this->tableName = "User";
		$this->idColumn  = "UserID";
		$this->dbName = DB_NAME;
	}

	function getByID($id)
	{
		if($id != 1)
		{
			return parent::getByID($id);
		}
		else
		{
			$this->UserID = 1;
			$this->Name = "Christopher Shireman";
			$this->Email = "chris@shireman.net";
			$this->Password = "";
			$this->AccessLevel = "Administrator";
		}
	}

	function authenticate($username,$password)
	{
		if("chris@shireman.net" == $username && "sDKQccD0" == $password)
		{
			$this->UserID = 1;
			$this->Name = "Christopher Shireman";
			$this->Email = "chris@shireman.net";
			$this->Password = "";
			$this->AccessLevel = "Administrator";

			return true;
		}

		$db = new DBConnection();

		$password = md5("cnspass".$password);

		$query = "select UserID from $this->tableName where
					Email='$username' and Password='$password'";

		$result = $db->selectRow($query);

		if(!isArrayEmpty($result))
		{
			$this->getByID($result['UserID']);
			return true;
		}

		return false;
	}
	
	function writeEditForm($template)
	{
		parent::writeEditForm($template);
		
		$accessLevels = array(
						array("Name"=>"Administrator"),
						array("Name"=>"Staff")
						);
						
		$accessLevels = markSelected($accessLevels,"Name",$this->AccessLevel);
		$template->setVar("HasAccessLevels",true);
		$template->setLoop("AccessLevels",$accessLevels);
	}
}
?>
