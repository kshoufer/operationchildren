<?php
/*********************************************
* Filename    : index.php
* Author      : Christopher Shireman
* Create Date : 08/29/2005
* Description :
* Change Log  :
*    08/17/2005 - Created File
*********************************************/

require_once("LocalConfig.php");
require_once(PATH_TO_ROOT."lib/Constants.php");
require_once(PATH_TO_ROOT."lib/DBConnection.php");
require_once(PATH_TO_ROOT."lib/Date.php");

require_once(PATH_TO_ROOT."lib/vlib/vlibTemplate.php");

require_once(PATH_TO_ROOT."lib/classes/File.php");

$siteTemplate = new vlibTemplate(PATH_TO_ROOT."global/AdminTemplate.tmpl",$VLIB_OPTIONS);
$siteTemplate->setVar("SITE_URL",SITE_URL);
$siteTemplate->setVar("title","Files");

$template = new vlibTemplate("index.tmpl",$VLIB_OPTIONS);

$file = new File();

if(array_key_exists("delete",$_REQUEST))
{
	$file->Path = $_REQUEST['delete'];
	$file->delete();
}

if(array_key_exists("AddNewFile",$_REQUEST))
{
	$newFilename = $_REQUEST['NewFilename'];
	$fileInfo = pathinfo($newFilename);
	if("" == $fileInfo['extension'])
		$newFilename .= ".html";

	$file->createFile($newFilename);
}

$file = new File();
$files = $file->getFileListing();

if(is_array($files) && sizeof($files) > 0)
{
	$template->setLoop("Files",$files);
	$template->setVar("HasFiles",true);
}
else
{
	$template->setVar("HasFiles",false);
}

$template->setVar("SITE_URL",SITE_URL);

$content = $template->grab();
$siteTemplate->setVar("content",$content);
$siteTemplate->pparse();
?>
