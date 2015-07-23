<?php
/**
 * CATS
 * Job Orders Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: JobOrders.php 3829 2007-12-11 21:17:46Z brian $
 */

/* 
 * CandidATS
 * Document to Text Conversion Library
 *
 * Copyright (C) 2014 - 2015 Auieo Software Private Limited, Parent Company of Unicomtech.
 * 
 * This Modified Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

define('JOBORDERS_STATUS_ACTIVE',        100);
define('JOBORDERS_STATUS_ONHOLD',        200);
define('JOBORDERS_STATUS_FULL',          300);
define('JOBORDERS_STATUS_PLACED',        400);
define('JOBORDERS_STATUS_LOST',          500);
define('JOBORDERS_STATUS_CLOSED',        600);
define('JOBORDERS_STATUS_UPCOMING_LEAD', 700);
define('JOBORDERS_STATUS_CANCELED',      800);

define('JOBORDERS_STATUS_ALL',              10100);
define('JOBORDERS_STATUS_ONHOLDFULL',       10200);
define('JOBORDERS_STATUS_ACTIVEONHOLDFULL', 10300);

include_once('./lib/Pipelines.php');
include_once('./lib/Calendar.php');
include_once('./lib/Pager.php');
include_once('./lib/History.php');
include_once('./lib/DataGrid.php');

/**
 *	Job Orders Library
 *	@package    CATS
 *	@subpackage Library
 */
class JobOrders extends Modules
{
    public $extraFields;
    protected $module="joborders";
    protected $module_table="joborder";
    protected $module_id="joborder_id";

    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        $this->extraFields = new ExtraFields($siteID, DATA_ITEM_JOBORDER);
        parent::__construct();
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

    // FIXME: Document me.
    public function getExport($IDs)
    {
        if (count($IDs) != 0)
        {
            $IDsValidated = array();
            
            foreach ($IDs as $id)
            {
                $IDsValidated[] = $this->_db->makeQueryInteger($id);
            }
            
            $criterion = 'AND '.$this->module_table.'.'.$this->module_id.' IN ('.implode(',', $IDsValidated).')';
        }
        else
        {
            $criterion = '';
        }

        $sql = sprintf(
            "SELECT
                joborder.joborder_id AS joborderID,
                joborder.title AS Title,
                joborder.city AS City,
                joborder.state AS State
            FROM
                joborder
            WHERE
                joborder.site_id = %s
                %s
            ",
            $this->_siteID,
            $criterion
        );

        return $this->_db->getAllAssoc($sql);
    }
    
    /**
     * Adds a job order to the database and returns its job order ID.
     *
     * @param string title
     * @param integer company ID
     * @param integer contact ID
     * @param string job description
     * @param string job order notes
     * @param string duration
     * @param string maximum rate
     * @param string job order type
     * @param boolean is job order hot
     * @param string (numeric) number of openings
     * @param string salary
     * @param string city
     * @param string state
     * @param string start date
     * @param integer entered-by user
     * @param integer recruiter user
     * @param integer owner user
     * @return new job order ID, or -1 on failure.
     */
    public function add($title, $companyID, $contactID, $description, $notes,
        $duration, $maxRate, $type, $isHot, $public, $openings, $companyJobID,
        $salary, $city, $state, $startDate, $enteredBy, $recruiter, $owner,
        $department, $questionnaire = false,$candidate_mapping=false,$ownertype=0)
    {
        if(!empty($candidate_mapping))
        {
            $candidate_mapping =  json_encode($candidate_mapping);
        }
        $record=  get_defined_vars();
        /* Get the department ID of the selected department. */
        // FIXME: Move this up to the UserInterface level. I don't like this
        //        tight coupling, and calling Contacts methods as static is
        //        bad.
        $objContacts=new Contacts($this->_siteID);
        $departmentID = $objContacts->getDepartmentIDByName(
            $department, $companyID, $this->_db
        );
        $hook=_AuieoHook("joborders_add_before");
        if($hook)
        {
            $hook($record);
        }
        // FIXME: Is the OrNULL usage below correct? Can these fields be NULL?
        $sql = sprintf(
            "INSERT INTO joborder (
                title,
                client_job_id,
                company_id,
                contact_id,
                description,
                notes,
                duration,
                rate_max,
                type,
                is_hot,
                public,
                openings,
                openings_available,
                salary,
                city,
                state,
                company_department_id,
                start_date,
                entered_by,
                recruiter,
                owner,
                ownertype,
                site_id,
                date_created,
                date_modified,
                questionnaire_id,
                candidate_mapping
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
                NOW(),
                NOW(),
                %s,
                %s
            )",
            $this->_db->makeQueryString($title),
            $this->_db->makeQueryString($companyJobID),
            $this->_db->makeQueryInteger($companyID),
            $this->_db->makeQueryInteger($contactID),
            $this->_db->makeQueryString($description),
            $this->_db->makeQueryString($notes),
            $this->_db->makeQueryString($duration),
            $this->_db->makeQueryString($maxRate),
            $this->_db->makeQueryString($type),
            ($isHot ? '1' : '0'),
            ($public ? '1' : '0'),
            $this->_db->makeQueryInteger($openings),
            $this->_db->makeQueryInteger($openings),
            $this->_db->makeQueryString($salary),
            $this->_db->makeQueryString($city),
            $this->_db->makeQueryString($state),
            $this->_db->makeQueryInteger($departmentID),
            $this->_db->makeQueryStringOrNULL($startDate),
            $this->_db->makeQueryInteger($enteredBy),
            $this->_db->makeQueryInteger($recruiter),
            $this->_db->makeQueryInteger($owner),
            $this->_db->makeQueryInteger($ownertype),
            $this->_siteID,
            /// Questionnaire ID or NULL if none
            $questionnaire !== false ? $this->_db->makeQueryInteger($questionnaire) : 'NULL',
            /// $candidate_mapping or NULL if none
            $candidate_mapping !== false ? $this->_db->makeQueryString($candidate_mapping) : 'NULL'
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        $jobOrderID = $this->_db->getLastInsertID();

        $hook=_AuieoHook("joborders_add_after");
        if($hook)
        {
            $record["id"]=$jobOrderID;
            $hook($record);
        }
        /* Store history. */
        $history = new History($this->_siteID);
        $history->storeHistoryNew(DATA_ITEM_JOBORDER, $jobOrderID);

        return $jobOrderID;
    }

