<?php
require_once("LocalConfig.php");
require_once(PATH_TO_ROOT."lib/Constants.php");
require_once(PATH_TO_ROOT."lib/ErrorHandling.php");
require_once(PATH_TO_ROOT."lib/DBConnection.php");
require_once(PATH_TO_ROOT."lib/DBObject.php");

class MerchantCategory extends DBObject
{
    protected $MerchantCategoryID = 0;

    public $Name="";
    
    public function __construct($domain="")
    {
        $this->tableName = "MerchantCategory";
        $this->dbName = DB_NAME;
        $this->idColumn = "MerchantCategoryID";
    }

    public function readEditForm($data)
    {
        parent::readEditForm($data);

    }
    
    public function writeEditForm($template)
    {
        parent::writeEditForm($template);   
    }    
}
?>
