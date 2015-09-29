<?php
class SearchDataStructure
{
    private $mode="";
    private $wildCardString="";
    private $module="";
    private $advancedSearchParser="";
    private $advancedSearchOn=0;
    public function __construct($module)
    {
        $this->module=$module;
    }
    public function loadFromURL()
    {
        $this->addMode();
        $this->addWildCardString();
        $this->addAdvancedSearchParser();
        $this->advancedSearchOn();
    }
    public function getAsArray()
    {
        $arr=array();
        $arr["mode"]=$this->mode;
        $arr["wildCardString"]=$this->wildCardString;
        $arr["m"]=$this->module;
        $arr["a"]="search";
        $arr["advancedSearchParser"]=$this->advancedSearchParser;
        $arr["advancedSearchOn"]=$this->advancedSearchOn;
        $ucmodule=  ucfirst($this->module);
        $arr["search($ucmodule}"]="Search";
        $arr["getback"]="getback";
        return $arr;
    }
    public function addMode($mode=false)
    {
        $objRequest=ClsNaanalRequest::getInstance();
        $this->mode=$mode===false?$objRequest->getData("mode"):$mode;
    }
    public function addWildCardString($wildCardString=false)
    {
        $objRequest=ClsNaanalRequest::getInstance();
        $this->wildCardString=$wildCardString===false?$objRequest->getData("wildCardString"):$wildCardString;
    }
    public function addAdvancedSearchParser($advancedSearchParser=false)
    {
        $objRequest=ClsNaanalRequest::getInstance();
        $this->advancedSearchParser=$advancedSearchParser===false?$objRequest->getData("advancedSearchParser"):$advancedSearchParser;
    }
    public function advancedSearchOn($advancedSearchOn=false)
    {
        $objRequest=ClsNaanalRequest::getInstance();
        $this->advancedSearchOn=$advancedSearchOn===false?$objRequest->getData("advancedSearchOn"):$advancedSearchOn;
    }
    public function __toString()
    {
        $ucmodule=  ucfirst($this->module);
        $strUrlParam="m={$this->module}&a=search&search($ucmodule}=Search&getback=getback&mode={$this->mode}&wildCardString={$this->wildCardString}&advancedSearchParser={$this->advancedSearchParser}&advancedSearchOn={$this->advancedSearchOn}";
    }
}
?>