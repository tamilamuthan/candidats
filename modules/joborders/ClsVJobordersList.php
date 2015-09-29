<?php
include_once("mvc/viewers/ClsVList.php");
include_once("modules/joborders/ClsJobOrdersFilter.php");
class ClsVJobordersList extends ClsVList
{
    public function __construct(ClsAuieoArray &$objAuieoModel) 
    {
        $this->objAuieoModel=$objAuieoModel;
        parent::__construct("joborders",7);
        $rows=$objAuieoModel->getRows();
        if($rows>0)
        {
            $this->addRowCheckbox(0);
            $this->addData($this->getSortLink('name', 'Name'),0,0);
            $this->addData($this->getSortLink('phone1', 'Primary Phone'),0,1);
            $this->addData("Key Technologies",0,2);
            $this->addData($this->getSortLink('city', 'Created'),0,3);
            $this->addData($this->getSortLink('owner_user.last_name', 'Owner'),0,4);
            for($i=0;$i<$rows;$i++)
            {
                $this->addRowCheckbox($i+1,$objAuieoModel->getRenderValue($i,0));
                $this->addData($objAuieoModel->getRenderValue($i,1), $i+1, 0);
                $this->addData($objAuieoModel->getRenderValue($i,2), $i+1, 1);
                $this->addData($objAuieoModel->getRenderValue($i,3), $i+1, 2);
                $this->addData($objAuieoModel->getRenderValue($i,4), $i+1, 3);
                $this->addData($objAuieoModel->getRenderValue($i,5), $i+1, 4);
            }
            $this->assign("list",$this->getTable());
        }
        else
        {
            $this->assign("list","No matching entries found.");
        }
        $arrPager=ClsNaanalRequest::getInstance()->getPager();
        $pagination=getPagination("index.php?module=joborders", $rows, $arrPager["current_page"], $arrPager["items_per_page"]);
        $this->assign("pagination",$pagination);
        $this->assign("total_records",$rows);
        $this->assign("filter", ClsJobOrdersFilter::getInstance()->getFilter());
        //$this->addData("test",3,3);
        //$this->addColHeading("good",3);
        //<input type="checkbox" id="checked_<?php echo($data['contactID']); ? >" name="checked_<?php echo($data['contactID']); ? >" />
        
    }
    public function addRowCheckbox($row,$recordID=false)
    {
        if($this->isRowCheckbox===false) $this->isRowCheckbox=true;
        if($row===0)
        {
            $data="<input type='checkbox' onclick='toggleChecksAll(this);' />";
        }
        else
        {
            $data="<input id='checked_{$recordID}' class='record_checkbox' type='checkbox' name='checked_{$recordID}' />
            <a href='javascript:void(0);' onclick=\"window.open('index.php?module=joborders&amp;action=show&amp;jobOrderID={$recordID}')\" title='View in New Window'>
                                        <img src='images/new_window.gif' alt='(Preview)' border='0' width='15' height='15' />
                                    </a>";
        }
        $this->addSideHeading($data, $row);
    }
}
?>