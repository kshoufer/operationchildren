<?php
require_once("LocalConfig.php");
require_once(PATH_TO_ROOT."lib/Constants.php");
require_once(PATH_TO_ROOT."lib/ErrorHandling.php");
require_once(PATH_TO_ROOT."lib/DBConnection.php");
require_once(PATH_TO_ROOT."lib/DBObject.php");
require_once(PATH_TO_ROOT."lib/Tools.php");

require_once(PATH_TO_ROOT."lib/classes/MerchantCategory.php");

class Merchant extends DBObject
{
	protected $MerchantID = 0;

	public $MerchantCategoryID="";
	public $Name="";
	public $Phone="";
	public $Website="";
	public $Email="";
	public $Password="";
	public $Address1="";
	public $Address2="";
	public $City="";
	public $State="";
	public $ZipCode="";
	public $Description="";
	public $IsFeatured="No";
	public $Photo="";
	public $PhotoWidth=0;
	public $PhotoHeight=0;
	public $PhotoThumbWidth=0;
	public $PhotoThumbHeight=0;

	public function __construct()
	{
		$this->tableName = "Merchant";
		$this->dbName = DB_NAME;
		$this->idColumn = "MerchantID";
	}

	public function readEditForm($data)
	{
		parent::readEditForm($data);
		if(is_uploaded_file($_FILES['PhotoUpload']['tmp_name']))
		{
			$imageInfo = pathinfo($_FILES['PhotoUpload']['name']);
			$extension = $imageInfo['extension'];
			$imagePath = md5(strtotime("now")."Photo".rand()).".".$extension;
			
			$this->Photo = $imagePath;
			$imagePath = APP_ROOT."/business_images/".$imagePath;
			
			move_uploaded_file($_FILES['PhotoUpload']['tmp_name'],$imagePath);

			list($width, $height) = getimagesize($imagePath);

			$fullPercent = 1.0;
			$thumbPercent = 1.0;

			if($width > 200 || $height > 135)
			{
				if($width > $height)
					$fullPercent = 200/$width;
				else
					$fullPercent = 135/$height;
			}

			if($width > 100 || $height > 100)
			{
				if($width > $height)
					$thumbPercent = 100/$width;
				else
					$thumbPercent = 100/$height;
			}

			$newWidth = round($width * $fullPercent);
			$newHeight = round($height * $fullPercent);

			$newThumbWidth = round($width * $thumbPercent);
			$newThumbHeight = round($height * $thumbPercent);

			$this->PhotoWidth = round($newWidth);
			$this->PhotoHeight = round($newHeight);
			$this->PhotoThumbWidth = round($newThumbWidth);
			$this->PhotoThumbHeight = round($newThumbHeight);

			// Load
			$thumb = imagecreatetruecolor($newWidth, $newHeight);
			$source = imagecreatefromjpeg($imagePath);

			// Resize
			imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
			imagejpeg($thumb,$imagePath);

		}
	}
	
	public function writeTemplate($template,$data=null)
	{
		if($data == null)
			$data = get_object_vars($this);
			
		foreach($data as $key=>$value)
		{
			$data[$key] =  stripslashes($value);
		}
			
		$template = str_replace("[name]",$data['Name'],$template);
		$template = str_replace("[description]",$data['Description'],$template);
		$template = str_replace("[category]",$data['CategoryName'],$template);
		$template = str_replace("[address1]",$data['Address1'],$template);
		$template = str_replace("[address2]",$data['Address2'],$template);
		$template = str_replace("[phone]",$data['Phone'],$template);
		$template = str_replace("[website]","<a href=\"http://{$data['Website']}\" target=\"_blank\">{$data['Website']}</a>",$template);
		$template = str_replace("[photo]","<img src=\"business_images/{$data['Photo']}\" width=\"{$data['PhotoWidth']}\" height=\"{$data['PhotoHeight']}\" border=\"0\" />",$template);
		
		if("" != trim($data['Photo']))
			$template = str_replace("[thumbnail]","<img src=\"business_images/{$data['Photo']}\" width=\"{$data['PhotoThumbWidth']}\" height=\"{$data['PhotoThumbHeight']}\" border=\"0\" />",$template);
		else 
			$template = str_replace("[thumbnail]","<img src=\"images/NoImage.jpg\" border=\"0\" />",$template);
		
		return $template;
	}

