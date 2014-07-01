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

$template = new vlibTemplate("topnav.tmpl");
$template->setVar("SITE_URL",SITE_URL);

$user = new User();
$user->getByID($_COOKIE['AdminID']);

if($user->AccessLevel == "Administrator")
	$template->setVar("IsAdministrator",true);
else
	$template->setVar("IsAdministrator",false);

$template->pparse();
?>
