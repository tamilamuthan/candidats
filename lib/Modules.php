<?php
/* 
 * CandidATS
 * Baseclass for All Modules
 *
 * Copyright (C) 2014 - 2015 Auieo Software Private Limited, Parent Company of Unicomtech.
 * 
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

include_once("lib/ClsNaanalSQL.php");
class Modules
{
    protected $_db;
    protected $_siteID;
    
    public function __construct($siteID=null)
    {
        if(!is_null($siteID))
            $this->_siteID=$siteID;
        $this->_db = DatabaseConnection::getInstance();
    }
    /**
     * mapping of action with 0 to 4
     * by default, 0 indicates add
     * 1 indicates edit
     * 2 indicates show
     * 3 indicates delete
     * 4 indidates list
     * default indicates default action
     * @return type array of action mapped with action
     */
    public static function actionMapping($action=false)
    {
        $arrAction =  array(
            "listByView"=>4,
            "add"=>0,
            "edit"=>1,
            "show"=>2,
            "delete"=>3,
            "default"=>"listByView"
        );
        if($action===false) return $arrAction;
        if(isset($arrAction[$action])) return $arrAction[$action];
        return false;
    }
    public function &getFromObject($alias=false)
    {
        static $arrFromObject=array();
        static $objFromObject=null;
        if($alias===false)
        {
            if(is_null($objFromObject))
            {
                $objFromObject=new ClsAuieoSQLFrom();
                $objFromObject->setTable($this->module_table);
                $objFromObject->setDatabase(DATABASE_NAME);
            }
            return $objFromObject;
        }
        else
        {
            if(!isset($arrFromObject[$alias]))
            {
                $arrFromObject[$alias]=new ClsAuieoSQLFrom($alias);
                $objFromObject->setTable($this->module_table);
                $objFromObject->setDatabase(DATABASE_NAME);
            }
            return $arrFromObject[$alias];
        }
    }
    
    /**
     * Updates a modules's site ID.
     *
     * @param integer Module ID.
     * @param integer Site ID.
     * @return boolean Boolean was the query executed successfully?
     */
    
    public function updateSite($moduleID, $siteID)
    {
        $sql = sprintf(
            "UPDATE
                {$this->module_table}
            SET
                site_id = %s
            WHERE
                {$this->module_id} = %s",
            $siteID,
            $this->_db->makeQueryInteger($moduleID),
            $this->_siteID
        );
        $status=(boolean) $this->_db->query($sql);
        if($status)
        {
            $this->extraFields->transferSite($moduleID, $siteID);
            $this->transferAttachment($moduleID,$_GET["siteID"]);
            if($this->data_item_type===DATA_ITEM_COMPANY)
            {
                $this->transferContacts($moduleID,$_GET["siteID"]);
            }
        }
        return $status;
    }
    
    public function getModuleID()
    {
        return $this->__get($this->module_id);
    }
    
    public function isLoaded()
    {
        if($this->record)
        {
            return true;
        }
        return false;
    }
    
    public function sendTemplateEMail($template, $emailAddress,
        $logMessage = true, $replyTo = array(), $wrapLinesAt = 78
        )
    {
        if(!$this->isLoaded())
        {
            return false;
        }
        $emailTemplates = new EmailTemplates($this->_siteID);
        $emailTemplates = $emailTemplates->getByTitle($template);
        if(empty($emailTemplates)) return;
        $subject=$emailTemplates["emailTemplateTitle"];
        $body=$this->renderTemplateVars($emailTemplates["text"]);
 
        $settings = new MailerSettings($this->_siteID);
        $settings_config = $settings->getAll();
        $mailer = new Mailer($this->_siteID);
        return $mailer->send(
            array($settings_config['fromAddress'], ''),
            array($emailAddress,''),
            $subject,
            $body,
            true,
            $logMessage,
            $replyTo,
            $wrapLinesAt,
            false,
                $this->module
        );
    }
    
    public function sendEMail($userID, $destination, $subject, $body, $isHTML = false,
        $logMessage = true, $replyTo = array(), $wrapLinesAt = 78,
        $signature = true)
    {
        $arrEmailData=array();
        $arrEmailData["id"]=$this->getModuleID();
        if($this->module=="candidates")
        {
            $arrEmailData["email"][]=array("email"=>$destination,"name"=>$this->first_name." ".$this->last_name);
        }
        else if($this->module=="contact")
        {
            $arrEmailData["email"][]=array("email"=>$destination,"name"=>$this->first_name." ".$this->last_name);
        }
        else
        {
            $arrEmailData["email"][]=array("email"=>$destination,"name"=>$destination);
        }
        $settings = new MailerSettings($this->_siteID);
        $settings_config = $settings->getAll();
        $mailer = new Mailer($this->_siteID, $userID);
        return $mailer->send(
            array($settings_config['fromAddress'], ''),
            $arrEmailData,
            $subject,
            $body,
            $isHTML,
            $logMessage,
            $replyTo,
            $wrapLinesAt,
            $signature,
                $this->module
        );
    }
    
    public function copyRecord($id)
    {
        $objSQL=new ClsNaanalSQL();
        $objSQL->addTable($this->module_table);
        $objSQL->addWhereNew($this->module_table,$this->module_id,$id);
        $sql=$objSQL->render();
        $record=$this->_db->getAssoc($sql);
        unset($record[$this->module_id]);
        
        $objSQL=new ClsNaanalSQL("INSERT");
        $objSQL->addTable($this->module_table);
        foreach($record as $field=>$data)
        {
            if($field=="site_id")
            {
                $objSQL->addValue("site_id", $_GET["siteID"]);
            }
            else
                $objSQL->addValue($field, $data);
        }
        $sql=$objSQL->render();
        $this->_db->query($sql);
        $insid=$this->_db->getLastInsertId();
        $efield=new ExtraFields($this->_siteID, $this->data_item_type);
        $records=$efield->getValues($id);
        
        if($records)
        {
            $efield=new ExtraFields($_GET["siteID"], $this->data_item_type);
            foreach($records as $record)
            {
                $efield->setValue($record["fieldName"], $record["value"], $insid);
            }
        }
        //if($this->data_item_type===DATA_ITEM_CANDIDATE)
        //{
            $this->copyAttachment($id,$insid,$_GET["siteID"]);
        //}
        return true;
    }
    /**
     * Transfer attachment to another site.
     *
     * @param integer Module ID.
     * @param integer Site ID.
     * @return true/false.
     */
    public function transferAttachment($moduleID, $siteID)
    {
        $sql = sprintf(
            "UPDATE 
                attachment
            SET
                site_id=%s
            WHERE
                attachment.data_item_type = %s
            AND
                attachment.data_item_id = %s
            AND
                attachment.site_id = %s",
            $siteID,
            $this->data_item_type,
            $this->_db->makeQueryInteger($moduleID),
            $this->_siteID
        );

        return $this->_db->query($sql);
    }
    
    /**
     * Transfer contacts of a company to another site.
     *
     * @param integer Company ID.
     * @param integer Site ID.
     * @return true/false.
     */
    public function transferContacts($moduleID, $siteID)
    {
        $sql = sprintf(
            "UPDATE 
                contact
            SET
                site_id=%s
            WHERE
                company_id = %s
            AND
                site_id = %s",
            $siteID,
            $this->_db->makeQueryInteger($moduleID),
            $this->_siteID
        );

        return $this->_db->query($sql);
    }

    /**
     * Copy attachment to same site or another site.
     *
     * @param integer Module ID.
     * @param integer Site ID.
     * @return true/false.
     */
    public function copyAttachment($moduleID, $toModuleID, $siteID)
    {
        $arrResume=$this->getAllAttachmentFields($moduleID);
        foreach($arrResume as $resume)
        {
            unset($resume["attachment_id"]);
            $resume["site_id"]=$siteID;
            $file=$resume["directory_name"].$resume["stored_filename"];
            $arrPatIhnfo=pathinfo($file);
            $resume["original_filename"]=$arrPatIhnfo["filename"]."_copy.{$arrPatIhnfo["extension"]}";
            $resume["stored_filename"]=$arrPatIhnfo["filename"]."_copy.{$arrPatIhnfo["extension"]}";
            if(file_exists("{$resume["directory_name"]}{$arrPatIhnfo["filename"]}.{$arrPatIhnfo["extension"]}"))
            {
                copy("{$resume["directory_name"]}{$arrPatIhnfo["filename"]}.{$arrPatIhnfo["extension"]}","{$arrPatIhnfo["directory_name"]}{$arrPatIhnfo["filename"]}_copy.{$arrPatIhnfo["extension"]}");
            }
            $resume["text"]=  addslashes($resume["text"]);
            $resume["data_item_id"]=$toModuleID;
            $objSQL=new ClsNaanalSQL("INSERT");
            $objSQL->addTable("attachment");
            foreach($resume as $field=>$data)
            {
                $objSQL->addValue($field, $data);
            }
            $sql=$objSQL->render();
            $this->_db->query($sql);
        }
        return true;
    }
    /**
     * Returns all resumes for a candidate.
     *
     * @param integer Candidate ID.
     * @return array Multi-dimensional associative result set array of
     *               candidate attachments data, or array() if no records were
     *               returned.
     */
    public function getAllAttachmentFields($moduleID)
    {
        $sql = sprintf(
            "SELECT
                *
            FROM
                attachment
            WHERE
                resume = 1
            AND
                attachment.data_item_type = %s
            AND
                attachment.data_item_id = %s
            AND
                attachment.site_id = %s",
            $this->data_item_type,
            $this->_db->makeQueryInteger($moduleID),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }
}
?>