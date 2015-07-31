<?php
function addLog($message="None",$stepBack=0)
{
    $logger=Logger::getLogger("APF");
    $logger->info($message);
    
}
function getModuleFieldsInfo($data_item_type)
{
    static $arrModuleField=array();
    if(!isset($arrModuleField[$data_item_type]))
    {
        $objSQL=new ClsAuieoSQL();
        $objFromCandidate=$objSQL->addFrom("auieo_fields");
        $objSQL->addWhere($objFromCandidate, "data_item_type", $data_item_type);
        $objSQL->addWhere($objFromCandidate, "site_id", $_SESSION['CATS']->getSiteID());
        $objSQL->addOrderBy("sequence");
        $sql=$objSQL->render();
        $db=  DatabaseConnection::getInstance();
        $arrFieldRecord=$db->getAllAssoc($sql);
        $arrModuleField[$data_item_type]=$arrFieldRecord;
    }
    return $arrModuleField[$data_item_type];
}
function getModuleFields($data_item_type)
{
    static $arrModuleField=array();
    if(!isset($arrModuleField[$data_item_type]))
    {
        $objSQL=new ClsAuieoSQL();
        $objFromCandidate=$objSQL->addFrom("auieo_fields");
        $objSQL->addWhere($objFromCandidate, "data_item_type", $data_item_type);
        $objSQL->addWhere($objFromCandidate, "site_id", $_SESSION['CATS']->getSiteID());
        $objSQL->addOrderBy("sequence");
        $sql=$objSQL->render();
        $db=  DatabaseConnection::getInstance();
        $arrFieldRecord=$db->getAllAssoc($sql);

        foreach($arrFieldRecord as $ind=>$record)
        {
            if($record["sequence"]>0) continue;
            $sql="update auieo_fields set sequence=".($ind+1)." where id={$record["id"]}";
            $db->query($sql);
            $arrFieldRecord[$ind]["sequence"]=$ind+1;
        }
        $arrCalculateField=getAVFields($data_item_type);
        if($arrCalculateField)
        foreach ($arrCalculateField as $ind=>$fieldinfo)
        {
            $arrFieldRecord[]=$fieldinfo["definition"];
        }
        $arrModuleField[$data_item_type]=$arrFieldRecord;
    }
    return $arrModuleField[$data_item_type];
}
function getAliasNameFromField($field)
{
    if(strpos($field,"_"))
    {
        $arrExp = explode("_", $field);
        $alias = array_shift($arrExp);
        if($arrExp)
        foreach($arrExp as $exp)
        {
            if($exp=="id")
            {
                $alias=$alias."ID";
            }
            else
            {
                $alias=$alias.ucfirst($exp);
            }
        }
        return $alias;
    }
    return $field;
}
function getAlternatingRowClass($rowNumber)
{
    /* Is the row number even? */
    if (($rowNumber % 2) == 0)
    {
        return 'evenTableRow';
    }

    return 'oddTableRow';
}
/**
 * 
 * @staticvar array $arrColumn
 * @param type $data_item_type
 * @param type $joinExtraField - if true, joins both extra field and regular table field in single associative array
 * @return type
 */
function getColumnMeta($data_item_type,$joinExtraField=false)
{
    static $arrColumn=array();
    if(!isset($arrColumn[$data_item_type]))
    {
        $table="";
        switch ($data_item_type)
        {
            case 100:
            {
                $table="candidate";
                break;
            }
            case 200:
            {
                $table="company";
                break;
            }
            case 300:
            {
                $table="contact";
                break;
            }
            case 400:
            {
                $table="joborder";
                break;
            }
        }
        if(empty($table)) trace("unknown data item type:{$data_item_type}");
        $objDatabase = DatabaseConnection::getInstance();
        $sql="SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".DATABASE_NAME."' AND TABLE_NAME = '$table'";
        $arrRow=$objDatabase->getAllAssoc($sql);
        foreach($arrRow as $row)
        {
            if($row["COLUMN_KEY"]=="PRI" || $row["COLUMN_KEY"]=="MUL") continue;
            if($joinExtraField)
            {
                $arrColumn[$data_item_type][$row["COLUMN_NAME"]]="";
            }
            else
                $arrColumn[$data_item_type]["regular"][$row["COLUMN_NAME"]]="";
        }
        $sql="SELECT * FROM extra_field_settings WHERE data_item_type='{$data_item_type}'";
        $arrRow=$objDatabase->getAllAssoc($sql);
        foreach($arrRow as $row)
        {
            if($joinExtraField)
            {
                $arrColumn[$data_item_type][$row["field_name"]]=$row["extra_field_settings_id"];
            }
            else
                $arrColumn[$data_item_type]["extra"][$row["field_name"]]=$row["extra_field_settings_id"];
        }
    }
    return $arrColumn[$data_item_type];
}
/**
 * if the parameter is field name from main table, the same will be return
 * if the parameter is extrafield, the id of the extrafield will be returned
 */