	public function writeEditForm($template)
	{
		global $US_STATES;

		parent::writeEditForm($template);

		if("Yes" == $this->IsFeatured)
		$template->setVar("ShowFeaturedMerchantBox",true);
		else
		$template->setVar("ShowFeaturedMerchantBox",false);

		$states = markSelected($US_STATES,"short_name",$this->State);
		$template->setLoop("States",$states);

		$merchantCategory = new MerchantCategory();
		$categories = $merchantCategory->getAll("Name");

		if(!isArrayEmpty($categories))
		{
			$categories = markSelected($categories,"MerchantCategoryID",$this->MerchantCategoryID);
			$template->setVar("HasMerchantCategoryIDs",true);
			$template->setLoop("MerchantCategoryIDs",$categories);
		}
		else
		{
			$template->setVar("HasMerchantCategoryIDs",false);
		}

	}

	public function setFeaturedMerchant($featuredMerchantID)
	{
		$db = new DBConnection();
		$query = "update $this->tableName set IsFeatured='No'";
		$db->execQuery($query);

		$query = "update $this->tableName set IsFeatured='Yes' where MerchantID={$featuredMerchantID}";
		$db->execQuery($query);
	}

	public function loadFeatured()
	{
		$db = new DBConnection();
		$query = "select MerchantID from {$this->tableName} where IsFeatured='Yes'";
		$result = $db->selectRow($query);

		if(!isArrayEmpty($result))
		{
			$this->getByID($result['MerchantID']);
		}
	}

	public function search($keywords,$page=0,$sortColumn=null,$sortDir=null)
	{
		if($page < 0)
		$page = 0;

		$pageSize = 20;
		$pageNum = $page;

		if(strlen(trim($keywords)) > 0)
		{
			$chars = array("%", "(", ")", "*", "!", "^", "#", "$");

			for($x=0; $x<count($chars); $x++)
			{
				$keywords = str_replace($chars[$x], "", $keywords);
			}
		}


		$db = new DBConnection();
		$db->selectDB($this->dbName);

		$queryParts = array();

		$allFields = array(
		"MerchantCategoryID","Name","Phone","Website","Email","Password","Address1","Address2","City","State","ZipCode","Description","IsFeatured","Photo"
		);

		$keywords = addslashes(trim($keywords));
		$allKeywords = array($keywords);

		if(strpos($keywords," ") !== false)
		{
			$allKeywords = explode(" ",$keywords);
		}


		for($i = 0; $i < sizeof($allFields); $i++)
		{
			$columnName = $allFields[$i];
			for($j = 0; $j < sizeof($allKeywords); $j++)
			{
				if(trim($allKeywords[$j]) != "")
				{
					$keyword = $allKeywords[$j];
					$queryParts[] = "$columnName like '%$keyword%'";
				}
			}
		}

		$queryTail = join(" or ",$queryParts);

		$query = "select * from $this->tableName ";
		$countQuery = "select count(*) as count from $this->tableName ";

		if(sizeof($queryParts) > 0)
		{
			$query .= " where $queryTail";
			$countQuery .= " where $queryTail";
		}

		if($sortColumn != null)
		{
			if($sortDir != null)
			$query .= " order by $sortColumn $sortDir";
			else
			$query .= " order by $sortColumn asc";
		}

		$countResult = $db->selectRow($countQuery);
		$count = $countResult['count'];

		$offset = $pageSize * $page;
		$startIndex = $offset;
		$endIndex = $startIndex + $pageSize;

		if($startIndex >= $count)
		{
			$startIndex = 0;
			$endIndex = $pageSize;

			$pageNum = 0;
		}

		if($endIndex >= $count)
		{
			$endIndex = $count;
		}

		$offset = $pageSize * $page;
		if($offset > $startIndex)
		{
			$page = 0;
			$offset = 0;
		}

		$query .= " limit $offset,$pageSize";

		$result = $db->selectArray($query);



		if($endIndex >= $count)
		{
			$endIndex = $count;
		}

		if(!isArrayEmpty($result))
		{
			for($i = 0; $i < sizeof($result); $i++)
			{
				foreach($result[$i] as $key=>$value)
				{
					$result[$i][$key] = stripslashes(trim($value));
					if($key == "MerchantCategoryID" & $value != 0)
					{
						$merchantCategory = new MerchantCategory;
						$merchantCategory->getByID($value);
						$result[$i]['CategoryName'] = $merchantCategory->Name;
					}
				}
			}
		}

		$pageCount = ceil($count/$pageSize);

		$nextPage = $pageNum+1;
		$prevPage = $pageNum-1;

		if($prevPage < 0)
		$prevPage = 0;

		if($nextPage >= $pageCount)
		$nextPage = $pageCount - 1;

		if($nextPage < 0)
		$nextPage = 0;

		$min = $startIndex + 1;
		$max = $endIndex;

		return array("results"=>$result,
		"pageCount"=>$pageCount,
		"nextPage"=>$nextPage,
		"prevPage"=>$prevPage,
		"min"=>$min,
		"max"=>$max,
		"total"=>$count);
	}
	
