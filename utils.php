<?php
function addLog($message="None",$stepBack=0)
{
    $logger=Logger::getLogger("APF");
    $logger->info($message);
    
}
function extract_emails($str){
    // This regular expression extracts all emails from a string:
    $regexp = '/([a-z0-9_\.\-])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,4})+/i';
    preg_match_all($regexp, $str, $m);

    return isset($m[0]) ? $m[0] : array();
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
function displayMultiColumnTable($AUIEO_PREVIEW_FIELD,$column=2)
{
    foreach($AUIEO_PREVIEW_FIELD as $ind=>$previewField)
    {
        if($ind%$column===0)
        {
            $tr = "<tr>";
        }
        $previewFieldPublic="";
        $previewFieldOther="";
        if($AUIEO_PREVIEW_FIELD[$ind]["public"]) $previewFieldPublic=$AUIEO_PREVIEW_FIELD[$ind]["public"];
         if($AUIEO_PREVIEW_FIELD[$ind]["other"]) $previewFieldOther=$AUIEO_PREVIEW_FIELD[$ind]["other"];
         $data=$AUIEO_PREVIEW_FIELD[$ind]["data"];
         if(isset($previewField["editable"]))
         {
              $sql=$previewField["sql"];
              $key=$previewField["key"];
              if($previewField["editable"]=="editable-bsdate")
              {
                  $data="<a href='#' {$previewField["editable"]}='data.{$key}'  onbeforesave='updateData(\$data,\"{$sql}\")'  e-datepicker-popup='dd-MMMM-yyyy'>{{ (data.{$key} | date:'dd/MM/yyyy') || '-'  }}</a>
     ";
              }
              else
              {
                    $data="<a href='#' {$previewField["editable"]}='data.{$key}'  e-form='{$key}'  onbeforesave='updateData(\$data,\"{$sql}\")'>{{ data.{$key} }}</a>
                   <button class='btn btn-default' ng-click='{$key}.\$show()' ng-hide='{$key}.\$visible'>
    edit
  </button> ";
              }
         }
         $tr = $tr."<td style='min-width:15%;' class='vertical'>{$AUIEO_PREVIEW_FIELD[$ind]["caption"]}:</td>
            <td style='min-width:35%' class='data' width='300'>
                <span class='{$AUIEO_PREVIEW_FIELD[$ind]["class"]}'>{$data}</span>
                    {$previewFieldPublic}
                    {$previewFieldOther}
            </td>";
         if($ind%$column===($column-1))
        {
            $tr = $tr."</tr>";
            echo $tr;
        }
    }
    ///if the table not closed, close it.
     if($ind%$column!==($column-1))
    {
         $tr = $tr."<td class='vertical'></td><td></td></tr>";
            echo $tr;
    }
}
function getReportFilter()
{
    /**
     * array of where condition
     */
    $arrWhere=array();
    if(isset($_REQUEST["filterby"]))
    {
        foreach($_REQUEST["filterby"] as $filterby)
        {
            $fieldModule=$filterby["fieldmodule"];
            $fieldname=$filterby["fieldname"];
            $fielddata=$filterby["fielddata"];
            $fieldcondition=isset($filterby["fieldcondition"])?$filterby["fieldcondition"]:"=";
            $arrWhere[]="`{$fieldModule}`.`{$fieldname}`{$fieldcondition}'{$fielddata}'";
        }
    }
    return $arrWhere;
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

function makePeriodCriterion($dateField, $period,$fromDate=false,$toDate=false)
{
    Logger::getLogger("AuieoATS")->info("utils:makePeriodCriterion entry");
    /* Note: we add a bogus "AND date > '1900-01-01'" condition to the
     * WHERE clause to force MySQL to use an index containing the date
     * column. MySQL can then build the entire result set without scanning
     * any rows.
     */
    $criteria = '';
    switch ($period)
    {
        case "today":
            $criteria = sprintf(
                '%s > \'1900-01-01\' AND DATE(%s) = CURDATE())',
                $dateField,
                $dateField
            );
            break;

        case "week":
            $criteria = sprintf(
                '%s > \'1900-01-01\' AND YEARWEEK(%s) = YEARWEEK(NOW()))',
                $dateField,
                $dateField
            );
            break;
        
        case "month":
            $criteria = sprintf(
                '%s > \'1900-01-01\' AND EXTRACT(YEAR_MONTH FROM %s) = EXTRACT(YEAR_MONTH FROM CURDATE()))',
                $dateField,
                $dateField
            );
            break;
        
        case "range":
            $criteria = sprintf(
                '%s > \'1900-01-01\' AND %s > DATE(\'%s\') AND %s < DATE(\'%s\') )',
                $dateField,
                $dateField,
                date("Y-m-d h:i:s",strtotime($fromDate)),
                $dateField,
                date("Y-m-d h:i:s",strtotime($toDate))
            );
            break;

        case "year":
        default:
            $criteria = sprintf(
                '%s > \'1900-01-01\' AND YEAR(%s) = YEAR(NOW()))',
                $dateField,
                $dateField
            );
            break;
    }

    $timeZoneOffset=$_SESSION['CATS']->getTimeZoneOffset();
    if ($timeZoneOffset != 0)
    {
        $criteria = str_replace('CURDATE()', 'DATE_ADD(CURDATE(), INTERVAL ' . $timeZoneOffset. ' HOUR)', $criteria);
        $criteria = str_replace('NOW()', 'DATE_ADD(NOW(), INTERVAL ' . $timeZoneOffset . ' HOUR)', $criteria);
        $criteria = str_replace($dateField, 'DATE_ADD(' . $dateField . ', INTERVAL ' . $timeZoneOffset . ' HOUR)', $criteria);
    }
    Logger::getLogger("AuieoATS")->info("utils:makePeriodCriterion exit");
    return $criteria;
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
 * 06 = DATE
 * 07 = OWNER
 * 08 = RADIOLIST
 * 09 = CHECKBOXLIST
 * 10 = READONLY
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
            "0"=>"TEXTBOX","1"=>"TEXTAREA","2"=>"SELECT","3"=>"MULTISELECT","4"=>"CHECKBOX","5"=>"RADIO","6"=>"CALENDAR","7"=>"OWNER","8"=>"RADIOLIST","9"=>"CHECKBOXLIST","10"=>"READONLY"
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
function anonymousws_addCandidate($data,$site_id=1)
{
    $candidates = new Candidates($site_id);
    $id=$candidates->getIDByEmail($data["email1"]);
    if($id) return 0;
    else
        return call_user_func_array(array($candidates,"add"), $data);
}
function anonymousws_updateRatingValue($candidateJobOrderID, $value,$site_id=1)
{
    $pipelines = new Pipelines($site_id);
    return $pipelines->updateRatingValue($candidateJobOrderID, -1);
}
function anonymousws_addActivityEntry($candidateID,$dataItem,$otherActivity,$activityNote,$userID,$jobOrderID,$site_id=1)
{
    $activityEntries = new ActivityEntries($site_id);
    return $activityEntries->add($candidateID,$dataItem,$otherActivity,$activityNote,$userID,$jobOrderID);
}
function anonymousws_getEmailTemplateByTag($site_id=1)
{
    $emailTemplates = new EmailTemplates($site_id);
    return $emailTemplates->getByTag(
            'EMAIL_TEMPLATE_CANDIDATEPORTALNEW'
        );
}
function anonymousws_sendEMailToJoborder($jobOrderID,$userID,$email,$subject,$emailContents,$site_id=1)
{
    $jobOrders = new JobOrders($site_id);
    if(!$jobOrders->isLoaded())
    {
        $jobOrders->load($jobOrderID);
    }
    $success = $jobOrders->sendEMail(
        $userID,
        $email,
        $subject,
        $emailContents
    );
    return array("completed"=>true,"result"=>$success,"message"=>$success?"Mail Sent Successfully":"Mail Sent Failed");
}
function anonymousws_sendEMailToCandidate($candidateID,$userID,$email,$subject,$emailContents,$site_id=1)
{
    $candidates = new Candidates($site_id);
    if(!$candidates->isLoaded())
    {
        $candidates->load($candidateID);
    }
    $success = $candidates->sendEMail(
        $userID,
        $email,
        $subject,
        $emailContents
    );
    return array("completed"=>true,"result"=>$success,"message"=>$success?"Mail Sent Successfully":"Mail Sent Failed");
}
function anonymousws_candidatesEmailTemplate($type,$site_id=1)
{
    $emailTemplates = new EmailTemplates($site_id);
    $candidatesEmailTemplateRS = $emailTemplates->getByTag(
        $type
    );
    return $candidatesEmailTemplateRS;
}
function anonymousws_candidateIDByEmail($email,$site_id=1)
{
    $candidates = new Candidates($site_id);
    return $candidates->getIDByEmail($email);
}
function anonymousws_getCareerPortalJobDetail($jobOrderID,$site_id=1)
{
    $jobOrders = new JobOrders($site_id);
    return $jobOrders->get($jobOrderID);
}
function anonymousws_addCandidateToPipeline($candidateID, $jobOrderID,$site_id=1)
{
    $pipelines = new Pipelines($site_id);
    return $pipelines->add($candidateID, $jobOrderID);
}
function anonymousws_getPipelines($candidateID, $jobOrderID,$site_id=1)
{
    $pipelines = new Pipelines($site_id);
    $activityEntries = new ActivityEntries($site_id);
    /* Is the candidate already in the pipeline for this job order? */
    $rs = $pipelines->get($candidateID, $jobOrderID);
    return $rs;
}
function anonymousws_getCareerPortalJobs($site_id=1)
{
    $jobOrders = new JobOrders($site_id);
    return $jobOrders->getAll(JOBORDERS_STATUS_ACTIVE, -1, -1, -1, false, true);
}
function webservice_getUpcomingEventsHTML($limit, $flag = UPCOMING_FOR_CALENDAR,$site_id)
{
    $calendar = new Calendar($site_id);
    return $calendar->getUpcomingEventsHTML($limit, $flag);
}
function webservice_getAllCalendarSettings($site_id=1)
{
    $calendarSettings = new CalendarSettings($site_id);
    $calendarSettingsRS = $calendarSettings->getAll();
}
function webservice_getAllEventTypes($site_id=1)
{
    $calendar = new Calendar($site_id);
    return $calendar->getAllEventTypes();
}
function webservice_getEventArray($month, $year,$site_id=1)
{
    $calendar = new Calendar($site_id);
    return $calendar->getEventArray($month, $year);
}
function anonymousws_getCareerPortalTemplate($templateName,$site_id=1)
{
    $careerPortalSettings = new CareerPortalSettings($site_id);
    $template = $careerPortalSettings->getTemplate($templateName);
    return $template;
}
function anonymousws_getCareerPortalSettings($site_id=1)
{
    $careerPortalSettings = new CareerPortalSettings($site_id);
    $careerPortalSettingsRS = $careerPortalSettings->getAll();
    return $careerPortalSettingsRS;
}
function webservice_getCareerPortalSettings($site_id=1)
{
    $careerPortalSettings = new CareerPortalSettings($site_id);
    $careerPortalSettingsRS = $careerPortalSettings->getAll();
    return $careerPortalSettingsRS;
}
function webservice_getModuleInfo($keyfield=false,$site_id=1)
{
    return getModuleInfo($keyfield,$site_id);
}
/**
 * get module details with keyfield as module name and value as module info as associative array
 * @staticvar array $arrDataItemType
 * @staticvar array $arrDataItemTypeKeyfield
 * @param type $keyfield
 * @return type
 */
function getModuleInfo($keyfield=false,$site_id=false)
{
    static $arrDataItemType=array();
    static $arrDataItemTypeKeyfield=array();
    if(empty($arrDataItemType))
    {
        if($site_id===false) $site_id=$_SESSION["CATS"]->getSiteID();
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