function getRealFieldName($data_item_type,$fieldOrExtraField)
{
    $arrColumn=getColumnMeta($data_item_type);
    if(isset($arrColumn["regular"][$fieldOrExtraField])) return $fieldOrExtraField;
    if(isset($arrColumn["extra"][$fieldOrExtraField])) return $arrColumn["extra"][$fieldOrExtraField];
    return false;
}
function getPagination($href,$totalItems,$currentPage=1,$itemsPerPage=20,$param="")
{
	$pagina=new PaginationUI($totalItems,$currentPage,$itemsPerPage);
	$pagina->paginationVar="pv";
	$prev=$pagina->getUIPrevLink($href,"Prev",$param);
	$next=$pagina->getUINextLink($href,"Next",$param);
	$first=$pagina->getUIFirstLink($href,"First",$param);
	$last=$pagina->getUILastLink($href,"Last",$param);
	$numPage=$pagina->getNumPage(); 
	$pagination=$first." ".$prev." "."(".$currentPage." of ".$numPage.")"." ".$next." ".$last;
	return $pagination;
}
function getLangVar($_AUIEO_FIELD_NAME,$_AUIEO_FIELD_MODULE=false)
{
    static $arrData=array();
    //trace($_AUIEO_FIELD_NAME);
    //$_AUIEO_FIELD_NAME=  substr($_AUIEO_FIELD_NAME, strpos($_AUIEO_FIELD_NAME, "AUIEO_LANG"));
    if(isset($arrData[$_AUIEO_FIELD_NAME])) return $arrData[$_AUIEO_FIELD_NAME];
    
    $language=getAppConfig("LANGUAGE");
    
    if(!$language) $language ="default";
    
    //if($arraData($_AUIEO_FIELD_NAME)=="")
    if(file_exists("lang/{$language}/common.php")) include "lang/{$language}/common.php"; //trace($incfile);
    
    if(!empty($_AUIEO_FIELD_MODULE) && file_exists("modules/{$_AUIEO_FIELD_MODULE}/lang/{$language}/common.php")) include "modules/{$_AUIEO_FIELD_MODULE}/lang/{$language}/common.php";

    if(isset($$_AUIEO_FIELD_NAME))
    {
        $arrData[$_AUIEO_FIELD_NAME]=$$_AUIEO_FIELD_NAME;
    }
    else
    {
        $arrData[$_AUIEO_FIELD_NAME]="";
    }
    return $arrData[$_AUIEO_FIELD_NAME];
}
function getModules()
{
    static $arrModule=false;
    if(empty($arrModule))
    {
        $site_id=$_SESSION["CATS"]->getSiteID();
        $sql="select * from data_item_type where site_id={$site_id}";
        $objDB=DatabaseConnection::getInstance();
        $arrRecord=$objDB->getAllAssoc($sql);
        foreach($arrRecord as $records)
        {
            $arrTmp=getTableInfoByDataItemType($records["data_item_type_id"]);
            $arrTmp["data_item_type_id"]=$records["data_item_type_id"];
            $arrModule[$records["data_item_type_id"]]=$arrTmp;
        }
    }
    return $arrModule;
}
function getAVFields($data_item_type,$record=false)
{
    $_AUIEO_ACTION=$_REQUEST["a"];//trace($_AUIEO_ACTION);
    $path=false;
    switch ($data_item_type)
    {
        case 100:
        {
            $path="modules/candidates/avfields/{$_AUIEO_ACTION}.php";
            break;
        }
        case 200:
        {
            $path="modules/companies/avfields/{$_AUIEO_ACTION}.php";
            break;
        }
        case 300:
        {
            $path="modules/contacts/avfields/{$_AUIEO_ACTION}.php";
            break;    
        }
        case 400:
        {
            $path="modules/joborders/avfields/{$_AUIEO_ACTION}.php";
            break;
        }
    }
    if($path!==false && file_exists($path))
    {
        $__AUIEO_RECORD=$record;
        $__AUIEO_DATA_ITEM_TYPE=$data_item_type;
        unset($_AUIEO_ACTION);
        unset($record);
        include($path);
        unset($path);
        unset($data_item_type);
        $arrVar=get_defined_vars();//trace($arrVar);
        $acRecord=array();
        foreach($arrVar as $functionName=>$function)
        {
            if($functionName=="__AUIEO_RECORD" || $functionName=="__AUIEO_DATA_ITEM_TYPE" || $functionName=="php_errormsg") continue;
            //trace($functionName);
            if($functionName===false)
            {
                $ret["definition"]["fieldname"]=$functionName;//trace($ret);
                $ret["definition"]["data_item_type"]=$__AUIEO_DATA_ITEM_TYPE;
                $acRecord[]=$ret;
            }
            else
            {//trace($function);
                $ret=$function($__AUIEO_RECORD);//trace($function);
                $ret["definition"]["fieldname"]=$functionName;
                $ret["definition"]["data_item_type"]=$__AUIEO_DATA_ITEM_TYPE;
                $acRecord[]=$ret;
            }
        }
        return $acRecord;
    }
    return false;
}
/**
 * last two digit indicates the datatype
 * 00 = TEXT
 * 01 = CHAR
 * 02 = VARCHAR
 * 03 = TINYTEXT
 * 04 = MEDIUMTEXT
 * 05 = LONGTEXT
 * 06 = BLOB
 * 07 = TINYBLOB
 * 08 = MEDIUMBLOB
 * 09 = LONGBLOB
 * 10 = ENUM
 * 11 = SET
 * 12 = BINARY
 * 14 = VARBINARY
 * 20 = TINYINT
 * 21 = SMALLINT
 * 22 = MEDIUMINT
 * 23 = INT
 * 24 = BIGINT
 * 25 = FLOAT
 * 26 = DOUBLE
 * 27 = DECIMAL
 * 28 = REAL
 * 29 = BIT
 * 30 = BOOLEAN
 * 31 = SERIAL
 * 40 = DATE
 * 41 = DATETIME
 * 42 = TIMESTAMP
 * 43 = TIME
 * 44 = YEAR
 * 60 = GEOMETRY
 * 61 = POINT
 * 62 = LINESTRING
 * 63 = POLYGON
 * 64 = MULTIPOINT
 * 65 = MULTILINESTRING
 * 66 = MULTIPOLYGON
 * 67 = GEOMETRYCOLLECTION
 * second last two digt inticates the HTML or Javascript control
 * 00 = TEXTBOX
 * 01 = TEXTAREA
 * 02 = SELECT
 * 03 = MULTISELECT
 * 04 = CHECKBOX
 * 05 = RADIO
 * 06 = READONLY
 * thrid last two digit, fourth last two digit and fifth last two digits indicates the binary word for input character type
 * 26 type of character representation possible
 * 0 = any characters
 * 1 = small alphabets
 * 2 = capital alphabets
 * 4 = 
 * 8 = 
 * 16 = 
 * 32 = space
 * 64 = @
 * 128 = _
 * 256 = .
 * 512 = /
 * 1024 = +
 * 2048 = -
 * 4096 = ( and )
 * 8192 = :
 * 16384 = positive numbers
 * 32768 = negative numbers
 * 65536 = positive real numbers
 * 131072 = negative real numbers
 */