	public function frontendSearch($keywords,$page=0,$sortColumn=null,$sortDir=null)
	{
		if($page < 0)
		$page = 0;

		$pageSize = 10000;
		$pageNum = $page;

		if(strlen(trim($keywords)) > 0)
		{
			$chars = array("%", "(", ")", "*", "!", "^", "#", "$");

			for($x=0; $x<count($chars); $x++)
			{
				$keywords = str_replace($chars[$x], "", $keywords);
			}
		}


		$db = new DBConnection();
		$db->selectDB($this->dbName);

		$queryParts = array();

		$allFields = array(
		"MerchantCategoryID","Name","Phone","Website","Email","Password","Address1","Address2","City","State","ZipCode","Description","IsFeatured","Photo"
		);

		$keywords = addslashes(trim($keywords));
		$allKeywords = array($keywords);

		if(strpos($keywords," ") !== false)
		{
			$allKeywords = explode(" ",$keywords);
		}


		for($i = 0; $i < sizeof($allFields); $i++)
		{
			$columnName = $allFields[$i];
			for($j = 0; $j < sizeof($allKeywords); $j++)
			{
				if(trim($allKeywords[$j]) != "")
				{
					$keyword = $allKeywords[$j];
					$queryParts[] = "$columnName like '%$keyword%'";
				}
			}
		}

		$queryTail = join(" or ",$queryParts);

		$query = "select * from $this->tableName ";
		$countQuery = "select count(*) as count from $this->tableName ";

		if(sizeof($queryParts) > 0)
		{
			$query .= " where $queryTail";
			$countQuery .= " where $queryTail";
		}

		if($sortColumn != null)
		{
			if($sortDir != null)
			$query .= " order by $sortColumn $sortDir";
			else
			$query .= " order by $sortColumn asc";
		}

		$countResult = $db->selectRow($countQuery);
		$count = $countResult['count'];

		$offset = $pageSize * $page;
		$startIndex = $offset;
		$endIndex = $startIndex + $pageSize;

		if($startIndex >= $count)
		{
			$startIndex = 0;
			$endIndex = $pageSize;

			$pageNum = 0;
		}

		if($endIndex >= $count)
		{
			$endIndex = $count;
		}

		$offset = $pageSize * $page;
		if($offset > $startIndex)
		{
			$page = 0;
			$offset = 0;
		}

		$query .= " limit $offset,$pageSize";

		$result = $db->selectArray($query);



		if($endIndex >= $count)
		{
			$endIndex = $count;
		}

		if(!isArrayEmpty($result))
		{
			for($i = 0; $i < sizeof($result); $i++)
			{
				foreach($result[$i] as $key=>$value)
				{
					$result[$i][$key] = stripslashes(trim($value));
					if($key == "MerchantCategoryID" & $value != 0)
					{
						$merchantCategory = new MerchantCategory;
						$merchantCategory->getByID($value);
						$result[$i]['CategoryName'] = $merchantCategory->Name;
					}
				}
			}
		}

		$pageCount = ceil($count/$pageSize);

		$nextPage = $pageNum+1;
		$prevPage = $pageNum-1;

		if($prevPage < 0)
		$prevPage = 0;

		if($nextPage >= $pageCount)
		$nextPage = $pageCount - 1;

		if($nextPage < 0)
		$nextPage = 0;

		$min = $startIndex + 1;
		$max = $endIndex;

		return array("results"=>$result,
		"pageCount"=>$pageCount,
		"nextPage"=>$nextPage,
		"prevPage"=>$prevPage,
		"min"=>$min,
		"max"=>$max,
		"total"=>$count);
	}