    /**
     * Updates a job order.
     *
     * @param integer job order ID
     * @param string title
     * @param integer company ID
     * @param integer contact ID
     * @param string job description
     * @param string job order notes
     * @param string duration
     * @param string maximum rate
     * @param string job order type
     * @param boolean is job order hot
     * @param string (numeric) number of openings total
     * @param string (numeric) number of openings available
     * @param string salary
     * @param string city
     * @param string state
     * @param string start date
     * @param string status
     * @param integer recruiter user
     * @param integer owner user
     * @return boolean True if successful; false otherwise.
     */
    public function update($jobOrderID, $title, $companyJobID, $companyID,
        $contactID, $description, $notes, $duration, $maxRate, $type, $isHot,
        $openings, $openingsAvailable, $salary, $city, $state, $startDate, $status, $recruiter,
        $owner, $public, $email, $emailAddress, $department, $questionnaire = false,$candidate_mapping=false,
            $ownertype=0)
    {
        if($candidate_mapping!==false)
        {
            $candidate_mapping=  json_encode($candidate_mapping);
        }
        $record=  get_defined_vars();
 
        /* Get the department ID of the selected department. */
        // FIXME: Move this up to the UserInterface level. I don't like this
        //        tight coupling, and calling Contacts methods as static is
        //        bad.
        $objContacts=new Contacts($this->_siteID);
        $departmentID = $objContacts->getDepartmentIDByName(
            $department, $companyID, $this->_db
        );
        
        $hook=_AuieoHook("joborders_update_before");
        if($hook)
        {
            $hook($record);
        }

        // FIXME: Is the OrNULL usage below correct? Can these fields be NULL?
        $sql = sprintf(
            "UPDATE
                joborder
             SET
                title              = %s,
                client_job_id      = %s,
                company_id         = %s,
                contact_id         = %s,
                start_date         = %s,
                description        = %s,
                notes              = %s,
                duration           = %s,
                rate_max           = %s,
                type               = %s,
                is_hot             = %s,
                openings           = %s,
                openings_available = %s,
                status             = %s,
                salary             = %s,
                city               = %s,
                state              = %s,
                company_department_id = %s,
                recruiter          = %s,
                owner              = %s,
                ownertype          = %s,
                public             = %s,
                date_modified      = NOW(),
                questionnaire_id   = %s,
                candidate_mapping  = %s
            WHERE
                joborder_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryString($title),
            $this->_db->makeQueryString($companyJobID),
            $this->_db->makeQueryInteger($companyID),
            $this->_db->makeQueryInteger($contactID),
            $this->_db->makeQueryStringOrNULL($startDate),
            $this->_db->makeQueryString($description),
            $this->_db->makeQueryString($notes),
            $this->_db->makeQueryString($duration),
            $this->_db->makeQueryString($maxRate),
            $this->_db->makeQueryString($type),
            ($isHot ? '1' : '0'),
            $this->_db->makeQueryInteger($openings),
            $this->_db->makeQueryInteger($openingsAvailable),
            $this->_db->makeQueryString($status),
            $this->_db->makeQueryString($salary),
            $this->_db->makeQueryString($city),
            $this->_db->makeQueryString($state),
            $this->_db->makeQueryInteger($departmentID),
            $this->_db->makeQueryInteger($recruiter),
            $this->_db->makeQueryInteger($owner),
            $this->_db->makeQueryInteger($ownertype),
            ($public ? '1' : '0'),
            // Questionnaire ID or NULL if none
            $questionnaire !== false ? $this->_db->makeQueryInteger($questionnaire) : 'NULL',
            /// $candidate_mapping or NULL if none
            $candidate_mapping !== false ? $this->_db->makeQueryString($candidate_mapping) : 'NULL',
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID
        );

        $preHistory = $this->get($jobOrderID);
        $queryResult = $this->_db->query($sql);
        $postHistory = $this->get($jobOrderID);

        /* Store history. */
        $history = new History($this->_siteID);
        $history->storeHistoryChanges(
            DATA_ITEM_JOBORDER, $jobOrderID, $preHistory, $postHistory
        );

        if (!$queryResult)
        {
            return false;
        }
        $hook=_AuieoHook("joborders_update_after");
        if($hook)
        {
            $record["id"]=$jobOrderID;
            $hook($record);
        }
        if (!empty($emailAddress))
        {
            /* Send e-mail notification. */
            //FIXME: Make subject configurable.
            $mailer = new Mailer($this->_siteID);
            $mailerStatus = $mailer->sendToOne(
                array($emailAddress, ''),
                'CATS Notification: Job Order Ownership Change',
                $email,
                true
            );
        }

        return true;
    }

    /**
     * Removes a job order and all associated records from the system.
     *
     * @param integer job order ID
     * @return void
     */
    public function delete($jobOrderID)
    {
        /* Delete the job order. */
        $sql = sprintf(
            "DELETE FROM
                joborder
            WHERE
                joborder_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID
        );
        $this->_db->query($sql);

        /* Store history. */
        $history = new History($this->_siteID);
        $history->storeHistoryDeleted(DATA_ITEM_JOBORDER, $jobOrderID);

        /* Delete pipeline entries from candidate_joborder. */
        $sql = sprintf(
            "DELETE FROM
                candidate_joborder
            WHERE
                joborder_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID
        );
        $this->_db->query($sql);

        /* Delete pipeline history from candidate_joborder_status_history. */
        $sql = sprintf(
            "DELETE FROM
                candidate_joborder_status_history
            WHERE
                joborder_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID
        );
        $this->_db->query($sql);

        /* Delete attachments. */
        $attachments = new Attachments($this->_siteID);
        $attachmentsRS = $attachments->getAll(
            DATA_ITEM_JOBORDER, $jobOrderID
        );

        foreach ($attachmentsRS as $rowNumber => $row)
        {
            $attachments->delete($row['attachmentID']);
        }

        /* Delete from saved lists. */
        $sql = sprintf(
            "DELETE FROM
                saved_list_entry
            WHERE
                data_item_id = %s
            AND
                site_id = %s
            AND
                data_item_type = %s",
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID,
            DATA_ITEM_JOBORDER
        );
        $this->_db->query($sql);

        /* Delete extra fields. */
        $this->extraFields->deleteValueByDataItemID($jobOrderID);
    }