function getFieldInfoByUIType($fieldinfoint)
{
    static $arrFieldInfo;
    if(!isset($arrFieldInfo[$fieldinfoint]))
    {
        $fieldinfonum=$fieldinfoint;
        $arrDataType=array
        (
            "0"=>"TEXT","1"=>"CHAR","2"=>"VARCHAR","3"=>"TINYTEXT","4"=>"MEDIUMTEXT",
            "5"=>"LONGTEXT", "6"=>"BLOB", "7"=>"TINYBLOB", "8"=>"MEDIUMBLOB", "9"=>"LONGBLOB",
            "10"=>"ENUM", "11"=>"SET", "12"=>"BINARY", "14"=>"VARBINARY",
            "20"=>"TINYINT", "21"=>"SMALLINT", "22"=>"MEDIUMINT", "23"=>"INT", "24"=>"BIGINT",
            "25"=>"FLOAT", "26"=>"DOUBLE", "27"=>"DECIMAL", "28"=>"REAL", "29"=>"BIT",
            "30"=>"BOOLEAN", "31"=>"SERIAL",
            "40"=>"DATE", "41"=>"DATETIME", "42"=>"TIMESTAMP", "43"=>"TIME", "44"=>"YEAR",
            "60"=>"GEOMETRY", "61"=>"POINT", "62"=>"LINESTRING", "63"=>"POLYGON", "64"=>"MULTIPOINT",
            "65"=>"MULTILINESTRING", "66"=>"MULTIPOLYGON", "67"=>"GEOMETRYCOLLECTION"
        );
        $arrUIControl=array
        (
            "0"=>"TEXTBOX","1"=>"TEXTAREA","2"=>"SELECT","3"=>"MULTISELECT","4"=>"CHECKBOX","5"=>"RADIO","6"=>"CALENDAR","7"=>"OWNER"
        );
        /**
         * get last two digit
         */
        $datatype = $fieldinfonum % 100;
        $fieldinfonum = ($fieldinfonum - $datatype)/100;
        $uicontrol=0;
        $arrCharacterAllowed=array();
        if($fieldinfonum>0)
        {
            /**
             * get second last two digit
             */
            $uicontrol = $fieldinfonum % 100;
            $fieldinfonum=($fieldinfonum-$uicontrol)/100;
            if($fieldinfonum>0)
            {
                $fieldinfonum=base_convert($fieldinfonum, 10, 2);
                $len=strlen($fieldinfonum);
                for($i=$len-1,$elapsedDigit=0;$i>=0;$i--,$elapsedDigit++)
                {
                    if($fieldinfonum[$i]==1)
                    {
                        $arrCharacterAllowed[]=base_convert(str_pad("1", $elapsedDigit+1, "0"), 2, 10);
                    }
                }
            }
        }
        $arrFieldInfo[$fieldinfoint]=array("allowedChars"=>$arrCharacterAllowed,"datatype"=>isset($arrDataType[$datatype])?$arrDataType[$datatype]:false,"uicontrol"=>isset($arrUIControl[$uicontrol])?$arrUIControl[$uicontrol]:false);
    }
    return $arrFieldInfo[$fieldinfoint];
}
/**
 * get module details with keyfield as module name and value as module info as associative array
 * @staticvar array $arrDataItemType
 * @staticvar array $arrDataItemTypeKeyfield
 * @param type $keyfield
 * @return type
 */
