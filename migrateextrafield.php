<?php
include("config.php");
include_once('./lib/vendor/autoload.php');
Logger::configure('logger.xml');
include_once("lib/ClsNaanalSQL.php");
include_once("lib/ClsNaanalPDO.php");
include_once("lib/DatabaseConnection.php");
function loadAuieoExtraField()
{
    $arrExtraFieldTypeMapping=array(1=>8,2=>9,3=>10,4=>6,5=>7,6=>11);
    $con=DatabaseConnection::getInstance();
    $DB= $con->getConnection();
    $DB->setQuery("select * from extra_field_settings");
    $arrExtraField=$DB->getAllAssoc();
    $arrSQL=array();
    $arrSQL[]="insert into auieo_uitype ( `name`,`caption`,`fieldinfo`,`length`) values('textbox','Text Box','2','255')";
    $arrSQL[]="insert into auieo_uitype ( `name`,`caption`,`fieldinfo`,`length`) values('multiline','Multiline Text Box','102','0')";
    $arrSQL[]="insert into auieo_uitype ( `name`,`caption`,`fieldinfo`,`length`) values('checkbox','Check Box','402','255')";
    $arrSQL[]="insert into auieo_uitype ( `name`,`caption`,`fieldinfo`,`length`) values('radiolist','Radio Button List','802','255')";
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
        $objSQL->addValue("is_extra", 1);
        $objSQL->addValue("maximumlength", $maximumlength);

        $objSQL->addValue("site_id",$arrData["site_id"]);
        $arrSQL[]=$objSQL->render();
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
        $arrSQL[]="SET @s = (SELECT IF(
    (SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_name = '{$table}'
        AND table_schema = DATABASE()
        AND column_name = '{$field_name}'
    ) > 0,
    'SELECT 1',
    'ALTER TABLE `{$table}` ADD `{$field_name}` {$fieldType} default NULL'
));
PREPARE stmt FROM @s;
EXECUTE stmt;";
    
        $sqlUpdate="UPDATE `{$table}` as t, 
(
    SELECT data_item_id, field_name, value 
    FROM extra_field where data_item_type={$arrData["data_item_type"]}
) as temp
SET t.`{$field_name}` = temp.value WHERE temp.data_item_id = t.{$table}_id and temp.`field_name`='{$field_name}'";
       //$arrTmpSql= loadAuieoExtraFieldData($arrData["data_item_type"], $arrData["field_name"], $arrData["site_id"],$field_name);
           $arrSQL[]=$sqlUpdate;
    }
    return $arrSQL;
}
function cleanToVariableName($string) {
  // $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.
   $string = preg_replace('/[^A-Za-z0-9\_ ]/', '', $string); // Removes special chars.

   return  $string; // Replaces multiple hyphens with single one.
}
$arrSQL=loadAuieoExtraField();
file_put_contents("extrafield.sql",  implode(";\n",$arrSQL));
echo "extrafield.sql generation completed";
?>