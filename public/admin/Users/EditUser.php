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

require_once(PATH_TO_ROOT."lib/classes/User.php");

require_once(PATH_TO_ROOT."lib/vlib/vlibTemplate.php");

$siteTemplate = new vlibTemplate(PATH_TO_ROOT."global/AdminTemplate.tmpl");
$siteTemplate->setVar("SITE_URL",SITE_URL);
$siteTemplate->setVar("title","Add/Edit Administrator");
$template = new vlibTemplate("EditUser.tmpl");

$error = "";
$user = new User();

if(!array_key_exists("AdminID",$_COOKIE))
{
	header("Location: ".SYSTEM_URL."/admin/login.php");
	exit();
}

$userID = 0;

if(array_key_exists("id",$_GET))
{
	$userID = $_GET['id'];
	
	if("" != $userID && 0 != $userID)
		$user->getByID($userID);
}

if(array_key_exists("Save",$_GET))
{
	$currentPassword = $user->Password;
	
	$user->readEditForm($_GET);
	if($currentPassword !== $user->Password)
	{
		$user->Password = md5("cnspass".$_GET['Password']);
	}

	$user->save();

	header("Location: index.php");
	exit();
}

$template->setVar("error",$error);
$template->setVar("UserID",$userID);

$user->writeEditForm($template);

$content = $template->grab();
$siteTemplate->setVar("content",$content);
$siteTemplate->pparse();
?>