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

$myMerchant = new Merchant();
$myMerchant->setFeaturedMerchant($_INFO['MerchantID']);

?>