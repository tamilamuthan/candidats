<?php
/* 
 * CandidATS
 * Candidates
 *
 * Copyright (C) 2014 - 2015 Auieo Software Private Limited, Parent Company of Unicomtech.
 * 
 * This Modified Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */




/**
 *  Projects Library
 *  @package    CandidATS
 *  @subpackage Library
 */
class Projects extends Modules
{
    public $extraFields;
    private $record=array();
    private $extraRecord;
    protected $module="projects";
    protected $module_table="auieo_projects";
    protected $module_id="candidate_id";
    protected $data_item_type=0;

    public function __construct($siteID)
    {
        Logger::getLogger("AuieoATS")->info("Projects constructor");
        $this->data_item_type=500;
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        //$this->extraFields = new ExtraFields($siteID, 500);
    }
    
    public static function getInstance()
    {
        static $objProject=null;
        if(is_null($objProject))
        {
            $siteID=$_SESSION["CATS"]->getSiteID();
            $objProject=new Projects($siteID);
        }
        return $objProject;
    }
    
    public static function getActiveList()
    {
        $sql="select id,name from auieo_projects";
        $whereSQL=getPermittedRecordWhere("projects");
        if($whereSQL)
        {
            $sql = $sql." where ".implode($whereSQL, ' AND '."\n")." AND status IN (1,2)";
        }
        else
        {
            $sql = $sql." where  AND status IN (1,2)";
        }
        $records=DatabaseConnection::getInstance()->getAllAssoc($sql);
        $arrList=array();
        if($records)
        {
            foreach($records as $record)
            {
                $arrList[$record["id"]]=$record["name"];
            }
        }
        return $arrList;
    }
    
    public static function actionMapping()
    {
        return array(
            "listing"=>4,
            "add"=>0,
            "edit"=>1,
            "show"=>2,
            "delete"=>3,
            "default"=>"listing"
        );
    }
    
    public function __get($var)
    {
        if(strpos($var, "EXTRA_")===0)
        {
            $arrVar=explode("EXTRA_",$var);
            $var=$arrVar[1];
            return isset($this->extraRecord[$var])?$this->extraRecord[$var]:"";
        }
        else if(isset($this->$var))
        {
            return $this->$var;
        }
        else if (isset($this->record[$var]))
        {
            return $this->record[$var];
        }
        else
        {
            return null; 
        }
    }

    /**
     * Adds a candidate to the database and returns its candidate ID.
     *
     * @param boolean Skip creating a history entry?
     * @return integer Candidate ID of new candidate, or -1 on failure.
     */
    public function add(
        $skipHistory = false)
    {
        Logger::getLogger("AuieoATS")->info("Projects in add method");
        $record=  get_defined_vars();
        $arrFieldsInfo=getModuleFieldsInfo($data_item_type);
        trace($arrFieldsInfo);
        $hook=_AuieoHook("projects_insert_before");
        if($hook)
        {
            $hook($record);
        }
        $objSQL=new ClsAuieoSQL("INSERT");
        $objSQL->addFrom("auieo_projects");
        $objSQL->addValue("name", $_REQUEST["name"]);
        $objSQL->addValue("site_id", $_SESSION["CATS"]->getSiteID());
        $sql=$objSQL->render();
        $objDB=DatabaseConnection::getInstance();
        $objDB->query($sql);
        $sql = sprintf(
            "INSERT INTO candidate (
                first_name,
                middle_name,
                last_name,
                email1,
                email2,
                phone_home,
                phone_cell,
                phone_work,
                address,
                city,
                state,
                zip,
                source,
                key_skills,
                date_available,
                current_employer,
                can_relocate,
                current_pay,
                desired_pay,
                notes,
                web_site,
                best_time_to_call,
                entered_by,
                is_hot,
                owner,
                site_id,
                date_created,
                date_modified,
                eeo_ethnic_type_id,
                eeo_veteran_type_id,
                eeo_disability_status,
                eeo_gender
            )
            VALUES (
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                0,
                %s,
                %s,
                NOW(),
                NOW(),
                %s,
                %s,
                %s,
                %s
            )",
            $this->_db->makeQueryString($firstName),
            $this->_db->makeQueryString($middleName),
            $this->_db->makeQueryString($lastName),
            $this->_db->makeQueryString($email1),
            $this->_db->makeQueryString($email2),
            $this->_db->makeQueryString($phoneHome),
            $this->_db->makeQueryString($phoneCell),
            $this->_db->makeQueryString($phoneWork),
            $this->_db->makeQueryString($address),
            $this->_db->makeQueryString($city),
            $this->_db->makeQueryString($state),
            $this->_db->makeQueryString($zip),
            $this->_db->makeQueryString($source),
            $this->_db->makeQueryString($keySkills),
            $this->_db->makeQueryStringOrNULL($dateAvailable),
            $this->_db->makeQueryString($currentEmployer),
            ($canRelocate ? '1' : '0'),
            $this->_db->makeQueryString($currentPay),
            $this->_db->makeQueryString($desiredPay),
            $this->_db->makeQueryString($notes),
            $this->_db->makeQueryString($webSite),
            $this->_db->makeQueryString($bestTimeToCall),
            $this->_db->makeQueryInteger($enteredBy),
            $this->_db->makeQueryInteger($owner),
            $this->_siteID,
            $this->_db->makeQueryInteger($race),
            $this->_db->makeQueryInteger($veteran),
            $this->_db->makeQueryString($disability),
            $this->_db->makeQueryString($gender)
        );
        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        $candidateID = $this->_db->getLastInsertID();