	public function searchByCategory($keywords,$page=0,$sortColumn=null,$sortDir=null)
	{
		if($page < 0)
		$page = 0;

		$pageSize = 10000;
		$pageNum = $page;


		$db = new DBConnection();
		$db->selectDB($this->dbName);

		$queryParts = array();

		$allFields = array(
		"MerchantCategoryID"
		);

		$keywords = addslashes(trim($keywords));
		$allKeywords = array($keywords);

		if(strpos($keywords," ") !== false)
		{
			$allKeywords = explode(" ",$keywords);
		}

		for($i = 0; $i < sizeof($allFields); $i++)
		{
			$columnName = $allFields[$i];
			for($j = 0; $j < sizeof($allKeywords); $j++)
			{
				if(trim($allKeywords[$j]) != "")
				{
					$keyword = $allKeywords[$j];
					$queryParts[] = "$columnName = '$keyword'";
				}
			}
		}

		$queryTail = join(" or ",$queryParts);

		$query = "select * from $this->tableName ";
		$countQuery = "select count(*) as count from $this->tableName ";

		if(sizeof($queryParts) > 0)
		{
			$query .= " where $queryTail";
			$countQuery .= " where $queryTail";
		}

		if($sortColumn != null)
		{
			if($sortDir != null)
			$query .= " order by $sortColumn $sortDir";
			else
			$query .= " order by $sortColumn asc";
		}

		$countResult = $db->selectRow($countQuery);
		$count = $countResult['count'];

		$offset = $pageSize * $page;
		$startIndex = $offset;
		$endIndex = $startIndex + $pageSize;

		if($startIndex >= $count)
		{
			$startIndex = 0;
			$endIndex = $pageSize;

			$pageNum = 0;
		}

		if($endIndex >= $count)
		{
			$endIndex = $count;
		}

		$offset = $pageSize * $page;
		if($offset > $startIndex)
		{
			$page = 0;
			$offset = 0;
		}

		$query .= " limit $offset,$pageSize";

		$result = $db->selectArray($query);

		if($endIndex >= $count)
		{
			$endIndex = $count;
		}

		if(!isArrayEmpty($result))
		{
			for($i = 0; $i < sizeof($result); $i++)
			{
				foreach($result[$i] as $key=>$value)
				{
					$result[$i][$key] = stripslashes(trim($value));
					if($key == "MerchantCategoryID" & $value != 0)
					{
						$merchantCategory = new MerchantCategory;
						$merchantCategory->getByID($value);
						$result[$i]['CategoryName'] = $merchantCategory->Name;
					}
				}
			}
		}

		$pageCount = ceil($count/$pageSize);

		$nextPage = $pageNum+1;
		$prevPage = $pageNum-1;

		if($prevPage < 0)
		$prevPage = 0;

		if($nextPage >= $pageCount)
		$nextPage = $pageCount - 1;

		if($nextPage < 0)
		$nextPage = 0;

		$min = $startIndex + 1;
		$max = $endIndex;

		return array("results"=>$result,
		"pageCount"=>$pageCount,
		"nextPage"=>$nextPage,
		"prevPage"=>$prevPage,
		"min"=>$min,
		"max"=>$max,
		"total"=>$count);
	}
}
?>