    /**
     * Returns number of total activities (for joborders datagrid).
     *
     * @return integer count
     */
    public function getCount()
    {
        $sql = sprintf(
            "SELECT
                COUNT(*) AS totalJobOrders
            FROM
                joborder
            WHERE
                joborder.site_id = %s",
            $this->_siteID
        );

        return $this->_db->getColumn($sql, 0, 0);
    }
    
    public function load($jobOrderID)
    {
        $sql = sprintf(
            "SELECT
                joborder.joborder_id,
                joborder.company_id,
                joborder.contact_id,
                joborder.client_job_id,
                joborder.title,
                joborder.description,
                joborder.type,
                joborder.is_hot,
                joborder.openings,
                joborder.openings_available,
                joborder.notes,
                joborder.duration,
                joborder.rate_max,
                joborder.salary,
                joborder.status,
                joborder.city,
                joborder.state,
                joborder.recruiter,
                joborder.owner,
                joborder.public,
                joborder.questionnaire_id,
                joborder.is_admin_hidden,
                joborder.candidate_mapping,
                company_department.name,
                CONCAT(
                    contact.first_name, ' ', contact.last_name
                ) AS contactFullName,
                contact.phone_work AS contactWorkPhone,
                contact.email1 AS contactEmail,
                CONCAT(
                    recruiter_user.first_name, ' ', recruiter_user.last_name
                ) AS recruiterFullName,
                CONCAT(
                    entered_by_user.first_name, ' ', entered_by_user.last_name
                ) AS enteredByFullName,
                CONCAT(
                    owner_user.first_name, ' ', owner_user.last_name
                ) AS ownerFullName,
                owner_user.email AS owner_email,
                recruiter_user.email AS recruiter_email,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS startDate,
                DATEDIFF(
                    NOW(), joborder.date_created
                ) AS daysOld,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateCreated,
                DATE_FORMAT(
                    joborder.date_modified, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateModified,
                COUNT(
                    candidate_joborder.joborder_id
                ) AS pipeline,
                (
                    SELECT
                        COUNT(*)
                    FROM
                        candidate_joborder_status_history
                    WHERE
                        joborder_id = %s
                    AND
                        status_to = %s
                    AND
                        site_id = %s
                ) AS submitted,
                company.name AS companyName
            FROM
                joborder
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            LEFT JOIN contact
                ON joborder.contact_id = contact.contact_id
            LEFT JOIN user AS recruiter_user
                ON joborder.recruiter = recruiter_user.user_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            LEFT JOIN user AS entered_by_user
                ON joborder.entered_by = entered_by_user.user_id
            LEFT JOIN candidate_joborder
                ON joborder.joborder_id = candidate_joborder.joborder_id
            LEFT JOIN company_department
                ON joborder.company_department_id = company_department.company_department_id
            WHERE
                joborder.joborder_id = %s
            AND
                joborder.site_id = %s
            GROUP BY
                joborder.joborder_id",
            $this->_db->makeQueryInteger($jobOrderID),
            PIPELINE_STATUS_SUBMITTED,
            $this->_siteID,
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID
        );
        $this->record = $this->_db->getAssoc($sql);
        if(isset($this->record["candidate_mapping"]) && !empty($this->record["candidate_mapping"]))
        {
            $this->record["candidate_mapping"]=  json_decode($this->record["candidate_mapping"]);
        }
        $sql="select * from extra_field where data_item_type=100 and data_item_id='{$jobOrderID}'";
        $arrAssoc = $this->_db->getAllAssoc($sql);
        $this->extraRecord=array();
        foreach($arrAssoc as $ind=>$row)
        {
            $this->extraRecord[$row["field_name"]]=$row["value"];
        }
    }

    /**
     * Returns all relevent job order information for a given job order ID.
     *
     * @param integer job order ID
     * @return array job order data
     */
    public function get($jobOrderID)
    {
        $sql = sprintf(
            "SELECT
                joborder.joborder_id,
                joborder.company_id,
                joborder.contact_id,
                joborder.client_job_id,
                joborder.title,
                joborder.description,
                joborder.type,
                joborder.is_hot,
                joborder.openings,
                joborder.openings_available,
                joborder.notes,
                joborder.duration,
                joborder.rate_max,
                joborder.salary,
                joborder.status,
                joborder.city,
                joborder.state,
                joborder.recruiter,
                joborder.owner,
                joborder.ownertype,
                joborder.public,
                joborder.questionnaire_id,
                joborder.is_admin_hidden,
                joborder.entered_by,
                joborder.start_date,
                joborder.date_created,
                joborder.date_modified,
                joborder.company_department_id,
                joborder.candidate_mapping,
                company_department.name AS department,
                CONCAT(
                    contact.first_name, ' ', contact.last_name
                ) AS contactFullName,
                contact.phone_work AS contactWorkPhone,
                contact.email1 AS contactEmail,
                CONCAT(
                    recruiter_user.first_name, ' ', recruiter_user.last_name
                ) AS recruiterFullName,
                CONCAT(
                    entered_by_user.first_name, ' ', entered_by_user.last_name
                ) AS enteredByFullName,
                CONCAT(
                    owner_user.first_name, ' ', owner_user.last_name
                ) AS ownerFullName,
                owner_user.email AS owner_email,
                recruiter_user.email AS recruiter_email,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS startDate,
                DATEDIFF(
                    NOW(), joborder.date_created
                ) AS daysOld,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateCreated,
                DATE_FORMAT(
                    joborder.date_modified, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS dateModified,
                COUNT(
                    candidate_joborder.joborder_id
                ) AS pipeline,
                (
                    SELECT
                        COUNT(*)
                    FROM
                        candidate_joborder_status_history
                    WHERE
                        joborder_id = %s
                    AND
                        status_to = %s
                    AND
                        site_id = %s
                ) AS submitted,
                company.name AS companyName
            FROM
                joborder
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            LEFT JOIN contact
                ON joborder.contact_id = contact.contact_id
            LEFT JOIN user AS recruiter_user
                ON joborder.recruiter = recruiter_user.user_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            LEFT JOIN user AS entered_by_user
                ON joborder.entered_by = entered_by_user.user_id
            LEFT JOIN candidate_joborder
                ON joborder.joborder_id = candidate_joborder.joborder_id
            LEFT JOIN company_department
                ON joborder.company_department_id = company_department.company_department_id
            WHERE
                joborder.joborder_id = %s
            AND
                joborder.site_id = %s
            GROUP BY
                joborder.joborder_id",
            $this->_db->makeQueryInteger($jobOrderID),
            PIPELINE_STATUS_SUBMITTED,
            $this->_siteID,
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID
        );

        if (!eval(Hooks::get('JO_GET_1_SQL'))) return;

        return $this->_db->getAssoc($sql);
    }

    /**
     * Returns all job order information relevant to the Edit Job Order page
     * for a given job order ID.
     *
     * @param integer job order ID
     * @return array job order data
     */
    public function getForEditing($jobOrderID)
    {
        $sql = sprintf(
            "SELECT
                joborder.joborder_id,
                joborder.company_id,
                company.name,
                company_department.name,
                joborder.contact_id,
                joborder.client_job_id,
                joborder.title,
                joborder.description,
                joborder.type,
                joborder.is_hot,
                joborder.openings,
                joborder.openings_available,
                joborder.notes,
                joborder.duration,
                joborder.rate_max,
                joborder.salary,
                joborder.status,
                joborder.city,
                joborder.state,
                joborder.recruiter,
                joborder.owner,
                joborder.ownertype,
                joborder.public,
                joborder.questionnaire_id,
                joborder.company_department_id,
                joborder.candidate_mapping,
                joborder.start_date,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS startDate,
                joborder.candidate_mapping,
                company.name AS companyName
            FROM
                joborder
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            LEFT JOIN company_department
                ON joborder.company_department_id = company_department.company_department_id
            WHERE
                joborder.joborder_id = %s
            AND
                joborder.site_id = %s",
            $jobOrderID,
            $this->_siteID
        );

        if (!eval(Hooks::get('JO_GET_EDIT_SQL'))) return;

        $rs = $this->_db->getAssoc($sql);

        return $rs;
    }
    
    /**
     * Returns the entire job orders list.
     *
     * @param flag job order status flag
     * @param integer assigned-to owner/recruiter user ID (optional)
     * @param integer assigned-to company ID (optional)
     * @param integer assigned-to contact ID (optional)
     * @param boolean only hot job orders
     * @return array job orders data
     */
    public function getAllByProject($projectID,$status='Active')
    {        
        switch ($status)
        {
            case JOBORDERS_STATUS_ACTIVE:
                $statusCriterion = "AND joborder.status = 'Active'";
                break;

            case JOBORDERS_STATUS_ONHOLDFULL:
                $statusCriterion = "AND joborder.status IN ('OnHold', 'Full')";
                break;

            case JOBORDERS_STATUS_ACTIVEONHOLDFULL:
                $statusCriterion = "AND joborder.status IN ('Active', 'OnHold', 'Full')";
                break;

            case JOBORDERS_STATUS_CLOSED:
                $statusCriterion = "AND joborder.status = 'Closed'";
                break;

            case JOBORDERS_STATUS_ALL:
            default:
                $statusCriterion = '';
                break;
        }
        $objProjectsJoborderFrom=new ClsAuieoSQLFrom();
        $objSQL=new ClsAuieoSQL();
        $objJoborderFrom=$objSQL->addFrom("joborder");
        $joinJoborder=$objJoborderFrom->addJoinField("joborder_id");
        $objProjectsJoborderFrom=$objSQL->addFrom("auieo_projects_joborder");
        $joinProjectJoborder=$objProjectsJoborderFrom->addJoinField("joborderid");
        $objProjectsJoborderFrom->setJoinWith($objJoborderFrom, $joinJoborder, $joinProjectJoborder);
        $objSQL->addSelect($objJoborderFrom, "joborder_id", "id");
        $objSQL->addSelect($objJoborderFrom, "title", "joborder");
        $objSQL->addSelect($objProjectsJoborderFrom, "startdate");
        $objSQL->addSelect($objProjectsJoborderFrom, "targetenddate");
        $objSQL->addWhere($objProjectsJoborderFrom, "projectsid", $projectID);
        $sql=$objSQL->render();

        if (!eval(Hooks::get('JO_GET_ALL_SQL'))) return;

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns the entire job orders list.
     *
     * @param flag job order status flag
     * @param integer assigned-to owner/recruiter user ID (optional)
     * @param integer assigned-to company ID (optional)
     * @param integer assigned-to contact ID (optional)
     * @param boolean only hot job orders
     * @return array job orders data
     */
    public function getAll($status, $userID = -1, $companyID = -1,
        $contactID = -1, $onlyHot = false, $onlyPublic = false, $allowAdministrativeHidden = false)
    {
        if ($userID >= 0)
        {
            $userCriterion = sprintf(
                "AND (joborder.recruiter = %s OR joborder.owner = %s)",
                $this->_db->makeQueryInteger($userID),
                $this->_db->makeQueryInteger($userID)
            );
        }
        else
        {
            $userCriterion = '';
        }

        if ($companyID >= 0)
        {
            $companyCriterion = sprintf(
                "AND company.company_id = %s",
                $this->_db->makeQueryInteger($companyID)
            );
        }
        else
        {
            $companyCriterion = '';
        }

        if ($contactID >= 0)
        {
            $contactCriterion = sprintf(
                "AND contact.contact_id = %s",
                $this->_db->makeQueryInteger($contactID)
            );
        }
        else
        {
            $contactCriterion = '';
        }

        if ($onlyHot)
        {
            $hotCriterion = "AND joborder.is_hot = 1";
        }
        else
        {
            $hotCriterion = '';
        }

        if ($onlyPublic)
        {
            $publicCriterion = "AND joborder.public = 1";
        }
        else
        {
            $publicCriterion = '';
        }

        if (!$allowAdministrativeHidden)
        {
            $adminHiddenCriterion = 'AND joborder.is_admin_hidden = 0';
        }
        else
        {
            $adminHiddenCriterion = '';
        }

        switch ($status)
        {
            case JOBORDERS_STATUS_ACTIVE:
                $statusCriterion = "AND joborder.status = 'Active'";
                break;

            case JOBORDERS_STATUS_ONHOLDFULL:
                $statusCriterion = "AND joborder.status IN ('OnHold', 'Full')";
                break;

            case JOBORDERS_STATUS_ACTIVEONHOLDFULL:
                $statusCriterion = "AND joborder.status IN ('Active', 'OnHold', 'Full')";
                break;

            case JOBORDERS_STATUS_CLOSED:
                $statusCriterion = "AND joborder.status = 'Closed'";
                break;

            case JOBORDERS_STATUS_ALL:
            default:
                $statusCriterion = '';
                break;
        }

        $sql = sprintf(
            "SELECT
                joborder.joborder_id AS jobOrderID,
                IF(attachment_id, 1, 0) AS attachmentPresent,
                joborder.title AS title,
                joborder.description AS jobDescription,
                joborder.type AS type,
                joborder.is_hot AS isHot,
                joborder.openings AS openings,
                joborder.openings_available AS openingsAvailable,
                joborder.duration AS duration,
                joborder.city AS city,
                joborder.state AS state,
                joborder.status AS status,
                joborder.company_department_id AS departmentID,
                joborder.questionnaire_id as questionnaireID,
                company.company_id AS companyID,
                company.name AS companyName,
                company_department.name AS departmentName,
                contact.contact_id AS contactID,
                recruiter_user.first_name AS recruiterFirstName,
                recruiter_user.last_name AS recruiterLastName,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS startDate,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    joborder.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                DATEDIFF(
                    NOW(), joborder.date_created
                ) AS daysOld,
                COUNT(
                    candidate_joborder.joborder_id
                ) AS pipeline,
                (
                    SELECT
                        COUNT(*)
                    FROM
                        candidate_joborder_status_history
                    WHERE
                        joborder_id = joborder.joborder_id
                    AND
                        status_to = %s
                    AND
                        site_id = %s
                ) AS submitted,
                joborder.is_admin_hidden AS isAdminHidden,
                joborder.date_created AS dateCreatedSort
            FROM
                joborder
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            LEFT JOIN contact
                ON joborder.contact_id = contact.contact_id
            LEFT JOIN company_department
                ON joborder.company_department_id = company_department.company_department_id
            LEFT JOIN candidate_joborder
                ON joborder.joborder_id = candidate_joborder.joborder_id
            LEFT JOIN user AS recruiter_user
                ON joborder.recruiter = recruiter_user.user_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            LEFT JOIN attachment
                ON
                (
                    joborder.joborder_id = attachment.data_item_id
                    AND attachment.data_item_type = 400
                )
            WHERE
                joborder.site_id = %s
            %s
            %s
            %s
            %s
            %s
            %s
            %s
            GROUP BY
                joborder.joborder_id
            ORDER BY
                daysOld ASC,
                dateCreatedSort DESC",
            PIPELINE_STATUS_SUBMITTED,
            $this->_siteID,
            $this->_siteID,
            $statusCriterion,
            $userCriterion,
            $companyCriterion,
            $contactCriterion,
            $hotCriterion,
            $publicCriterion,
            $adminHiddenCriterion
        );

        if (!eval(Hooks::get('JO_GET_ALL_SQL'))) return;

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Updates a job order's modified timestamp.
     *
     * @param integer job order ID
     * @return boolean True if successful; false otherwise.
     */
    public function updateModified($jobOrderID)
    {
        $sql = sprintf(
            "UPDATE
                joborder
            SET
                date_modified = NOW()
            WHERE
                joborder_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID
        );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Updates a job order's openings available count.
     *
     * @param integer job order ID
     * @param integer openings
     * @return boolean True if successful; false otherwise.
     */
    public function updateOpeningsAvailable($jobOrderID, $count)
    {
        $sql = sprintf(
            "UPDATE
                joborder
            SET
                openings_available = %s
            WHERE
                joborder_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($count),
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID
        );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Returns a human readable string representation
     * of the given typecode.
     *
     * @param string typecode
     * @return string human readable typecode
     */
    public static function typeCodeToString($typeCode)
    {
        switch ($typeCode)
        {
            case 'C':
                return 'Contract';
                break;

            case 'H';
                return 'Hire';
                break;

            case 'C2H';
                return 'Contract to Hire';
                break;

            case 'FL';
                return 'Freelance';
                break;

            default:
                return '(Unknown)';
                break;
        }
    }

    /**
     * Sets the administrative hide feature.  ASP site administrators
     * (root users) can use this to hide a job order from the site
     * owners.
     *
     * @param integer job order ID
     * @param integer new hidden state (1 = true)
     * @return boolean query response
     */
    public function administrativeHideShow($jobOrderID, $state)
    {
        $sql = sprintf(
            "UPDATE
                joborder
            SET
                is_admin_hidden = %s
            WHERE
                joborder_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($state),
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_siteID
        );

        return (boolean) $this->_db->query($sql);
    }

    public function renderTemplateVars($template)
    {
        include_once("lib/JoborderTemplate.php");
        $joborderid=$this->record["joborder_id"];
        $joborders=new JoborderTemplate($this->_siteID);
        $joborders->load($joborderid);
        $template=  html_entity_decode($template);
        try
        {
        ob_start();
        $render="";
        eval('echo <<< EOT
'.$template.'
EOT;
');
        $render = ob_get_clean();
    }
        catch(Exception $e)
        {
            trace($e);
        }
        return $render;
    }
}


class JobOrdersDataGrid extends DataGrid
{
    protected $_siteID;


    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($instanceName, $siteID, $parameters, $misc)
    {
        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;
        $this->_assignedCriterion = "";
        $this->_dataItemIDColumn = 'joborder.joborder_id';

        $this->_classColumns = array(
            'Attachments' => array(  'select'   => 'IF(attachment_id, 1, 0) AS attachmentPresent',
                                     'pagerRender' => '
                                                    if ($rsData[\'attachmentPresent\'] == 1)
                                                    {
                                                        $return = \'<img src="images/paperclip.gif" alt="" width="16" height="16" title="Attachment Present" />\';
                                                    }
                                                    else
                                                    {
                                                        $return = \'<img src="images/mru/blank.gif" alt="" width="16" height="16" />\';
                                                    }

                                                    return $return;
                                                   ',

                                     'pagerWidth'    => 10,
                                     'pagerOptional' => true,
                                     'pagerNoTitle' => true,
                                     'sizable'  => false,
                                     'exportable' => false,
                                     'filterable' => false),

            'ID' =>        array(     'pagerRender'    => 'return $rsData[\'jobOrderID\'];',
                                      'sortableColumn' => 'jobOrderID',
                                      'pagerWidth'     => 33,
                                      'pagerOptional'  => true,
                                      'alphaNavigation'=> false,
                                      'exportColumnHeaderText' => 'id',
                                      'filter'         => 'joborder.joborder_id',
                                      'filterTypes'   => '===>=<'),

            'Company Job ID'  => array ('select' => 'joborder.client_job_id AS cpyJobID',
                                        'sortableColumn' => 'cpyJobID',
                                        'pagerWidth'     => 65,
                                        'pagerOptional'  => true,
                                        'alphaNavigation'=> false,
                                        'exportColumnHeaderText' => 'Company Job id',
                                        'columnHeaderText' => 'Cpy Job ID',
                                        'filter'         => 'joborder.client_job_id',
                                        'filterTypes'   => '===>=<'),

            'Title' =>       array('select'         => 'joborder.title AS title',
                                      'pagerRender'    => 'if ($rsData[\'isHot\'] == 1) $className =  \'jobLinkHot\'; else $className = \'jobLinkCold\'; return \'<a href="'.CATSUtility::getIndexName().'?m=joborders&amp;a=show&amp;jobOrderID=\'.$rsData[\'jobOrderID\'].\'" class="\'.$className.\'">\'.htmlspecialchars($rsData[\'title\']).\'</a>\';',
                                      'sortableColumn' => 'title',
                                      'pagerWidth'     => 165,
                                      'pagerOptional'  => false,
                                      'alphaNavigation'=> true,
                                      'filter'         => 'joborder.title'),

            'Company' =>       array('select'         => 'company.name AS companyName,
                                                          company.company_id AS companyID',
                                      'pagerRender'    => 'return \'<a href="'.CATSUtility::getIndexName().'?m=companies&amp;a=show&amp;companyID=\'.$rsData[\'companyID\'].\'">\'.htmlspecialchars($rsData[\'companyName\']).\'</a>\';',
                                      'sortableColumn' => 'companyName',
                                      'pagerWidth'     => 125,
                                      'pagerOptional'  => true,
                                      'alphaNavigation'=> true,
                                      'filter'         => 'company.name'),

            'Department' =>           array('select'   => 'company_department.name AS department',
                                      'join'           => 'LEFT JOIN company_department ON company_department.company_department_id = joborder.company_department_id',
                                      'pagerRender'    => 'return $rsData[\'department\'];',
                                      'sortableColumn' => 'department',
                                      'pagerWidth'     => 95,
                                      'pagerOptional'  => true,
                                      'alphaNavigation'=> true,
                                      'filter'         => 'company_department.name'),

            'Type' =>           array('select'         => 'joborder.type AS type',
                                      'pagerRender'    => 'return $rsData[\'type\'];',
                                      'sortableColumn' => 'type',
                                      'pagerWidth'     => 45,
                                      'pagerOptional'  => true,
                                      'alphaNavigation'=> false,
                                      'exportRender'   => 'return $rsData[\'type\'];',
                                      'filter'         => 'joborder.type'),

            'Status' =>         array('select'         => 'joborder.status AS status',
                                      'pagerRender'    => 'return $rsData[\'status\'];',
                                      'exportRender'   => 'return $rsData[\'status\'];',
                                      'sortableColumn' => 'status',
                                      'pagerWidth'     => 45,
                                      'pagerOptional'  => true,
                                      'alphaNavigation'=> false,
                                      'filter'         => 'joborder.status'),

            'Age' =>            array('select'         => 'DATEDIFF(NOW(), joborder.date_created) AS daysOld',
                                      'pagerRender'    => 'return $rsData[\'daysOld\'];',
                                      'sortableColumn' => 'daysOld',
                                      'pagerWidth'     => 45,
                                      'pagerOptional'  => true,
                                      'alphaNavigation'=> false,
                                      'filterHaving'  => 'daysOld',
                                      'filterTypes'   => '===>=<'),

            'Created' =>       array('select'   => 'DATE_FORMAT(joborder.date_created, \'%m-%d-%y\') AS dateCreated',
                                     'pagerRender'      => 'return $rsData[\'dateCreated\'];',
                                     'sortableColumn'     => 'dateCreatedSort',
                                     'pagerWidth'    => 60,
                                     'filterHaving' => 'DATE_FORMAT(joborder.date_created, \'%m-%d-%y\')'),

            'Modified' =>      array('select'   => 'DATE_FORMAT(joborder.date_modified, \'%m-%d-%y\') AS dateModified',
                                     'pagerRender'      => 'return $rsData[\'dateModified\'];',
                                     'sortableColumn'     => 'dateModifiedSort',
                                     'pagerWidth'    => 60,
                                     'pagerOptional' => true,
                                     'filterHaving' => 'DATE_FORMAT(joborder.date_modified, \'%m-%d-%y\')'),

            'Not Contacted' => array('select'   => '(
                                                              SELECT
                                                                  COUNT(*)
                                                              FROM
                                                                  candidate_joborder
                                                              WHERE
                                                                  joborder_id = joborder.joborder_id
                                                              AND
                                                                  (status = '.PIPELINE_STATUS_NOCONTACT.' OR status = '.PIPELINE_STATUS_NOSTATUS.')
                                                              AND
                                                                  site_id = '.$this->_siteID.'
                                                          ) AS notContacted',
                                       'pagerRender'      => 'return $rsData[\'notContacted\'];',
                                       'sortableColumn'     => 'notContacted',
                                       'columnHeaderText' => 'NC',
                                       'pagerWidth'    => 25,
                                       'filterHaving'  => 'notContacted',
                                       'filterTypes'   => '===>=<'),
 
            'Submitted' =>       array('select'   => '(
                                                            SELECT
                                                                COUNT(*)
                                                            FROM
                                                                candidate_joborder_status_history
                                                            WHERE
                                                                joborder_id = joborder.joborder_id
                                                            AND
                                                                status_to = '.PIPELINE_STATUS_SUBMITTED.'
                                                            AND
                                                                site_id = '.$this->_siteID.'
                                                        ) AS submitted',
                                     'pagerRender'      => 'return $rsData[\'submitted\'];',
                                     'sortableColumn'     => 'submitted',
                                     'columnHeaderText' => 'S',
                                     'pagerWidth'    => 25,
                                     'filterHaving'  => 'submitted',
                                     'filterTypes'   => '===>=<'),

            'Pipeline' =>       array('select'   => '(
                                                            SELECT
                                                                COUNT(*)
                                                            FROM
                                                                candidate_joborder
                                                            WHERE
                                                                joborder_id = joborder.joborder_id
                                                            AND
                                                                site_id = '.$this->_siteID.'
                                                        ) AS pipeline',
                                     'pagerRender'      => 'return $rsData[\'pipeline\'];',
                                     'sortableColumn'     => 'pipeline',
                                     'columnHeaderText' => 'P',
                                     'pagerWidth'    => 25,
                                     'filterHaving'  => 'pipeline',
                                     'filterTypes'   => '===>=<'),

             'Interviews' =>       array('select'   => '(
                                                             SELECT
                                                                 COUNT(*)
                                                             FROM
                                                                 candidate_joborder_status_history
                                                             WHERE
                                                                 joborder_id = joborder.joborder_id
                                                             AND
                                                                 status_to = '.PIPELINE_STATUS_INTERVIEWING.'
                                                             AND
                                                                 site_id = '.$this->_siteID.'
                                                         ) AS interviewingCount',
                                      'pagerRender'      => 'return $rsData[\'interviewingCount\'];',
                                      'sortableColumn'     => 'interviewingCount',
                                      'columnHeaderText' => 'I',
                                      'pagerWidth'    => 25,
                                      'filterHaving'  => 'interviewingCount',
                                      'filterTypes'   => '===>=<'),

           /* 'Owner' =>         array('select'   => 'owner_user.first_name AS ownerFirstName,' .
                                                   'owner_user.last_name AS ownerLastName,' .
                                                   'CONCAT(owner_user.last_name, owner_user.first_name) AS ownerSort',
                                     'join'     => 'LEFT JOIN user AS owner_user ON joborder.owner = owner_user.user_id',
                                     'pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'ownerFirstName\'], $rsData[\'ownerLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'ownerFirstName\'] . " " .$rsData[\'ownerLastName\'];',
                                     'sortableColumn'     => 'ownerSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),*/

            'Recruiter' =>     array('select'   => 'recruiter_user.first_name AS recruiterFirstName,' .
                                                   'recruiter_user.last_name AS recruiterLastName,' .
                                                   'CONCAT(recruiter_user.last_name, recruiter_user.first_name) AS recruiterSort',
                                     'join'     => 'LEFT JOIN user AS recruiter_user ON joborder.recruiter = recruiter_user.user_id',
                                     'pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'recruiterFirstName\'], $rsData[\'recruiterLastName\'], false, LAST_NAME_MAXLEN);',
                                     'exportRender'     => 'return $rsData[\'recruiterFirstName\'] . " " .$rsData[\'recruiterLastName\'];',
                                     'sortableColumn'     => 'recruiterSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(recruiter_user.first_name, recruiter_user.last_name)'),

            'Contact' =>       array('select'   => 'contact.first_name AS contactFirstName,' .
                                                   'contact.last_name AS contactLastName,' .
                                                   'CONCAT(contact.last_name, contact.first_name) AS contactSort,' .
                                                   'contact.contact_id AS contactID',
                                     'pagerRender'      => 'return \'<a href="'.CATSUtility::getIndexName().'?m=contacts&amp;a=show&amp;contactID=\'.$rsData[\'contactID\'].\'">\'.StringUtility::makeInitialName($rsData[\'contactFirstName\'], $rsData[\'contactLastName\'], false, LAST_NAME_MAXLEN).\'</a>\';',
                                     'exportRender'     => 'return $rsData[\'contactFirstName\'] . " " .$rsData[\'contactLastName\'];',
                                     'sortableColumn'     => 'contactSort',
                                     'pagerWidth'    => 75,
                                     'alphaNavigation' => true,
                                     'filter'         => 'CONCAT(contact.first_name, contact.last_name)'),

            'Contact Phone' => array('select'   => 'contact.phone_work AS contactPhone',
                                     'pagerRender'      => 'return $rsData[\'contactPhone\'];',
                                     'exportRender'     => 'return $rsData[\'contactPhone\'];',
                                     'sortableColumn'     => 'contactPhone',
                                     'pagerWidth'    => 85,
                                     'alphaNavigation' => false,
                                     'filter'         => 'contact.phone_work'),

            'City'          => array('select'   => 'joborder.city AS locationCity',
                                     'pagerRender'      => 'return $rsData[\'locationCity\'];',
                                     'exportRender'     => 'return $rsData[\'locationCity\'];',
                                     'sortableColumn'     => 'locationCity',
                                     'pagerWidth'    => 65,
                                     'alphaNavigation' => true,
                                     'filter'         => 'joborder.city'),

            'State'          => array('select'   => 'joborder.state AS locationState',
                                     'pagerRender'      => 'return $rsData[\'locationState\'];',
                                     'exportRender'     => 'return $rsData[\'locationState\'];',
                                     'sortableColumn'     => 'locationState',
                                     'pagerWidth'    => 65,
                                     'alphaNavigation' => true,
                                     'filter'         => 'joborder.state'),

            'Max Rate'          => array('select'   => 'joborder.rate_max AS maxRate',
                                     'pagerRender'      => 'return $rsData[\'maxRate\'];',
                                     'exportRender'     => 'return $rsData[\'maxRate\'];',
                                     'sortableColumn'     => 'maxRate',
                                     'pagerWidth'    => 85,
                                     'alphaNavigation' => false,
                                     'filter'         => 'joborder.rate_max',
                                     'filterTypes'   => '===>=<'),

            'Salary'          => array('select'   => 'joborder.salary AS salary',
                                     'pagerRender'      => 'return $rsData[\'salary\'];',
                                     'exportRender'     => 'return $rsData[\'salary\'];',
                                     'sortableColumn'     => 'salary',
                                     'pagerWidth'    => 85,
                                     'alphaNavigation' => false,
                                     'filter'         => 'joborder.salary',
                                     'filterTypes'   => '===>=<'),

            'Duration'          => array('select'   => 'joborder.duration AS duration',
                                     'pagerRender'      => 'return $rsData[\'duration\'];',
                                     'exportRender'     => 'return $rsData[\'duration\'];',
                                     'sortableColumn'     => 'duration',
                                     'pagerWidth'    => 85,
                                     'alphaNavigation' => false,
                                     'filter'         => 'joborder.duration',
                                     'filterTypes'   => '===>=<'),

            'Openings'      => array('select'   => 'joborder.openings_available AS openingsAvailable',
                                     'pagerRender'      => 'return $rsData[\'openingsAvailable\'];',
                                     'exportRender'     => 'return $rsData[\'openingsAvailable\'];',
                                     'sortableColumn'     => 'openingsAvailable',
                                     'pagerWidth'    => 85,
                                     'alphaNavigation' => false,
                                     'filter'         => 'joborder.openings_available',
                                     'filterTypes'   => '===>=<'),

            'Misc Notes' =>    array('select'  => 'joborder.notes AS notes',
                                     'sortableColumn'    => 'notes',
                                     'pagerWidth'   => 300,
                                     'filter'         => 'joborder.notes'),

            'OwnerID' =>       array('select'    => '',
                                     'filter'    => 'joborder.owner, joborder.recruiter',
                                     'filterInList' => true,
                                     'pagerOptional' => false,
                                     'filterable' => false,
                                     'filterDescription' => 'Only My Job Orders'),

            'IsHot' =>         array('select'    => '',
                                     'filter'    => 'joborder.is_hot',
                                     'pagerOptional' => false,
                                     'filterable' => false,
                                     'filterDescription' => 'Only Hot Job Orders')
        );

        if (!eval(Hooks::get('JOBORDERS_DATAGRID_COLUMNS'))) return;

        /* Extra fields get added as columns here. */
        $jobOrders = new JobOrders($this->_siteID);
        $extraFieldsRS = $jobOrders->extraFields->getSettings();
        foreach ($extraFieldsRS as $index => $data)
        {
            $fieldName = $data['fieldName'];

            if (!isset($this->_classColumns[$fieldName]))
            {
                $columnDefinition = $jobOrders->extraFields->getDataGridDefinition($index, $data, $this->_db);

                /* Return false for extra fields that should not be columns. */
                if ($columnDefinition !== false)
                {
                    $this->_classColumns[$fieldName] = $columnDefinition;
                }
            }
        }

        parent::__construct($instanceName, $parameters, $misc);
    }

    /**
     * Returns the sql statment for the pager.
     *
     * @return array clients data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {
        // FIXME: Factor out Session dependency.
        if ($_SESSION['CATS']->isLoggedIn() && $_SESSION['CATS']->getAccessLevel() < ACCESS_LEVEL_MULTI_SA)
        {
            $adminHiddenCriterion = 'AND joborder.is_admin_hidden = 0';
        }
        else
        {
            $adminHiddenCriterion = '';
        }

        if ($this->getMiscArgument() != 0)
        {
            $savedListID = (int) $this->getMiscArgument();
            $joinSQL  .= ' INNER JOIN saved_list_entry
                                    ON saved_list_entry.data_item_type = '.DATA_ITEM_JOBORDER.'
                                    AND saved_list_entry.data_item_id = joborder.joborder_id
                                    AND saved_list_entry.site_id = '.$this->_siteID.'
                                    AND saved_list_entry.saved_list_id = '.$savedListID;
        }
        else
        {
            $joinSQL  .= ' LEFT JOIN saved_list_entry
                                    ON saved_list_entry.data_item_type = '.DATA_ITEM_JOBORDER.'
                                    AND saved_list_entry.data_item_id = joborder.joborder_id
                                    AND saved_list_entry.site_id = '.$this->_siteID;
        }

        if (!eval(Hooks::get('JOBORDER_DATAGRID_GETSQL'))) return;

        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                joborder.joborder_id AS jobOrderID,
                joborder.joborder_id AS exportID,
                joborder.date_modified AS dateModifiedSort,
                joborder.date_created AS dateCreatedSort,
                joborder.is_hot AS isHot,
            %s
            FROM
                joborder
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            LEFT JOIN contact
                ON joborder.contact_id = contact.contact_id
            LEFT JOIN attachment
                ON joborder.joborder_id = attachment.data_item_id
                AND attachment.data_item_type = %s
            %s
            WHERE
                joborder.site_id = %s
            %s
            %s
            %s
            GROUP BY joborder.joborder_id
            %s
            %s
            %s",
            $distinct,
            $selectSQL,
            DATA_ITEM_JOBORDER,
            $joinSQL,
            $this->_siteID,
            $adminHiddenCriterion,
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            $this->_assignedCriterion,
            (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : '',
            $orderSQL,
            $limitSQL
        );

        return $sql;
    }
}

?>
