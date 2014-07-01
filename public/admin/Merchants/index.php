<?php
/*********************************************
* Filename    : test.tmpl
* Author      : Christopher Shireman
* Create Date : Mon Sep 14 15:06:13 PDT 2009
* Description : Merchant search
* Change Log  :
*    Mon Sep 14 15:06:13 PDT 2009 - Change description here
*********************************************/

require_once("LocalConfig.php");
require_once(PATH_TO_ROOT."lib/Constants.php");
require_once(PATH_TO_ROOT."lib/DBConnection.php");
require_once(PATH_TO_ROOT."lib/Date.php");
require_once(PATH_TO_ROOT."lib/Tools.php");

require_once(PATH_TO_ROOT."lib/vlib/vlibTemplate.php");

require_once(PATH_TO_ROOT."lib/classes/Merchant.php");

$siteTemplate = new vlibTemplate(PATH_TO_ROOT."global/AdminTemplate.tmpl",$VLIB_OPTIONS);
$siteTemplate->setVar("title","Merchants");

$template = new vlibTemplate("index.tmpl",$VLIB_OPTIONS);

$myMerchant = new Merchant();

$sortColumn = "MerchantID";
$sortDir = "asc";

if(array_key_exists("deleteID",$_INFO) && is_numeric($_INFO['deleteID']))
{
        $myMerchant->deleteByID($_INFO['deleteID']);
}

$allMerchants = array();

if(array_key_exists("keywords",$_INFO))
{
    if(array_key_exists("sort",$_INFO))
    {
        $sortColumn = $_INFO['sort'];
        $sortDir = $_INFO['sortDir'];
        $prevSort = $_INFO['prevSort'];

        if($sortColumn == $prevSort && $_INFO['cmd'] == "Sort")
        {
            if($sortDir == "asc")
                $sortDir = "desc";
            else
                $sortDir = "asc";
        }
        elseif($_INFO['cmd'] == "Sort")
        {
            $sortDir = "asc";
        }
    }
    
    $page = 0;
    if(array_key_exists("page",$_INFO))
    {
        $page = $_INFO['page'];
    }
    
    if(array_key_exists("CurrentPage",$_INFO) && is_numeric($_INFO['CurrentPage']))
    {
        $page = $_INFO['CurrentPage'] - 1;
    }
    
    $searchResults = $myMerchant->search($_INFO['keywords'],$page,$sortColumn,$sortDir);

    $allMerchants = $searchResults['results'];
    
    $currentPage = 0;
    
    if(array_key_exists("page",$_INFO))
    {
        $currentPage = $_INFO['page'] + 1;
    }

    if(array_key_exists("CurrentPage",$_INFO) && is_numeric($_INFO['CurrentPage']))
    {
        $currentPage = $_INFO['CurrentPage'];
    }
    
    if($currentPage > $searchResults['pageCount'])
        $currentPage = 1;
    
    $template->setVar("Min",$searchResults['min']);
    $template->setVar("Max",$searchResults['max']);
    $template->setVar("Total",$searchResults['total']);
    $template->setVar("NextPage",$searchResults['nextPage']);
    $template->setVar("PrevPage",$searchResults['prevPage']);
    $template->setVar("PageCount",$searchResults['pageCount']);
    $template->setVar("MaxPage",$searchResults['pageCount']-1);
    $template->setVar("CurrentPage",$currentPage);
    $template->setVar("Keywords",trim($_INFO['keywords']));
}
else
{
    if(array_key_exists("sort",$_INFO))
    {
        $sortColumn = $_INFO['sort'];
        $sortDir = $_INFO['sortDir'];
        $prevSort = $_INFO['prevSort'];

        if($sortColumn == $prevSort && $_INFO['cmd'] == "Sort")
        {
            if($sortDir == "asc")
                $sortDir = "desc";
            else
                $sortDir = "asc";
        }
        elseif($_INFO['cmd'] == "Sort")
        {
            $sortDir = "asc";
        }
    }
    
    $searchResults = $myMerchant->search("",0,$sortColumn,$sortDir);

    $allMerchants = $searchResults['results'];
    
    $currentPage = 1;
    if(array_key_exists("page",$_INFO))
    {
        $currentPage = $_INFO['CurrentPage'];
    }
    
    $template->setVar("Min",$searchResults['min']);
    $template->setVar("Max",$searchResults['max']);
    $template->setVar("Total",$searchResults['total']);
    $template->setVar("NextPage",$searchResults['nextPage']);
    $template->setVar("PrevPage",$searchResults['prevPage']);
    $template->setVar("PageCount",$searchResults['pageCount']);
    $template->setVar("MaxPage",$searchResults['pageCount']-1);
    $template->setVar("CurrentPage",$currentPage);
    $template->setVar("Keywords",trim(""));
}

if(is_array($allMerchants) && sizeof($allMerchants) > 0)
{
    $template->setLoop("Merchants",$allMerchants);
    $template->setVar("HasMerchants",true);
}
else
{
    $template->setVar("HasMerchants",false);
}

$template->setVar("SearchContext",base64_encode(urlencode($_SERVER['QUERY_STRING'])));
$template->setVar("sort",$sortColumn);
$template->setVar("sortDir",$sortDir);

$template->setVar("SITE_URL",SITE_URL);

$content = $template->grab();
$siteTemplate->setVar("content",$content);
$siteTemplate->pparse();
?>
