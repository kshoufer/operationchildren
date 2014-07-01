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
$template = new vlibTemplate("home.tmpl");

if(!array_key_exists("AdminID",$_COOKIE))
{
	header("Location: login.php");
	exit();
}

$user = new User();
$user->getByID($_COOKIE['AdminID']);
if("Administrator" == $user->AccessLevel)
	$template->setVar("IsAdministrator",true);
else
	$template->setVar("IsAdministrator",false);

$template->setVar("SYSTEM_NAME",SYSTEM_NAME);
$template->pparse();
?>
