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

require_once(PATH_TO_ROOT."lib/classes/Merchant.php");

$siteTemplate = new vlibTemplate(PATH_TO_ROOT."global/AdminTemplate.tmpl",$VLIB_OPTIONS);
$siteTemplate->setVar("title","Add/Edit Merchant");
$siteTemplate->setVar("headerColor",$MENU_COLORS[SECTION_COLOR]);

$template = new vlibTemplate("EditMerchant.tmpl",$VLIB_OPTIONS);

$myMerchant = new Merchant();

if(array_key_exists("id",$_INFO)  && is_numeric(trim($_INFO['id'])))
{
    $myMerchant->getByID($_INFO['id']);
}

if(array_key_exists("Submit",$_INFO))
{
    $myMerchant->readEditForm($_INFO);
    $myMerchant->save();
    
    header("Location: index.php");
    exit();
}

$myMerchant->writeEditForm($template);

$template->setVar("MerchantID",$myMerchant->getID());
$template->setVar("SITE_URL",SITE_URL);

$content = $template->grab();
$siteTemplate->setVar("content",$content);
$siteTemplate->pparse();
?>
