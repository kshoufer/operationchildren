<?php
/*
 * Created on Jan 21, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once("LocalConfig.php");
require_once(PATH_TO_ROOT."lib/Constants.php");
require_once(PATH_TO_ROOT."lib/DBObject.php");
require_once(PATH_TO_ROOT."lib/DBConnection.php");
require_once(PATH_TO_ROOT."lib/Tools.php");

require_once(PATH_TO_ROOT."lib/classes/File.php");

require_once(PATH_TO_ROOT."lib/vlib/vlibTemplate.php");

$file = new File();
$file->Path = $_REQUEST['Filename'];

$siteTemplate = new vlibTemplate(PATH_TO_ROOT."global/AdminTemplate.tmpl");
$siteTemplate->setVar("SITE_URL",SITE_URL);
$siteTemplate->setVar("title","Edit File");

$template = new vlibTemplate("EditFile.tmpl");

$error = "";

if(!array_key_exists("AdminID",$_COOKIE))
{
	header("Location: ".SYSTEM_URL."/admin/login.php");
	exit();
}

if(array_key_exists("Save",$_REQUEST))
{
	$file->saveContents(stripslashes($_REQUEST['Content']));

	header("Location: index.php");
	exit();
}

$template->setVar("Filename",$_REQUEST['Filename']);

$fileContent = $file->getContents();

if(strpos($fileContent,"<html") !== false)
	$template->setVar("EditFullPage",true);
else
	$template->setVar("EditFullPage",false);

$template->setVar("Content",$fileContent);
$template->setVar("SYSTEM_URL",SYSTEM_URL);
$template->setVar("APP_PATH",APP_PATH);

$content = $template->grab();
$siteTemplate->setVar("content",$content);
$siteTemplate->pparse();
?>