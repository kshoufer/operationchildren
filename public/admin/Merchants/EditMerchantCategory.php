<?php
/*********************************************
* Filename    : EditMerchantCategory.php
* Author      : Christopher Shireman
* Create Date : Wed Nov 11 06:10:25 GMT 2009
* Description : Edit an instance of a MerchantCategory object
* Change Log  :
*    Wed Nov 11 06:10:25 GMT 2009 - Change description here
*********************************************/

require_once("LocalConfig.php");
require_once(PATH_TO_ROOT."lib/Constants.php");
require_once(PATH_TO_ROOT."lib/DBConnection.php");
require_once(PATH_TO_ROOT."lib/Tools.php");

require_once(PATH_TO_ROOT."lib/vlib/vlibTemplate.php");

require_once(PATH_TO_ROOT."lib/classes/MerchantCategory.php");

$myMerchantCategory = new MerchantCategory();

$siteTemplate = new vlibTemplate(PATH_TO_ROOT."global/AdminTemplate.tmpl",$VLIB_OPTIONS);
$siteTemplate->setVar("SITE_URL",SITE_URL);
$siteTemplate->setVar("title","Add/Edit Merchant Category");

$template = new vlibTemplate("EditMerchantCategory.tmpl",$VLIB_OPTIONS);

if(array_key_exists("id",$_INFO) && is_numeric($_INFO['id']))
{
	$myMerchantCategory->getByID($_INFO['id']);
}

if(array_key_exists("Submit",$_INFO))
{
	$myMerchantCategory->readEditForm($_INFO);
	$myMerchantCategory->save();
	
	header("Location: MerchantCategories.php");
	exit();
}

$myMerchantCategory->writeEditForm($template);

$template->setVar("MerchantCategoryID",$_INFO['id']);

$content = $template->grab();
$siteTemplate->setVar("content",$content);
$siteTemplate->pparse();
?>-