function getModuleInfo($keyfield=false)
{
    static $arrDataItemType=array();
    static $arrDataItemTypeKeyfield=array();
    if(empty($arrDataItemType))
    {
        $site_id=$_SESSION["CATS"]->getSiteID();
        $objDB=DatabaseConnection::getInstance();
        $arrAssoc=$objDB->getAllAssoc("select * from data_item_type where site_id={$site_id}");
        foreach($arrAssoc as $rec)
        {
            $arrDataItemType[]=$rec;
            $arrDataItemTypeKeyfield["data_item_type"][$rec["data_item_type_id"]]=$rec;
            $arrDataItemTypeKeyfield["modulename"][$rec["modulename"]]=$rec;
            $arrDataItemTypeKeyfield["tablename"][$rec["tablename"]]=$rec;
        }
    }
    if($keyfield===false || !isset($arrDataItemTypeKeyfield[$keyfield]))
    {
        return $arrDataItemType;
    }
    return $arrDataItemTypeKeyfield[$keyfield];
}
function getTableInfoByDataItemType($data_item_type)
{
    static $arrTableInfo=array();
    if(!isset($arrTableInfo[$data_item_type]))
    {
        $table="";
        $primaryKey="";
        $module="";
        $arrDataItemType=getModuleInfo("data_item_type");
        if(isset($arrDataItemType[$data_item_type]))
        {
            $table=$arrDataItemType[$data_item_type]["tablename"];
            $primaryKey=$arrDataItemType[$data_item_type]["primarykey"];
            $module=$arrDataItemType[$data_item_type]["modulename"];
        }
        $arrTableInfo[$data_item_type] = array("table"=>$table,"primary_key"=>$primaryKey,"module"=>$module);
    }
    return $arrTableInfo[$data_item_type];
}
function getTableInfoByModule($_AUIEO_MODULE)
{
    static $arrTableInfo=array();
    if(!isset($arrTableInfo[$_AUIEO_MODULE]))
    {
        $table="";
        $primaryKey="";
        $module="";
        $arrDataItemType=getModuleInfo("modulename");
        if(isset($arrDataItemType[$_AUIEO_MODULE]))
        {
            $table=$arrDataItemType[$_AUIEO_MODULE]["tablename"];
            $primaryKey=$arrDataItemType[$_AUIEO_MODULE]["primarykey"];
            $module=$arrDataItemType[$_AUIEO_MODULE]["modulename"];
            $data_item_type=$arrDataItemType[$_AUIEO_MODULE]["data_item_type_id"];
        }
        $arrTableInfo[$_AUIEO_MODULE] = array("table"=>$table,"primary_key"=>$primaryKey,"module"=>$module,"data_item_type"=>$data_item_type);
    }
    return $arrTableInfo[$_AUIEO_MODULE];
    /*$table="";
    $primaryKey="";
    switch ($module)
    {
        case "candidates":
        {
            $table="candidate";
            $primaryKey="candidate_id";
            $module="candidates";
            $data_item_type=100;
            break;
        }
        case "companies":
        {
            $table="company";
            $primaryKey="company_id";
            $module="companies";
            $data_item_type=200;
            break;
        }
        case "contacts":
        {
            $table="contact";
            $primaryKey="contact_id";
            $module="contacts";
            $data_item_type=300;
            break;
        }
        case "joborders":
        {
            $table="joborder";
            $primaryKey="joborder_id";
            $module="joborders";
            $data_item_type=400;
            break;
        }
    }
    return array("table"=>$table,"primary_key"=>$primaryKey,"module"=>$module,"data_item_type"=>$data_item_type);*/
}
?>