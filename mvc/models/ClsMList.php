<?php
class ClsMList extends ClsAuieoArray
{
    protected $objSQL=null;
    protected $table="";
    protected $_siteID="";
    protected $_sortBy="";
    protected $_sortDirection="DESC";
    protected $totalRecords=0;
    protected $objModuleRequest=null;
    public function __construct($table,$cols)
    {
        $this->objSQL = new ClsAuieoSQL();
        parent::__construct($cols);
        $objSession=ClsNaanalSession::getInstance();
        $this->_siteID=$objSession->getPanelData("client_id");
        $this->table=$table;
    }
    public function setModuleRequest(&$objModuleRequest)
    {
        $this->objModuleRequest=$objModuleRequest;
    }
    public function getTotalRecords()
    {
        return $this->totalRecords;
    }
    public function convertColumnwiseToRowwise($arrRequestColumn,$isAssociateArray=false)
    {
            $arrRow=array();
            $arrColumn=array();
            $arrHead=array();
            if($arrRequestColumn)
            foreach($arrRequestColumn as $columname=>$arrCol)
            {
                    $arrColumn=$arrCol;
                    $arrHead[]=$columname;
            }
            if(!$isAssociateArray) $arrRow[0]=$arrHead;
            $count=count($arrColumn);
            if($arrColumn)
            foreach($arrColumn as $k=>$v)
            {
                    foreach($arrHead as $head)
                    {
                            if($isAssociateArray) $arrRow[$k][$head]=$arrRequestColumn[$head][$k];
                            else $arrRow[$k][]=$arrRequestColumn[$head][$k];	
                    }
            }
            return $arrRow;
    }
    public function addListFilter($listID)
    {
        $objNewSQL=new ClsAuieoSQL();
        $objFrom=$objNewSQL->addFrom("saved_list_entry");
        $joinID=$objFrom->addJoinField("saved_list_id");
        $objFromList=$objNewSQL->addFrom("saved_list");
        $savedListID=$objFromList->addJoinField("saved_list_id");
        $objFromList->setJoinWith($objFrom,$joinID,$savedListID);
        $objNewSQL->addSelect($objFrom,"saved_list_entry_id");
        $objWhere=$objNewSQL->addWhere($objFromList, "saved_list_id", $listID);
        
        $objPDO=ClsNaanalPDO::getNamedInstance();
        $objPDO->setQuery($objNewSQL->render());
        $arr=$objPDO->getColumn("saved_list_entry_id");
        $fromSavedListEntry=$this->objSQL->getFromObjectByTableName("saved_list_entry");
        $fromSavedList=$this->objSQL->getFromObjectByTableName("saved_list");
        if(!$fromSavedListEntry)
        {
            $fromSavedListEntry=$this->objSQL->addFrom("saved_list_entry",false,1);
            $joinSavedListEntryID=$fromSavedListEntry->addJoinField("saved_list_id");
        }
        if(!$fromSavedList)
        {
            $fromSavedList=$this->objSQL->addFrom("saved_list",false,2);
            $joinSavedListID=$fromSavedList->addJoinField("saved_list_id");
            $fromSavedList->setJoinWith($fromSavedListEntry,$joinSavedListEntryID,$joinSavedListID);
        }
        else
        {
            $fromSavedList->setPriority(2);
            $joinSavedListID=$fromSavedList->addJoinField("saved_list_id");
            $fromSavedList->setJoinWith($fromSavedListEntry,$joinSavedListEntryID,$joinSavedListID);
        }
        $this->objSQL->addSelect($fromSavedListEntry,"saved_list_entry_id");
        $this->objSQL->addSelect($fromSavedListEntry,"data_item_id");
        $objWhere=$this->objSQL->addWhere($fromSavedListEntry,"saved_list_entry_id",implode(",",$arr));
        $objWhere->setCondition("in");
    }
    public function buildListFilter($module)
    {
        $objNewSQL=new ClsAuieoSQL();
        
        $objFrom=$objNewSQL->addFrom("saved_list_entry");
        $joinID=$objFrom->addJoinField("saved_list_id");
        $objFromList=$objNewSQL->addFrom("saved_list");
        $userJoinID=$objFromList->addJoinField("saved_list_id");
        $objFromList->setJoinWith($objFrom,$joinID,$userJoinID);
        
        $joinID=$objFromList->addJoinField("created_by");
        $objFromUser=$objNewSQL->addFrom("user","owner_user");
        $userJoinID=$objFromUser->addJoinField("user_id");
        $objFromUser->setJoinWith($objFromList,$joinID,$userJoinID);
        
        $joinID=$objFromList->addJoinField("data_item_type");
        $objFromType=$objNewSQL->addFrom("data_item_type");
        $userJoinID=$objFromType->addJoinField("data_item_type_id");
        $objFromType->setJoinWith($objFromList,$joinID,$userJoinID);
        
        $objWhere=$objNewSQL->addWhere($objFromType, "module", $module);
        
        $sql=$objNewSQL->render();
        $objPDO=ClsNaanalPDO::getNamedInstance();
        $objPDO->setQuery($sql);
        $arr=$objPDO->getAllAssoc();trace($arr);
    }
    public function buildFilter(ClsAuieoSQL &$objSQL)
    {
        $objFromCandidate=$objSQL->getFromObjectByTableName($this->table);
        $arrWhere=array();
        $objRequest=ClsNaanalRequest::getInstance();
        $arrFieldFilter=$objRequest->getData("fldfilter");
        if($arrFieldFilter)
        {
            $data=$objRequest->getData("data");
            $condition=$objRequest->getData("condition");
            $boolean=$objRequest->getData("boolean");
            $group=$objRequest->getData("boolean");
            foreach($arrFieldFilter as $ind=>$fldFilter)
            {
                if(empty($data[$ind])) continue;
                $arrWhere[]=array("field"=>$fldFilter,"data"=>$data[$ind],"condition"=>$condition[$ind],"boolean"=>$boolean[$ind],"group"=>$group[$ind]==1?true:false);
            }
        }
        $where="";
        if($arrWhere)
        foreach($arrWhere as $ind=>$whr)
        {
            if(is_numeric($whr["field"]))
            {
                $sql="select * from extra_field_settings where extra_field_settings_id={$whr["field"]}";
                $db = DatabaseConnection::getInstance();
                $arrRow=$db->getAllAssoc($sql);
                $field_name=$arrRow[0]["field_name"];
                $ctable="STABLE{$ind}";
                $cfield="`STABLE{$ind}`.`{$field_name}`";
                $cquery="select {$this->table}.{$this->table}_id as `parent_table_id{$ind}`,`ext{$ind}`.`value` AS `{$field_name}` from {$this->table} left join `extra_field` AS `ext{$ind}` ON {$this->table}.{$this->table}_id = ext{$ind}.data_item_id  AND ext{$ind}.field_name='{$field_name}'";
                $cfrom=$objSQL->addQuery($cquery,$ctable);
                $cjoin=$cfrom->addJoinField("parent_table_id{$ind}");
                $ccandidate=new ClsAuieoSQLFrom();
                $ccandidate->setTable("candidate");
                $ccjoin=$ccandidate->addJoinField("candidate_id");//trace($cjoin);
                $cfrom->setJoinWith($ccandidate,$ccjoin, $cjoin);
                $objSQL->addSelect($cfrom, $field_name);
                $objWhere=$objSQL->addWhere($cfrom, $field_name, $whr["data"]);
            }
            else
            {
                $objWhere=$objSQL->addWhere($objFromCandidate, $whr["field"], $whr["data"]);
                $boolean=$arrWhere[$ind]["boolean"];
                $objWhere->setBoolean($boolean);
            }
            if($whr["group"]==1)
            {
                $objWhere->setGroup();
            }
            if(empty($where))
            {
                if($whr["condition"]=="equals")
                {
                }
                else
                {
                    $objWhere->setCondition("like");
                }
            }
            else
            {
                if($whr["condition"]=="equals")
                {
                }
                else
                {
                    $objWhere->setCondition("like");
                }
            }
        }
    }
}
?>
