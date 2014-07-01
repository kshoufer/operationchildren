<?php
/*
 * Created on Jan 21, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

ini_set("display_errors","On");

require_once("LocalConfig.php");
require_once(PATH_TO_ROOT."lib/Constants.php");
require_once(PATH_TO_ROOT."lib/DBObject.php");
require_once(PATH_TO_ROOT."lib/DBConnection.php");
require_once(PATH_TO_ROOT."lib/Tools.php");

require_once(PATH_TO_ROOT."lib/classes/User.php");

require_once(PATH_TO_ROOT."lib/vlib/vlibTemplate.php");

$siteTemplate = new vlibTemplate(PATH_TO_ROOT."global/AdminTemplate.tmpl");
$template = new vlibTemplate("login.tmpl");

$error = "";
$user = new User();

if(array_key_exists("AdminID",$_COOKIE))
{
	header("Location: home.php");
	exit();
}

if(array_key_exists("Login",$_GET))
{
	$username = addslashes(trim($_GET['Username']));
	$password = addslashes(trim($_GET['Password']));

	$valid = $user->authenticate($username,$password);

	if($valid === true)
	{
		setcookie("AdminID",$user->UserID,time()+(24*60*60),"/");
		header("Location: home.php");
		exit();
	}
	else
	{
		$error = "Invalid Email address or Password";
	}
}

$template->setVar("SYSTEM_NAME",SYSTEM_NAME);
$template->setVar("error",$error);

$content = $template->grab();
$siteTemplate->setVar("content",$content);
$siteTemplate->pparse();
?>
