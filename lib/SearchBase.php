<?php
class SearchBase
{
    protected $table="";
    public function __construct($table="candidate")
    {
        $this->table=$table;
    }
    public function getExtraFieldWhere($arrExtraField)
    {
        $arrField=array();
        $arrJoin=array();
        $arrTable=array();
        foreach($arrExtraField as $ind=>$field_name)
        {
            $arrTable[$field_name]="STABLE{$ind}";
            $arrField[]="`STABLE{$ind}`.`{$field_name}`";
            $arrJoin[]="LEFT JOIN (select {$this->table}.{$this->table}_id as `parent_table_id{$ind}`,`ext{$ind}`.`value` AS `{$field_name}` from {$this->table} left join `extra_field` AS `ext{$ind}` ON {$this->table}.{$this->table}_id = ext{$ind}.data_item_id  AND ext{$ind}.field_name='{$field_name}') STABLE{$ind} ON {$this->table}.{$this->table}_id=`parent_table_id{$ind}`";
        }
        return array("table"=>$arrTable,"column"=>$arrField,"join"=>$arrJoin);
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
    private function getRegExpNumericRange($num)
    {
        if($num<10)
        {
            $regexp="[0-{$num}]";
        }
        else if($num<100)
        {
            $d=$num[0]-1;
            $regexp="[0-9]|[1-{$d}][0-9]|{$num[0]}[0-{$num[1]}]";
        }
        else if($num<1000)
        {
            $d=$num[0]-1;
            $d2=$num[1]-1;
            $regexp="[0-9]|[1-9][0-9}]|[1-{$d}][0-9][0-9]|{$num[0]}[1-{$d2}][0-9]|{$num[1]}[0-{$num[2]}]";
        }
        else
        {
            die("range exeeds 999");
        }
        return $regexp;
    }
    public function buildFilter()
    {
        ///search $ per hour regular expression : \$[\s]*([0-9]+)[\s]*\/[\s]*([a-zA-Z]*)
        $arrWhere=array();
        if(isset($_REQUEST["fldfilter"]) && !empty($_REQUEST["fldfilter"]))
        {
            foreach($_REQUEST["fldfilter"] as $ind=>$fldFilter)
            {
                if(empty($_REQUEST["data"][$ind])) continue;
                $arrWhere[]=array("field"=>$fldFilter,"data"=>$_REQUEST["data"][$ind],"condition"=>$_REQUEST["condition"][$ind],"boolean"=>$_REQUEST["boolean"][$ind]);
            }
        }
        $where="";
        $arrCustomField=array();
        if($arrWhere)
        foreach($arrWhere as $ind=>$whr)
        {
            if(is_numeric($whr["field"]))
            {
                $sql="select * from extra_field_settings where extra_field_settings_id={$whr["field"]}";
                $db = DatabaseConnection::getInstance();
                $arrRow=$db->getAllAssoc($sql);
                $fieldName=$arrRow[0]["field_name"];
                $arrCustomField[$ind]=$fieldName;
            }
            else
            {
                if($whr["field"]=="current_pay" || $whr["field"]=="desired_pay")
                {
                    $tmp=trim($whr["data"]);
                    if($tmp[0]!='$') $whr["data"]='$'.$tmp;
                    $arrMatch=array();
                    preg_match('/\$[\s]*([0-9]+)[\s]*\/[-]*[\s]*([a-zA-Z]*)/', $whr["data"],$arrMatch);
                    /*$rangeRegExp=$this->getRegExpNumericRange($arrMatch[1]);
                    $where="`{$this->table}`.`{$whr["field"]}` REGEXP '[$]?[\s]*({$rangeRegExp})[\s]*\/[\s]*{$arrMatch[2]}'";*/
                    if(empty($where))
                    {
                        $where ="CONVERT(TRIM(SUBSTRING(TRIM(TRIM(LEADING '$' FROM `candidate`.`desired_pay`)),1,LOCATE('/',TRIM(TRIM(LEADING '$' FROM `candidate`.`desired_pay`)))-1)) , UNSIGNED INTEGER) BETWEEN 1 AND {$arrMatch[1]}";
                    }
                    else
                    {
                        $where =$where." {$boolean} CONVERT(TRIM(SUBSTRING(TRIM(TRIM(LEADING '$' FROM `candidate`.`desired_pay`)),1,LOCATE('/',TRIM(TRIM(LEADING '$' FROM `candidate`.`desired_pay`)))-1)) , UNSIGNED INTEGER) BETWEEN 1 AND {$arrMatch[1]}";
                    }
                }
                else
                {
                    if(empty($where))
                    {
                        if($whr["condition"]=="equals")
                            $where="`{$this->table}`.`{$whr["field"]}` = '{$whr["data"]}'";
                        else
                            $where="`{$this->table}`.`{$whr["field"]}` like '%{$whr["data"]}%'";
                    }
                    else
                    {
                        $boolean=$arrWhere[$ind-1]["boolean"];
                        if($whr["condition"]=="equals")
                            $where=$where." {$boolean} `{$this->table}`.`{$whr["field"]}` = '{$whr["data"]}'";
                        else
                            $where=$where." {$boolean} `{$this->table}`.`{$whr["field"]}` like '%{$whr["data"]}%'";
                    }
                }
            }
        }
        $customSqlColum="";
        $customSqlJoin="";
        if($arrCustomField)
        {
            $arrCustomSql=$this->getExtraFieldWhere($arrCustomField);
            $arrTable=$arrCustomSql["table"];
            $customSqlColum=implode(",", $arrCustomSql["column"]);
            $customSqlJoin=implode(" ", $arrCustomSql["join"]);
            foreach($arrCustomField as $ind=>$fieldName)
            {
                $whr=$arrWhere[$ind];
                $table=$arrTable[$fieldName];
                if(empty($where))
                {
                    if($whr["condition"]=="equals")
                        $where="`{$table}`.`{$fieldName}` = '{$whr["data"]}'";
                    else
                        $where="`{$table}`.`{$fieldName}` like '%{$whr["data"]}%'";
                }
                else
                {
                    if($ind<=0)
                    {
                        $boolean=$whr["boolean"];
                    }
                    else
                    {
                        $boolean=$arrWhere[$ind-1]["boolean"];
                    }
                    if($whr["condition"]=="equals")
                        $where=$where." {$boolean} `{$table}`.`{$fieldName}` = '{$whr["data"]}'";
                    else
                        $where=$where." {$boolean} `{$table}`.`{$fieldName}` like '%{$whr["data"]}%'";
                }
            }
        }
        return array("where"=>empty($where)?$where:" AND ({$where})","extra_column"=>$customSqlColum,"extra_join"=>$customSqlJoin);
    }
}
?>