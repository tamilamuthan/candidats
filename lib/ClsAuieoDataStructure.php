<?php
class ClsAuieoDataStructure
{
    protected $arrHook=array();
    public function __construct() {
        ;
    }
    public function setHook($hookName,$hook)
    {
        $this->arrHook[$hookName]=$hook;
    }
}
?>