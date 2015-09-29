<?php
function loadAuieoField()
{
    $arrTable=array("candidate","company","contact","joborder");
    $sites = new Site(false);
    $rs = $sites->getAll();
    $arrSQL=array();
    foreach($rs as $siteData)
    {
        foreach($arrTable as $table)
        {
            $data_item_type=0;
            if($table=="candidate")
            {
                $data_item_type=100;
            }
            else if($table=="company")
            {
                $data_item_type=200;
            }
            else if($table=="contact")
            {
                $data_item_type=300;
            }
            else if($table=="joborder")
            {
                $data_item_type=400;
            }
            $objSQL=new ClsNaanalSQL("INSERT");
            $objSQL->addTable("auieo_fields");
            $con=DatabaseConnection::getInstance();
            $DB= $con->getConnection()->MetaColumns($table);
            foreach($DB as $arrData)
            {
                if($arrData["name"]=="site_id") continue;
                $objSQL->addValue("data_item_type", $data_item_type);
                $extra_field_type=1;
                if($arrData["native_type"]=="DATETIME")
                {
                    $extra_field_type=4;
                }
                else if($arrData["native_type"]=="BLOB")
                {
                    $extra_field_type=2;
                }
                $objSQL->addValue("uitype", $extra_field_type);
                $field_name=$arrData["name"];
                $objSQL->addValue("fieldname", $field_name);
                $objSQL->addValue("fieldlabel", $field_name);
                $maximumlength=$arrData["len"];
                $objSQL->addValue("maximumlength", $maximumlength);
                $default_value="";
                $key="";
                if(!empty($arrData["flags"]))
                {
                    $key=array_pop($arrData["flags"]);
                    if($key=="primary_key") continue;
                    if(!empty($arrData["flags"]))
                    {
                        $default_value=array_pop($arrData["flags"]);
                    }
                }
                $objSQL->addValue("defaultvalue", $default_value);
                $objSQL->addValue("site_id",$siteData["siteID"]);
                //$objSQL->setSQLType("SELECT");
                $arrSQL[]=$objSQL->render();//trace($sql);
                //$con->query($sql);
            }
            //print_r($DB);
        }
    }
    return $arrSQL;
}
/**
 * 
 * @param type $data_item_type
 * @param type $field_name - field name as in the extra field
 * @param type $site_id
 * @param type $field_name_modified - cleaned fieldname
 */
function loadAuieoExtraFieldData($data_item_type, $field_name, $site_id,$field_name_modified)
{
    $con=DatabaseConnection::getInstance();
    $DB= $con->getConnection();
    $sql="select * from extra_field where data_item_type={$data_item_type} and field_name='{$field_name} and site_id={$site_id}'";
    $arrRecord=$DB->getAllAssoc($sql);
    $arrSQL=array();
    foreach($arrRecord as $record)
    {
        if($data_item_type==100)
        {
            $table="candidate";
            $fldID="candidate_id";
        }
        else if($data_item_type==200)
        {
            $table="company";
            $fldID="company_id";
        }
        else if($data_item_type==300)
        {
            $table="contact";
            $fldID="contact_id";
        }
        else if($data_item_type==400)
        {
            $table="joborder";
            $fldID="joborder_id";
        }
        $objSQL=new ClsNaanalSQL("INSERT");
        $objSQL->addTable($table);
        $objSQL->addValue($field_name_modified, $record["value"]);
        $objSQL->addWhere("site_id", $site_id);
        $objSQL->addWhere($fldID, $record["data_item_id"]);
        $arrSQL[]=$objSQL->render();
    }
    return $arrSQL;
}
function loadAuieoExtraField()
{
    $arrExtraFieldTypeMapping=array(1=>8,2=>9,3=>10,4=>6,5=>7,6=>11);
    $con=DatabaseConnection::getInstance();
    $DB= $con->getConnection();
    $DB->setQuery("select * from extra_field_settings");
    $arrExtraField=$DB->getAllAssoc();

    foreach($arrExtraField as $arrData)
    {
        $objSQL=new ClsNaanalSQL("INSERT");
        $objSQL->addTable("auieo_fields");
        $objSQL->addValue("data_item_type", $arrData["data_item_type"]);
        $objSQL->addValue("uitype", $arrExtraFieldTypeMapping[$arrData["extra_field_type"]]);
        $field_name = cleanToVariableName($arrData["field_name"]);
        $objSQL->addValue("fieldname", $field_name);
        $objSQL->addValue("fieldlabel", $arrData["field_name"]);
        $objSQL->addValue("field_options", $arrData["extra_field_options"]);
        $objSQL->addValue("position", $arrData["position"]);
        $maximumlength=255;
        $fieldType="VARCHAR(255)";
        if($arrData["extra_field_type"]==2)
        {
            $maximumlength=0;
            $fieldType="TEXT";
        }
        else if($arrData["extra_field_type"]==3)
        {
            $maximumlength=3;
            $fieldType="INT(3)";
        }
        else if($arrData["extra_field_type"]==4)
        {
            $fieldType="DATETIME";
        }
        else if($arrData["extra_field_type"]==5 || $arrData["extra_field_type"]==6)
        {
            $fieldType="TEXT";
        }

        $objSQL->addValue("maximumlength", $maximumlength);

        $objSQL->addValue("site_id",$arrData["site_id"]);
        $arrFieldSQL=$objSQL->render();
        $table="";
        if($arrData["data_item_type"]==100)
        {
            $table="candidate";
        }
        else if($arrData["data_item_type"]==200)
        {
            $table="company";
        }
        else if($arrData["data_item_type"]==300)
        {
            $table="contact";
        }
        else if($arrData["data_item_type"]==400)
        {
            $table="joborder";
        }
        $arrSQL[]="ALTER IGNORE TABLE `{$table}` ADD COLUMN `{$field_name}` {$fieldType} default NULL";
       $arrTmpSql= loadAuieoExtraFieldData($arrData["data_item_type"], $arrData["field_name"], $site_id,$field_name);
       foreach($arrTmpSql as $sqltmp)
       {
           $arrSQL[]=$sqltmp;
       }
    }
    return $arrSQL;
}
function cleanToVariableName($string) {
  // $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.
   $string = preg_replace('/[^A-Za-z0-9\_ ]/', '', $string); // Removes special chars.

   return  $string; // Replaces multiple hyphens with single one.
}
?>