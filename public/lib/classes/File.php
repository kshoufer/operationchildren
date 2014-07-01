<?php
require_once("LocalConfig.php");
require_once(PATH_TO_ROOT."lib/Constants.php");
require_once(PATH_TO_ROOT."lib/DBConnection.php");
require_once(PATH_TO_ROOT."lib/DBObject.php");
require_once(PATH_TO_ROOT."lib/Tools.php");

class File
{
	public $Path;
	protected $BasePath;

	public function __construct()
	{
		$this->BasePath = APP_ROOT;
	}

	public function delete()
	{
		$fullPath = $this->BasePath."/".$this->Path;
		if(is_file($fullPath))
			unlink($fullPath);
	}

   function copy($from,$to)
   {
      $from = $this->BasePath.$from;
      $to = $this->BasePath.$to;

      $errors = array();
      if(file_exists($from))
      {
         if(!file_exists($to))
         {
            copy($from,$to);
         }
         else
         {
            $errors[] = "A file with that name allready exists.<br>Please choose a different name.";
         }
      }
      else
      {
         $errors[] = "The file you are trying to copy no longer exists.<br>Please refresh your browser and try again.";
      }

      return $errors;
   }


	public function getBasePath()
	{
		return $this->BasePath;
	}

	public function setPath($path)
	{
		$this->Path = $path;
	}

	public function getPath()
	{
		return $this->Path;
	}

	function getContents()
	{
		if(file_exists($this->BasePath.$this->Path))
		{
			return file_get_contents($this->BasePath.$this->Path);
		}

		return "";
	}

	function saveContents($content)
	{
		file_put_contents($this->BasePath.$this->Path,$content);
	}

	function rename($from,$to)
	{
		$from = $this->BasePath.$from;
		$to = $this->BasePath.$to;

		$errors = array();
		if(file_exists($from))
		{
			if(!file_exists($to))
			{
				rename($from,$to);
			}
			else
			{
				$errors[] = "A file with that name allready exists.<br>Please choose a different name.";
			}
		}
		else
		{
			$errors[] = "The file you are trying to rename no longer exists.<br>Please refresh your browser and try again.";
		}

		return $errors;
	}

	function create($newDir)
	{
		$errors = array();
		if(!file_exists($newDir))
		{
			$newDir = $this->BasePath.$newDir;
			mkdir($newDir,0775);
		}
		else
		{
			$errors[] = "A file with that name allready exists<br>Please choose a different name.";
		}

		return $errors;
	}

	function createFile($path)
	{
		$errors = array();
		$fullPath = $this->BasePath.$path;
		if(!file_exists($fullPath))
		{
			touch($fullPath);
			chmod($fullPath,0755);
	//		chown($fullPath,"apache");
		}
		else
		{
			$errors[] = "A file with that name allready exists<br>Please choose a different name.";
		}

		return $errors;
	}

	public function getFileListing()
	{
		$files = array();

		$fullPath = $this->BasePath . $this->Path;

		if (is_dir($fullPath) && is_readable($fullPath))
		{

			$d = dir($fullPath);
			while (false !== ($f = $d->read()))
			{
				// skip . and ..
				if (('.' == $f) || ('..' == $f))
				{
					continue;
				}

				$fileStats = stat("$fullPath/$f");

				$file = array("Name"=>$f,
				"IsFile"=>true,
				"IsDirectory"=>false,
				"Path"=>urlencode("$this->Path/".$f),
				"RealPath"=>"$this->Path/".$f,
				"HasChildren"=>false,
				"LastUpdated"=>date("m/d/Y h:i:s a",$fileStats['mtime']));

				if (is_dir("$fullPath/$f"))
				{
					//Do Nothing
				}
				else
				{
					$fileInfo = pathinfo($f);
					$fileExt = $fileInfo['extension'];

					if($fileExt == "html" || $fileExt == "htm" ||
						$fileExt == "HTML" || $fileExt == "HTML")
					{
						$files[] = $file;
					}
				}
			}

			$d->close();
		}

		if(sizeof($files) > 0)
		{
			$files = array_column_sort($files,"Name");
		}

		return $files;
	}
}
?>
