<?php
class ClsVList extends ClsAuieoModuleViewer
{
    private $objTable=null;
    private $cols=1;
    private $isRowCheckbox=false;
    protected $objAuieoModel=null;
    public function __construct($module,$cols) {
        $this->cols=$cols;
        $objHTML=ClsNaanalLibrary::getInstance("html");
        $this->objTable=$objHTML->getGenerator()->getTable("search",$cols);
        //$this->objTable->setParam("border",1);
        pageHeaderInclude('js/export.js');
        pageHeaderInclude('js/searchSaved.js');
        pageHeaderInclude('js/suggest.js');
        parent::__construct($module);
        $arrPager=ClsNaanalRequest::getInstance()->getPager();
        $pagination=getPagination("index.php?module={$module}", $this->objAuieoModel->getTotalRecords() , $arrPager["current_page"], $arrPager["items_per_page"]);
        $this->assign("total_records",$this->objAuieoModel->getTotalRecords());
        $this->assign("pagination",$pagination);
    }
    public function setTableParam($name,$value)
    {
        $this->objTable->setParam($name,$value);
    }
    public function addData($data,$row,$col)
    {
        $pos=$row*$this->cols + $col;
        $this->objTable->addData($data,$pos);
    }
    public function addColHeading($data,$col)
    {
        if($this->objTable->getColHeading()===false) $this->objTable->setColHeading();
        $objCell=$this->objTable->addData($data,$col);
    }
    public function addSideHeading($data,$row)
    {
        if($this->objTable->getSideHeading()===false) $this->objTable->setSideHeading();
        $pos=$row*$this->cols;
        $objCell=$this->objTable->addData($data,$pos);
    }
    public function getSortLink($headerField, $headerText)
    {
        /* If this field is not the current sort-by field, or if it is and the
         * current sort direction is DESC, the link will use ASC sort order.
         */
        if ($this->_sortBy !== $headerField || $this->_sortDirection === 'DESC')
        {
            $sortDirection = 'ASC';
        }
        else
        {
            $sortDirection = 'DESC';
        }

        if ($this->_sortBy == $headerField && $this->_sortDirection === 'ASC')
        {
            $sortImage = '&nbsp;<img src="images/downward.gif" style="border: none;" alt="" />';
        }
        else if ($this->_sortBy == $headerField && $this->_sortDirection === 'DESC')
        {
            $sortImage = '&nbsp;<img src="images/upward.gif" style="border: none;" alt="" />';
        }
        else
        {
            $sortImage = '&nbsp;<img src="images/nosort.gif" style="border: none;" alt="" />';
        }

        $ret = "<a href='index.php?module=contacts&amp;sortBy={$headerField}&amp;sortDirection={$sortDirection}'><nobr>{$headerText}{$sortImage}</nobr></a>"; 
        return $ret;
    }
    public function setRowCheckbox()
    {
        $this->isRowCheckbox=true;
    }
    public function resetRowCheckbox()
    {
        $this->isRowCheckbox=false;
    }
    public function getRowCheckbox()
    {
        return $this->isRowCheckbox;
    }
    public function getTable()
    {
        return $this->objTable->render();
    }
}
?>