        $hook=_AuieoHook("candidates_insert_after");
        if($hook)
        {
            $record["id"]=$candidateID;
            $hook($record);
        }
        
        if (!$skipHistory)
        {
            $history = new History($this->_siteID);
            $history->storeHistoryNew(DATA_ITEM_CANDIDATE, $candidateID);
        }

        return $candidateID;
    }
    
    public function get($projectID)
    {
        $arrModuleInfo=getModuleInfo("data_item_type");
        $moduleInfo=$arrModuleInfo[500];
        $arrFieldsInfo=getModuleFieldsInfo(500);
        $objSQL=new ClsAuieoSQL();
        $objFrom=$objSQL->addFrom($moduleInfo["tablename"]);
        $objSQL->addSelect($objFrom,$moduleInfo["primarykey"]);
        $objFromDropDownData=false;
        $objFromDropDown=false;
        $objOwnerUser=false;
        foreach($arrFieldsInfo as $fieldInfo)
        {
            /**
             * if custom dropdown exist, join the tables related to custom dropdown
             */
            if($fieldInfo["uitype"]>10000)
            {
                /**
                 * if from object for dropdown tables not exist add it else ignore it
                 * make sure the tales added only once
                 */
                if($objFromDropDownData===false)
                {
                    $joinProjectsID=$objFrom->addJoinField("id");
                    $joinStatus=$objFrom->addJoinField("status");
                    $objFromDropDownData=$objSQL->addFrom("auieo_dropdowndata");
                    $joinDropdonwDataID=$objFromDropDownData->addJoinField("id");
                    $joinDropdownDataDropdownID=$objFromDropDownData->addJoinField("dropdown_id");
                    $objFromDropDown=$objSQL->addFrom("auieo_dropdown");
                    $joinDropdownID=$objFromDropDown->addJoinField("id");
                    
                    $objFromDropDownData->setJoinWith($objFrom, $joinStatus, $joinDropdonwDataID);
                    $objFromDropDown->setJoinWith($objFromDropDownData, $joinDropdownDataDropdownID, $joinDropdownID);
                }
                
            }
            /**
             * if owner field exist
             */
            if($fieldInfo["uitype"]==5)
            {
                $objOwnerUser=new ClsAuieoSQLFrom();
                $joinOwner=$objFrom->addJoinField("owner");
                $objOwnerUser=$objSQL->addFrom("user");
                $joinOwnerUser=$objOwnerUser->addJoinField("user_id");
                $objOwnerUser->setJoinWith($objFrom, $joinOwner, $joinOwnerUser);
                $objSQL->addSelect($objOwnerUser,"user_name","owner_user_name");
                $objSQL->addSelect($objOwnerUser,"first_name","owner_first_name");
                $objSQL->addSelect($objOwnerUser,"last_name","owner_last_name");
            }
            if($objFromDropDownData!==false)
            {
                $objSQL->addSelect($objFromDropDownData,"data",$fieldInfo["fieldname"]);
            }
            else
            {
                $objSQL->addSelect($objFrom,$fieldInfo["fieldname"]);
            }
        }
        $objSQL->addWhere($objFrom,"id", $projectID);
        $objSQL->addWhere($objFrom,"site_id", $this->_siteID);
        $sql=$objSQL->render();
        return $this->_db->getAssoc($sql);
    }

    public function getJoborders($projectID)
    {
        $arrModuleInfo=getModuleInfo("data_item_type");
        $moduleInfo=$arrModuleInfo[400];
        $arrFieldsInfo=getModuleFieldsInfo(400);
        $objSQL=new ClsAuieoSQL();
        $objSQL->setDistinct();
        $objFrom=$objSQL->addFrom($moduleInfo["tablename"]);
        $objSQL->addSelect($objFrom,$moduleInfo["primarykey"],"id");
        $objSQL->addSelect($objFrom,"title","joborder");
        $objFromDropDownData=false;
        $objFromDropDown=false;
        $objOwnerUser=false;
        foreach($arrFieldsInfo as $fieldInfo)
        {
            /**
             * if custom dropdown exist, join the tables related to custom dropdown
             */
            if($fieldInfo["uitype"]>10000)
            {
                /**
                 * if from object for dropdown tables not exist add it else ignore it
                 * make sure the tales added only once
                 */
                if($objFromDropDownData===false)
                {
                    $joinProjectsID=$objFrom->addJoinField("id");
                    $joinStatus=$objFrom->addJoinField("status");
                    $objFromDropDownData=$objSQL->addFrom("auieo_dropdowndata");
                    $joinDropdonwDataID=$objFromDropDownData->addJoinField("id");
                    $joinDropdownDataDropdownID=$objFromDropDownData->addJoinField("dropdown_id");
                    $objFromDropDown=$objSQL->addFrom("auieo_dropdown");
                    $joinDropdownID=$objFromDropDown->addJoinField("id");
                    
                    $objFromDropDownData->setJoinWith($objFrom, $joinStatus, $joinDropdonwDataID);
                    $objFromDropDown->setJoinWith($objFromDropDownData, $joinDropdownDataDropdownID, $joinDropdownID);
                }
                
            }
            /**
             * if owner field exist
             */
            if($fieldInfo["uitype"]==5)
            {
                $objOwnerUser=new ClsAuieoSQLFrom();
                $joinOwner=$objFrom->addJoinField("owner");
                $objOwnerUser=$objSQL->addFrom("user");
                $joinOwnerUser=$objOwnerUser->addJoinField("user_id");
                $objOwnerUser->setJoinWith($objFrom, $joinOwner, $joinOwnerUser);
                $objSQL->addSelect($objOwnerUser,"user_name","owner_user_name");
                $objSQL->addSelect($objOwnerUser,"first_name","owner_first_name");
                $objSQL->addSelect($objOwnerUser,"last_name","owner_last_name");
            }
            if($objFromDropDownData!==false)
            {
                $objSQL->addSelect($objFromDropDownData,"data",$fieldInfo["fieldname"]);
            }
            else
            {
                $objSQL->addSelect($objFrom,$fieldInfo["fieldname"]);
            }
        }
        $joinJoborderID=$objFrom->addJoinField("joborder_id");
        $objProjectsJoborder=$objSQL->addFrom("auieo_projects_joborder");
        $joinProjectsJoborder=$objProjectsJoborder->addJoinField("joborderid");
        $objProjectsJoborder->setJoinWith($objFrom,$joinJoborderID,$joinProjectsJoborder);
        
        $objSQL->addSelect($objProjectsJoborder, "startdate");
        $objSQL->addSelect($objProjectsJoborder, "targetenddate");
        
        $objSQL->addWhere($objProjectsJoborder,"projectsid", $projectID);
        //$objWhere=new ClsAuieoSQLWhere();
        $objWhere=$objSQL->addWhere($objProjectsJoborder,"actualenddate", "0000-00-00 00:00:00.000000");
        $objSQL->addWhere($objFrom,"site_id", $this->_siteID);
        $sql=$objSQL->render();//trace($sql);
        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns the number of candidates in the system.  Useful
     * for determining if the friendly "no candidates in system"
     * should be displayed rather than the datagrid.
     *
     * @param boolean Include administratively hidden candidates?
     * @return integer Number of Candidates in site.
     */
    public function getCount($page = 1)
    {
        if (!$allowAdministrativeHidden)
        {
            $adminHiddenCriterion = 'AND candidate.is_admin_hidden = 0';
        }
        else
        {
            $adminHiddenCriterion = '';
        }

        $sql = sprintf(
            "SELECT
                COUNT(*) AS totalCandidates
            FROM
                candidate
            WHERE
                candidate.site_id = %s
            %s",
            $this->_siteID,
            $adminHiddenCriterion
        );

        return $this->_db->getColumn($sql, 0, 0);
    }

    /**
     * Returns the projects list.
     *
     * @return array Multi-dimensional associative result set array of
     *               candidates data, or array() if no records were returned.
     */
    public function getAll($page=1)
    {
        $arrModuleInfo=getModuleInfo("data_item_type");
        $moduleInfo=$arrModuleInfo[500];
        $arrFieldsInfo=getModuleFieldsInfo(500);
        $objSQL=new ClsAuieoSQL();
        $objFrom=$objSQL->addFrom($moduleInfo["tablename"]);
        $objSQL->addSelect($objFrom,$moduleInfo["primarykey"]);
        $objFromDropDownData=false;
        $objFromDropDown=false;
        foreach($arrFieldsInfo as $fieldInfo)
        {
            /**
             * if custom dropdown exist, join the tables related to custom dropdown
             */
            if($fieldInfo["uitype"]>10000)
            {
                /**
                 * if from object for dropdown tables not exist add it else ignore it
                 * make sure the tales added only once
                 */
                if($objFromDropDownData===false)
                {
                    $objFromDropDownData=new ClsAuieoSQLFrom();
                    $joinProjectsID=$objFrom->addJoinField("id");
                    $joinStatus=$objFrom->addJoinField("status");
                    $objFromDropDownData=$objSQL->addFrom("auieo_dropdowndata");
                    $joinDropdonwDataID=$objFromDropDownData->addJoinField("id");
                    $joinDropdownDataDropdownID=$objFromDropDownData->addJoinField("dropdown_id");
                    $objFromDropDown=$objSQL->addFrom("auieo_dropdown");
                    $joinDropdownID=$objFromDropDown->addJoinField("id");
                    
                    $objFromDropDownData->setJoinWith($objFrom, $joinStatus, $joinDropdonwDataID);
                    $objFromDropDown->setJoinWith($objFromDropDownData, $joinDropdownDataDropdownID, $joinDropdownID);
                }
                
            }
            if($objFromDropDownData!==false)
            {
                $objSQL->addSelect($objFromDropDownData,"data",$fieldInfo["fieldname"]);
            }
            else
            {
                $objSQL->addSelect($objFrom,$fieldInfo["fieldname"]);
            }
        }
        $objSQL->addWhere($objFrom,"site_id", $this->_siteID);
        loadPermittedRecordWhere($objSQL, 500);
        $objSQL->setLimit(($page-1)*20, 20);
        $objSQL->setVersion(2);
        $sql=$objSQL->render();
        return $this->_db->getAllAssoc($sql);
    }
    
 
}

?>
