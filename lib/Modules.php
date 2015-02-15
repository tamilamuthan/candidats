<?php
/* 
 * CandidATS
 * Document to Text Conversion Library
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