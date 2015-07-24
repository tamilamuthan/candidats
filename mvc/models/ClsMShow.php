<?php
class ClsMShow
{
    protected $objSQL=null;
    protected $module="";
    protected $_siteID="";
    protected $objModuleRequest=null;
    public function __construct($module)
    {
        $this->objSQL = new ClsAuieoSQL();
        $this->_siteID = $_SESSION['CATS']->getSiteID();
        $this->module=$module;
    }
    public function setModuleRequest(&$objModuleRequest)
    {
        $this->objModuleRequest=$objModuleRequest;
    }
}
?>
