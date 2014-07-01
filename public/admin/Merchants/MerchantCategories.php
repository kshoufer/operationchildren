<?php
/*********************************************
 * Filename    : MerchantCategories.php
 * Author      : Christopher Shireman
 * Create Date : Wed Nov 11 06:08:45 GMT 2009
 * Description : List MerchantCategory objects
 * Change Log  :
 *    Wed Nov 11 06:08:45 GMT 2009 - Created File
 *********************************************/

require_once("LocalConfig.php");
require_once(PATH_TO_ROOT."lib/Constants.php");
require_once(PATH_TO_ROOT."lib/DBConnection.php");
require_once(PATH_TO_ROOT."lib/Tools.php");

require_once(PATH_TO_ROOT."lib/vlib/vlibTemplate.php");

require_once(PATH_TO_ROOT."lib/classes/MerchantCategory.php");

$siteTemplate = new vlibTemplate(PATH_TO_ROOT."global/AdminTemplate.tmpl",$VLIB_OPTIONS);
$siteTemplate->setVar("SITE_URL",SITE_URL);
$siteTemplate->setVar("title","Merchant Categories");

$template = new vlibTemplate("MerchantCategories.tmpl",$VLIB_OPTIONS);

$myMerchantCategory = new MerchantCategory();

if(array_key_exists("delete",$_REQUEST))
{
   $myMerchantCategory->deleteByID($_REQUEST['delete']);
}

$allMerchantCategorys = $myMerchantCategory->getAll();

$message = "";

if(sizeof($allMerchantCategorys) > 0)
{
   $template->setVar("HasMerchantCategorys",true);
   $template->setLoop("MerchantCategorys",$allMerchantCategorys);
}
else
{
   $template->setVar("HasMerchantCategorys",false);
}

$template->setVar("Message",$message);

$content = $template->grab();
$siteTemplate->setVar("content",$content);
$siteTemplate->pparse();
?>
