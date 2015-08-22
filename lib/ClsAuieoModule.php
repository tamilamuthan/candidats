<?php
class ClsAuieoModule
{
    private $module="home";
    private $action="create";
    private $arrConfigVar=array();
    public function __construct($module,$action,$id=0,$arrWhere=array(),$parentModuleID=0)
    {
        $this->module=$module;
        $this->action=$action;
        $this->arrConfigVar=ClsNaanalApplication::getConfigVars($module);
    }
    public function getConfigVar($name)
    {
        if(isset($this->arrConfigVar["action"][$this->action]["theme"]))
        {
            return $this->arrConfigVar["action"][$this->action]["theme"];
        }
        else
        {
            return null;
        }
    }
}
?>