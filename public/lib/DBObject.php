<?php
/*********************************************
* Filename    : DBObject.php
* Author      : Christopher Shireman
* Create Date : 08/17/2005
* Description : A base class for dealing with a database object.
* Change Log  :
*    08/17/2005 - Created File
*********************************************/

require_once("LocalConfig.php");
require_once(PATH_TO_ROOT."lib/Constants.php");
require_once(PATH_TO_ROOT."lib/ErrorHandling.php");
require_once(PATH_TO_ROOT."lib/DBConnection.php");

class DBObject
{
	var $tableName = "TableName";
	var $dbName = "DBName";
	var $idColumn = "IDColumn";

	function DBObject()
	{
	}

	function deleteByID($deleteID)
	{
		if($deleteID)
		{
			$db = new DBConnection();

			$tableName = $this->tableName;
			$dbName = $this->dbName;
			$idColumn = $this->idColumn;

			$db->selectDB($dbName);

			$query = "delete from $tableName where $idColumn=$deleteID";
			$db->execQuery($query);
		}
	}

	function deleteSet($deleteIDArray)
	{
		if(is_array($deleteIDArray) && sizeof($deleteIDArray) > 0)
		{
			$deleteIDs = join(",",$deleteIDArray);

			$db = new DBConnection();

			$tableName = $this->tableName;
			$dbName = $this->dbName;
			$idColumn = $this->idColumn;

			$db->selectDB($dbName);

			if(strlen(trim($deleteIDs)) > 0)
			{
				$query = "delete from $tableName where $idColumn in ($deleteIDs)";
				$db->execQuery($query);
			}
		}
	}

	function getAll($sort=null,$selected=array(),$selectField=null,$sortOrder=null)
	{
		$db = new DBConnection();

		$tableName = $this->tableName;
		$dbName = $this->dbName;

		$db->selectDB($dbName);

		if(is_null($sortOrder))
		{
			$sortOrder = "asc";
		}

		$query = "select * from $tableName order by $sort $sortOrder";

		if(is_null($sort))
		{
			$query = "select * from $tableName";
		}

		$result = $db->selectArray($query);

		if(sizeof($selected) != 0)
		{
			for($i = 0; $i < sizeof($result); $i++)
			{
				if(in_array($result[$i][$selectField],$selected))
				{
					$result[$i]['selected'] = true;
				}
				else
				{
					$result[$i]['selected'] = false;
				}
			}
		}

		return $result;
	}

	function getByID($id)
	{
		$db = new DBConnection();

		$tableName = $this->tableName;
		$dbName = $this->dbName;
		$idColumn = $this->idColumn;

		$db->selectDB($dbName);

		$query = "select * from $tableName where $idColumn=$id";
		$result = $db->selectRow($query);

		if(is_array($result) && sizeof($result) > 0)
		{
			$this->{$idColumn} = $id;

			foreach($result as $name=>$value)
			{
				if($name != $idColumn)
				{
					$this->{$name} = $value;
				}
			}

			return true;
		}
		else
		{
			return false;
		}

	}

	function update()
	{
		$db = new DBConnection();
		$db->selectDB($this->dbName);

		$updateItems = array();

		$objID = 0;

		$fields = get_object_vars($this);
		foreach($fields as $name=>$value)
		{
			if($this->isUsableField($name))
			{
				$value = addslashes($value);
				$updateItems[] = "$name = '$value'";
			}
			elseif($name == $this->idColumn)
			{
				$objID = $value;
			}
		}

		$updateString = join(",", $updateItems);
		$query = "update $this->tableName set $updateString
                  where $this->idColumn=$objID";

		$db->execQuery($query);
	}

	function insert()
	{
		$db = new DBConnection();
		$db->selectDB($this->dbName);

		$fields = get_object_vars($this);
		$insertItems = array();
		foreach($fields as $name=>$value)
		{
			if($this->isUsableField($name))
			{
				$value = addslashes($value);
				$insertItems[] = "$name = '$value'";
			}
		}

		$insertString = join(",", $insertItems);
		$query = "insert into $this->tableName set $insertString";
		$db->execQuery($query);

		$this->{$this->idColumn} = mysql_insert_id();
	}

	function save()
	{
		if($this->{$this->idColumn} != 0)
		{
			$this->update();
		}
		else
		{
			$this->insert();
		}
	}

	function readEditForm($data)
	{
		$fields = get_object_vars($this);
		foreach($fields as $name=>$value)
		{
			if($this->isUsableField($name) && array_key_exists($name,$data))
			{
				$this->{$name} = $data[$name];
			}
		}

		if(array_key_exists("id",$data))
		$this->{$this->idColumn} = $data['id'];

	}

	function writeEditForm(&$template)
	{
		$fields = get_object_vars($this);
		foreach($fields as $name=>$value)
		{
			if($this->isWritableField($name))
			{
				if(is_null($value))
				$value = "";

				$template->setVar($name,stripslashes($value));
			}
		}

		$template->setVar("id",$this->{$this->idColumn});
	}

	function isUsableField($fieldName)
	{
		if("idColumn" == $fieldName || "tableName" == $fieldName ||
		"dbName" == $fieldName || $this->idColumn == $fieldName)
		{
			return false;
		}

		return true;
	}

	function isWritableField($fieldName)
	{
		if("idColumn" == $fieldName || "tableName" == $fieldName ||
		"dbName" == $fieldName || $this->idColumn == $fieldName)
		{
			return false;
		}

		return true;
	}


	function reload()
	{
		$this->getByID($this->{$this->idColumn});
	}

	function getID()
	{
		return $this->{$this->idColumn};
	}
}
?>
