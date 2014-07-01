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
require_once(PATH_TO_ROOT."lib/ErrorHandling.php");
require_once(PATH_TO_ROOT."lib/DBConnection.php");
require_once(PATH_TO_ROOT."lib/Date.php");

require_once(PATH_TO_ROOT."lib/vlib/vlibTemplate.php");

require_once(PATH_TO_ROOT."lib/classes/User.php");

$siteTemplate = new vlibTemplate(PATH_TO_ROOT."global/AdminTemplate.tmpl");
$siteTemplate->setVar("SITE_URL",SITE_URL);
$siteTemplate->setVar("title","Administrators");

$template = new vlibTemplate("index.tmpl",$VLIB_OPTIONS);

$myUser = new User();

if(array_key_exists("delete",$_REQUEST))
{
    $myUser->deleteByID($_REQUEST['delete']);
}

$allUsers = $myUser->getAll();

if(is_array($allUsers) && sizeof($allUsers) > 0)
{
    $template->setLoop("Users",$allUsers);
    $template->setVar("HasUsers",true);
}
else
{
    $template->setVar("HasUsers",false);
}

$content = $template->grab();
$siteTemplate->setVar("content",$content);
$siteTemplate->pparse();
?>