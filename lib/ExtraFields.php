<?php
/*
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
 * $Id: ExtraFields.php 3767 2007-11-29 16:49:10Z brian $
 */

/* 
 * CandidATS
 * Extra Field Handler
 *
 * Copyright (C) 2014 - 2015 Auieo Software Private Limited, Parent Company of Unicomtech.
 * 
 * This Modified Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
 
include_once('lib/Site.php');
 
/**
 *	Extra Fields Library
 *	@package    CATS
 *	@subpackage Library
 */
 
class ExtraFields 
{
    private $_db;
    private $_siteID;
    private $_dataItemType;

    public function __construct($siteID, $dataItemType)
    {
        $this->_siteID = $siteID;
        $this->_dataItemType = $dataItemType;
        $this->_db = DatabaseConnection::getInstance();
    }

    /**
     * Returns extra fields specified by an SA for a site.
     *
     * @return response array
     */
    public function getSettings()
    {
        $sql = sprintf(
            "SELECT
                auieo_fields.fieldname AS fieldName,
                auieo_fields.id AS extraFieldSettingsID,
                auieo_fields.uitype as extraFieldType,
                auieo_fields.field_options as extraFieldOptions,
                auieo_fields.site_id AS siteID
            FROM
                auieo_fields
            WHERE
                auieo_fields.site_id = %s
            AND
                auieo_fields.data_item_type = %s
            AND
                auieo_fields.is_extra=1
            ORDER BY
                auieo_fields.position ASC",
            $this->_siteID,
            $this->_dataItemType
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Creates a new extra field for a record type in a site.
     *
     * @param string field name
     * @param integer field type (check constants.php)
     * @return boolean query response
     */
    public function define($fieldName, $fieldType)
    {
        $sql = sprintf(
            "INSERT INTO auieo_fields (
                fieldname,
                site_id,
                date_created,
                data_item_type,
                uitype,
                is_extra
             )
             VALUES (
                %s,
                %s,
                NOW(),
                %s,
                %s,
                1
             )",
             $this->_db->makeQueryString($fieldName),
             $this->_siteID,
             $this->_dataItemType,
             $this->_db->makeQueryInteger($fieldType)
        );
        $this->_db->query($sql);
        
        $table="";
        if($this->_dataItemType==100)
        {
            $table="candidate";
        }
        else if($this->_dataItemType==200)
        {
            $table="company";
        }
        else if($this->_dataItemType==300)
        {
            $table="contact";
        }
        else if($this->_dataItemType==400)
        {
            $table="joborder";
        }
        $fieldTypeNum=$this->_db->makeQueryInteger($fieldType);
        $fieldType="VARCHAR(255)";
        if($fieldTypeNum==2)
        {
            $maximumlength=0;
            $fieldType="TEXT";
        }
        else if($fieldTypeNum==3)
        {
            $maximumlength=1;
            $fieldType="INT(1)";
        }
        else if($fieldTypeNum==4)
        {
            $fieldType="DATETIME";
        }

        $this->_db->query("ALTER IGNORE TABLE `{$table}` ADD COLUMN `{$fieldName}` {$fieldType} default NULL");
        /* Force this new extra field to have a position. */
        $sql = sprintf(
            "UPDATE 
                auieo_fields
             SET
                position = %s
             WHERE
                id = %s
             AND
                site_id = %s",
             $this->_db->getLastInsertID(),
             $this->_db->getLastInsertID(),
             $this->_siteID
        );
        $this->_db->query($sql);
    }
    
    /**
     * Deletes an extra field for a record type in a site.
     *
     * @param string field name
     * @return boolean query response
     */
    public function remove($fieldName)
    {
        $sql = sprintf(
            "DELETE FROM
                auieo_fields
             WHERE
                fieldname = %s
             AND
                site_id = %s
            AND
                auieo_fields.is_extra=1
             AND
                data_item_type = %s",
             $this->_db->makeQueryString($fieldName),
             $this->_siteID,
             $this->_dataItemType
        );
        $this->_db->query($sql);
        $table="";
        if($this->_dataItemType==100)
        {
            $table="candidate";
        }
        else if($this->_dataItemType==200)
        {
            $table="company";
        }
        else if($this->_dataItemType==300)
        {
            $table="contact";
        }
        else if($this->_dataItemType==400)
        {
            $table="joborder";
        }
        $this->_db->query("ALTER TABLE `{$table}` DROP `{$fieldName}`");
    }    

    /**
     * Creates a new option under a field which allows
     * multiple options (dropdown, radio boxes)
     *
     * @param string field name
     * @param string option name
     * @return boolean query response
     */
    public function addOptionToColumn($fieldName, $optionName)
    {
        $sql = sprintf(
            "SELECT
                auieo_fields.field_options as extraFieldOptions
            FROM
                auieo_fields
            WHERE
                auieo_fields.site_id = %s
            AND
                auieo_fields.data_item_type = %s
            AND
                auieo_fields.is_extra=1
            AND
                auieo_fields.fieldname = %s",
            $this->_siteID,
            $this->_dataItemType,
            $this->_db->makeQueryString($fieldName)
        );

        $rs = $this->_db->getAssoc($sql);
       
        $options = explode(',', $rs['extraFieldOptions']);
       
        /* First delete it if it is already an option... */
        foreach ($options as $index => $data)
        {
           if ($data == urlencode($optionName))
           {
              unset($options[$index]);
           }
        }
        
        $options[] = urlencode($optionName);
       
        $sql = sprintf(
            "UPDATE
               auieo_fields
             SET
               field_options = %s
             WHERE
               auieo_fields.site_id = %s
             AND
               auieo_fields.data_item_type = %s
             AND
               auieo_fields.fieldname = %s
            AND
                auieo_fields.is_extra=1
             ",
             $this->_db->makeQueryString(implode(',', $options)),
             $this->_siteID,
             $this->_dataItemType,
             $this->_db->makeQueryString($fieldName)
        );
        $this->_db->query($sql);
    }

    /**
     * Deletes an option under a field which allows  multiple options 
     * (dropdown, radio boxes)
     *
     * @param string field name
     * @param string option name
     * @return boolean query response
     */
    public function deleteOptionFromColumn($fieldName, $optionName)
    {
        $sql = sprintf(
            "SELECT
                auieo_fields.field_options as extraFieldOptions
            FROM
                auieo_fields
            WHERE
                auieo_fields.site_id = %s
            AND
                auieo_fields.data_item_type = %s
            AND
                auieo_fields.is_extra=1
            AND
                auieo_fields.fieldname = %s",
            $this->_siteID,
            $this->_dataItemType,
            $this->_db->makeQueryString($fieldName)
        );

        $rs = $this->_db->getAssoc($sql);
       
        $options = explode(',', $rs['extraFieldOptions']);
       
        foreach ($options as $index => $data)
        {
           if ($data == urlencode($optionName))
           {
              unset($options[$index]);
           }
        }
       
        $sql = sprintf(
            "UPDATE
               auieo_fields
             SET
               field_options = %s
             WHERE
               auieo_fields.site_id = %s
             AND
               auieo_fields.data_item_type = %s
            AND
                auieo_fields.is_extra=1
             AND
               auieo_fields.fieldname = %s
             ",
             $this->_db->makeQueryString(implode(',', $options)),
             $this->_siteID,
             $this->_dataItemType,
             $this->_db->makeQueryString($fieldName)
        );
        $this->_db->query($sql);
    }

    /**
     * Swaps 2 columns position parameter.  Usefull for reordering extra fields.
     * //FIXME: Sanity Checks
     *
     * @param string field name
     * @param string field name 2
     * @return boolean query response
     */    
    public function swapColumns($fieldName1, $fieldName2)
    {
        $sql = sprintf(
            "SELECT
                auieo_fields.position as position
            FROM
                auieo_fields
            WHERE
                auieo_fields.site_id = %s
            AND
                auieo_fields.data_item_type = %s
            AND
                auieo_fields.is_extra=1
            AND
                auieo_fields.fieldname = %s",
            $this->_siteID,
            $this->_dataItemType,
            $this->_db->makeQueryString($fieldName1)
        );
        
        $rs = $this->_db->getAssoc($sql);
        
        $fieldPosition1 = $rs['position'];

        $sql = sprintf(
            "SELECT
                auieo_fields.position as position
            FROM
                auieo_fields
            WHERE
                auieo_fields.site_id = %s
            AND
                auieo_fields.data_item_type = %s
            AND
                auieo_fields.is_extra=1
            AND
                auieo_fields.fieldname = %s",
            $this->_siteID,
            $this->_dataItemType,
            $this->_db->makeQueryString($fieldName2)
        );
        
        $rs = $this->_db->getAssoc($sql);
        
        $fieldPosition2 = $rs['position'];
 
        $sql = sprintf(
            "UPDATE
                auieo_fields
            SET 
                auieo_fields.position = %s
            WHERE
                auieo_fields.site_id = %s
            AND
                auieo_fields.data_item_type = %s
            AND
                auieo_fields.is_extra=1
            AND
                auieo_fields.fieldname = %s",
            $fieldPosition2,
            $this->_siteID,
            $this->_dataItemType,
            $this->_db->makeQueryString($fieldName1)
        );
        
        $rs = $this->_db->query($sql);
        
        $sql = sprintf(
            "UPDATE
                auieo_fields
            SET 
                auieo_fields.position = %s
            WHERE
                auieo_fields.site_id = %s
            AND
                auieo_fields.data_item_type = %s
            AND
                auieo_fields.is_extra=1
            AND
                auieo_fields.fieldname = %s",
            $fieldPosition1,
            $this->_siteID,
            $this->_dataItemType,
            $this->_db->makeQueryString($fieldName2)
        );
        
        $rs = $this->_db->query($sql);  
    }
    
    /**
     * Swaps 2 columns position parameter.  Usefull for reordering extra fields.
     * //FIXME: Sanity Checks
     *
     * @param string field name
     * @param string field name 2
     * @return boolean query response
     */    
    public function renameColumn($oldName, $newName)
    { 
        $sql = sprintf(
            "UPDATE
                auieo_fields
            SET 
                auieo_fields.fieldname = %s
            WHERE
                auieo_fields.site_id = %s
            AND
                auieo_fields.data_item_type = %s
            AND
                auieo_fields.is_extra=1
            AND
                auieo_fields.fieldname = %s",
            $this->_db->makeQueryString($newName),
            $this->_siteID,
            $this->_dataItemType,
            $this->_db->makeQueryString($oldName)
        );
        
        $rs = $this->_db->query($sql);
        
        $sql = sprintf(
            "UPDATE
                extra_field
            SET 
                extra_field.fieldname = %s
            WHERE
                extra_field.site_id = %s
            AND
                extra_field.data_item_type = %s
            AND
                extra_field.fieldname = %s",
            $this->_db->makeQueryString($newName),
            $this->_siteID,
            $this->_dataItemType,
            $this->_db->makeQueryString($oldName)
        );
        
        $rs = $this->_db->query($sql);   
    }

    /**
     * Returns all extra fields fields for a company.
     *
     * @param integer candidate ID
     * @return array extra fields data
     */
    public function getValues($record_id)
    {
        $arrTableInfo=getTableInfoByDataItemType($this->_dataItemType);
        $sql = sprintf(
            "SELECT
                *
            FROM
                auieo_fields
            WHERE
            data_item_type={$this->_dataItemType}
               AND
            is_extra=1
            AND
               site_id = %s",
            $this->_db->makeQueryInteger($record_id),
            $this->_siteID
            
        );

        return $this->_db->getAssoc($sql);
    }
    /**
     * transfer all the extra field data to another site if the field name matches
     * @param type $moduleID
     * @param type $siteID
     */
    public function transferSite($moduleID, $siteID)
    {
        /**
         * get all the fields from the field settings
         */
        $objSQL=new ClsNaanalSQL();
        $objSQL->addTable("auieo_fields");
        $objSQL->addWhereNew("auieo_fields","data_item_type", $this->_dataItemType);
        $objSQL->addWhereNew("auieo_fields","site_id", $this->_siteID);
        $sql=$objSQL->render();
        $records=$this->_db->getAllAssoc($sql);
        if($records)
        foreach($records as $record)
        {
            /**
             * verify whether the field exist in remote site
             */
            $objSQL=new ClsNaanalSQL();
            $objSQL->addTable("auieo_fields");
            $objSQL->addWhereNew("auieo_fields","data_item_type", $this->_dataItemType);
            $objSQL->addWhereNew("auieo_fields","fieldname", $record["fieldname"]);
            $objSQL->addWhereNew("auieo_fields","site_id", $siteID);
            $sql=$objSQL->render();
            $row=$this->_db->getAssoc($sql);
            if(empty($row)) continue;
            $sql="update extra_field set site_id={$siteID} where fieldname='{$record["fieldname"]}' 
            and site_id={$this->_siteID} and data_item_type='{$this->_dataItemType}'
            and data_item_id={$moduleID}";
            $this->_db->query($sql);
        }
        return true;
    }

    /**
     * Sets an extra field (even if it previously existed).
     * If the requested field not exist, the data will not updated
     * 
     * @param string field name
     * @param string field value
     * @param integer candidate ID
     * @return boolean True if successful; false otherwise.
     */
    public function setValue($field, $value, $moduleID)
    {
        $moduleInfo=getModuleInfo("data_item_type");
        $sql="update {$moduleInfo[$this->_dataItemType]["tablename"]} set `{$field}`='{$value}' where {$moduleInfo[$this->_dataItemType]["primarykey"]}={$moduleID}";
        return (boolean) $this->_db->query($sql);
    }
    
    /**
     * Deletes all extra fields associated with a candidate.
     *
     * @param integer candidate ID
     * @return boolean True if successful; false otherwise.
     */
    public function deleteValueByDataItemID($dataItemID)
    {
        /*$sql = sprintf(
            "DELETE FROM
                extra_field
            WHERE
                extra_field.data_item_id = %s
            AND
                extra_field.site_id = %s
            AND
                extra_field.data_item_type = %s",
            $this->_db->makeQueryInteger($dataItemID),
            $this->_siteID,
            $this->_dataItemType
        );

        return (boolean) $this->_db->query($sql);*/
    }
    
    /**
     * Returns an array of extra fields which are HTML formatted to be displayed
     * on the associated data item's show() method.
     *
     * @param integer data item ID
     * @return array extra fields
     */
    public function getValuesForShow($dataItemID)
    {
        $extraFields = $this->_getValuesWithSettings($dataItemID);
        
        foreach ($extraFields as $index => $data)
        {
            switch ($data['extraFieldType'])
            {
                case EXTRA_FIELD_CHECKBOX:
                    if ($extraFields[$index]['value'] == '')
                    {
                        $extraFields[$index]['display'] = 'No';
                    }
                    else
                    {
                        $extraFields[$index]['display'] = $extraFields[$index]['value'];
                    }
                break;
                
                case EXTRA_FIELD_TEXTAREA:
                    $extraFields[$index]['display'] = nl2br(htmlspecialchars($extraFields[$index]['value']));
                break;
                
                case EXTRA_FIELD_DATE:
                    $dmy = false;
                    
                    if (isset($_SESSION['CATS']) && $_SESSION['CATS']->isLoggedIn())
                    {
                        if ($_SESSION['CATS']->isDateDMY())
                        {
                            $dmy = true;
                        }
                    } 
                    else 
                    {
                        // Look up the sites preference. (This would happen on careersUI)
                        $site = new Site($this->_siteID);
                        $siteRS = $site->getSiteBySiteID($this->_siteID);
                        
                        if ($siteRS['dateFormatDDMMYY'] == 1)
                        {
                            $dmy = true; 
                        }
                    }
                    
                    if ($dmy)
                    {
                        $dateParts = explode('-', $extraFields[$index]['value']);
                        if (count($dateParts) > 2)
                        {
                            $t = $dateParts[0];
                            $dateParts[0] = $dateParts[1];
                            $dateParts[1] = $t;
                        }
                        $date = implode('-', $dateParts);
                        
                        $extraFields[$index]['display'] = htmlspecialchars($date);
                    }
                    else
                    {
                        $extraFields[$index]['display'] = htmlspecialchars($extraFields[$index]['value']);
                    }
                break;
                
                case EXTRA_FIELD_TEXT:
                case EXTRA_FIELD_DROPDOWN:
                case EXTRA_FIELD_RADIO:
                default:
                    $extraFields[$index]['display'] = htmlspecialchars($extraFields[$index]['value']);
                break;
            }
        }
        
        return $extraFields;
    }
    
    /**
     * Returns an array of extra fields which are HTML formatted to be displayed
     * on the associated data item's add() method with associated input elements.
     *
     * @return array extra fields
     */
    public function getValuesForAdd()
    {
        $extraFields = $this->getSettings();
        
        foreach ($extraFields as $index => $data)
        {
            switch ($data['extraFieldType'])
            {
                case EXTRA_FIELD_CHECKBOX:
                    $extraFields[$index]['addHTML'] = '
                        <input type="checkbox" class="inputbox" id="extraFieldCB'.$index.'" name="extraFieldCB'.$index.'" onclick="if (this.checked) {document.getElementById(\'extraField'.$index.'\').value=\'Yes\';} else {document.getElementById(\'extraField'.$index.'\').value=\'No\';}" />
                        <input type="hidden" id="extraField'.$index.'" name="extraField'.$index.'" />
                    ';
                break;
                
                case EXTRA_FIELD_TEXTAREA:
                    $extraFields[$index]['addHTML'] = '
                        <textarea id="extraField'.$index.'" class="inputbox" name="extraField'.$index.'" style="width: 150px;" ></textarea>
                    ';
                    $extraFields[$index]['careersAddHTML'] = '
                        <textarea id="extraField'.$index.'" class="inputBoxArea" name="extraField'.$index.'"></textarea>
                    ';
                break;
                
                case EXTRA_FIELD_DROPDOWN:
                    $extraFields[$index]['addHTML'] = '
                        <select id="extraField'.$index.'" class="selectBox" name="extraField'.$index.'" style="width: 150px;">
                           <option value="" selected>- Select from List -</option>
                    ';
                    $extraFields[$index]['careersAddHTML'] = '
                        <select id="extraField'.$index.'" class="inputBoxNormal" name="extraField'.$index.'">
                           <option value="" selected>- Select from List -</option>
                    ';
                    
                    $options = explode(',', $data['extraFieldOptions']);
                    
                    foreach($options as $option)
                    {
                        if ($option != '')
                        {
                           $extraFields[$index]['addHTML'] .= '<option value="'.htmlspecialchars(urldecode($option)).'">'.htmlspecialchars(urldecode($option)).'</option>';
                           $extraFields[$index]['careersAddHTML'] .= '<option value="'.htmlspecialchars(urldecode($option)).'">'.htmlspecialchars(urldecode($option)).'</option>';
                        }
                    }
                    
                    $extraFields[$index]['addHTML'] .= '</select>';
                    $extraFields[$index]['careersAddHTML'] .= '</select>';
                break;

                case EXTRA_FIELD_RADIO:
                    $options = explode(',', $data['extraFieldOptions']);
                    
                    $extraFields[$index]['addHTML'] ='';
                    
                    foreach($options as $option)
                    {
                        if ($option != '')
                        {
                           $extraFields[$index]['addHTML'] .= '<input type="radio" name="extraField'.$index.'" value="'.htmlspecialchars(urldecode($option)).'">'.htmlspecialchars(urldecode($option)).'<br \>';
                        }
                    }
                break;
                
                case EXTRA_FIELD_DATE:
                    $extraFields[$index]['addHTML'] = '<script type="text/javascript">DateInput(\'extraField'.$index.'\', false, \'MM-DD-YY\', \'\');</script>';
                break;
                
                case EXTRA_FIELD_TEXT:
                default:
                    $extraFields[$index]['addHTML'] = '
                        <input id="extraField'.$index.'" class="inputbox" name="extraField'.$index.'" style="width: 150px;"  />
                    ';
                    $extraFields[$index]['careersAddHTML'] = '
                        <input id="extraField'.$index.'" class="inputBoxNormal" name="extraField'.$index.'"/>
                    ';
                break;
            }
        }
        
        return $extraFields;
    }    
    
    /**
     * Returns a row to add to an associated data item's datagrid,
     * which allows the extra fields to be displayed on the datagrid.
     * FIXME: Why are we passing a database handle?
     * 
     * @param string md5 unique index name for this datagrid
     * @param array data extra field definition
     * @param handle database handle
     * @return array datagrid class entry
     */
    public function getDataGridDefinition($uniqueIndex, $data, $db)
    {
        switch ($this->_dataItemType)
        {
            case DATA_ITEM_JOBORDER:
                $column = 'joborder.joborder_id';
                break;
                
            case DATA_ITEM_CANDIDATE:
                $column = 'candidate.candidate_id';
                break;

            case DATA_ITEM_CONTACT:
                $column = 'contact.contact_id';
                break;

            case DATA_ITEM_COMPANY:
            default:
                $column = 'company.company_id';
                break;
        }
        
        switch ($data['extraFieldType'])
        {
            case EXTRA_FIELD_CHECKBOX:
               return array('select'       => '`'.$data['fieldName'].'` AS `'.$data['fieldName'].'`',
                          'join'         => ' ',
                          'pagerRender'          => 'return ($rsData[\'extra_field_value' . $uniqueIndex . '\'] == \'Yes\' ? \'Yes\' : \'No\');',
                          'exportRender'          => 'return ($rsData[\'extra_field_value' . $uniqueIndex . '\'] == \'Yes\' ? \'Yes\' : \'No\');',
                          'sortableColumn'         => '`'.$data['fieldName'].'`',
                          'pagerWidth'  => 45,
                          'filter' => 'IF (`'.$data['fieldName'].'`.value = "Yes", "Yes", "No")');
            break;
            
            case EXTRA_FIELD_DATE:
                return array('select'  =>'`'.$data['fieldName'].'` AS `'.$data['fieldName'].'`',
                          'join'    => '',
                          'pagerRender'     => 'if (isset($_SESSION[\'CATS\']) && $_SESSION[\'CATS\']->isLoggedIn() && $_SESSION[\'CATS\']->isDateDMY())
                                        {
                                              $dateParts = explode(\'-\',  $rsData[\'extra_field_value' . $uniqueIndex . '\']);
                                              if (count($dateParts) > 2)
                                              {
                                                    $t = $dateParts[0];
                                                    $dateParts[0] = $dateParts[1];
                                                    $dateParts[1] = $t;
                                              }
                                              $date = implode(\'-\', $dateParts);
                                              return $date;
                                        }
                                        else
                                        {
                                             return $rsData[\'extra_field_value' . $uniqueIndex . '\'];
                                        }',
                          'exportRender'     => 'if (isset($_SESSION[\'CATS\']) && $_SESSION[\'CATS\']->isLoggedIn() && $_SESSION[\'CATS\']->isDateDMY())
                                        {
                                              $dateParts = explode(\'-\',  $rsData[\'extra_field_value' . $uniqueIndex . '\']);
                                              if (count($dateParts) > 2)
                                              {
                                                    $t = $dateParts[0];
                                                    $dateParts[0] = $dateParts[1];
                                                    $dateParts[1] = $t;
                                              }
                                              $date = implode(\'-\', $dateParts);
                                              return $date;
                                        }
                                        else
                                        {
                                             return $rsData[\'extra_field_value' . $uniqueIndex . '\'];
                                        }',
                          'sortableColumn'       => '`'.$data['fieldName'].'`',
                          'pagerWidth' => 110,
                          'filter' => '`'.$data['fieldName'].'`');
            
            case EXTRA_FIELD_TEXT:
            default:
                return array('select'  =>'`'.$data['fieldName'].'` AS `'.$data['fieldName'].'`',
                          'join'    => '',
                          'sortableColumn'    => '`'.$data['fieldName'].'`',
                          'pagerWidth'   => 110,
                          'filter' => '`'.$data['fieldName'].'`',
                          'filterTypes'   => '===>=<=~');
            break;
        }
    }
    
    /**
     * Returns an array of extra fields which are HTML formatted to be displayed
     * on the associated data item's edit() method with associated input elements.
     *
     * @param integer data item ID
     * @return array extra fields
     */
    public function getValuesForEdit($dataItemID)
    {
        $extraFields = $this->_getValuesWithSettings($dataItemID);
        
        foreach ($extraFields as $index => $data)
        {
            switch ($data['extraFieldType'])
            {
                case EXTRA_FIELD_CHECKBOX:
                    $extraFields[$index]['editHTML'] = '
                        <input type="checkbox" class="inputbox" id="extraFieldCB'.$index.'" name="extraFieldCB'.$index.'" ' .($data['value'] == 'Yes' ? 'checked' : '') . ' onclick="if (this.checked) {document.getElementById(\'extraField'.$index.'\').value=\'Yes\';} else {document.getElementById(\'extraField'.$index.'\').value=\'No\';}" />
                        <input type="hidden" id="extraField'.$index.'" name="extraField'.$index.'" value="'.htmlspecialchars($data['value']).'" />
                    ';
                break;
                
                case EXTRA_FIELD_TEXTAREA:
                    $extraFields[$index]['editHTML'] = '
                        <textarea id="extraField'.$index.'" class="inputbox" name="extraField'.$index.'" style="width: 150px;" >'.htmlspecialchars($data['value']).'</textarea>
                    ';
                break;
                
                case EXTRA_FIELD_DROPDOWN:
                    $extraFields[$index]['editHTML'] = '
                        <select id="extraField'.$index.'" class="selectBox" name="extraField'.$index.'" style="width: 150px;">
                           <option value=""></option>
                    ';
                    
                    $options = explode(',', $data['extraFieldOptions']);
                    
                    foreach($options as $option)
                    {
                        if ($option != '')
                        {
                           $extraFields[$index]['editHTML'] .= '<option value="'.htmlspecialchars(urldecode($option)).'" '.(urldecode($option) == $data['value'] ? 'selected' : '').'>'.htmlspecialchars(urldecode($option)).'</option>';
                        }
                    }
                    
                    if (!in_array($data['value'], $options))
                    {
                           $extraFields[$index]['editHTML'] .= '<option value="'.htmlspecialchars($data['value']).'" selected>'.htmlspecialchars($data['value']).'</option>';
                    }
                    
                    $extraFields[$index]['editHTML'] .= '</select>';
                break;
                
                case EXTRA_FIELD_RADIO:
                    $options = explode(',', $data['extraFieldOptions']);
                    
                    $extraFields[$index]['editHTML'] = '';
                    
                    foreach($options as $option)
                    {
                        if ($option != '')
                        {
                           $extraFields[$index]['editHTML'] .= '<input type="radio" name="extraField'.$index.'" value="'.htmlspecialchars(urldecode($option)).'" '.(urldecode($option) == $data['value'] ? 'checked' : '').'>'.htmlspecialchars(urldecode($option)).'<br \>';
                        }
                    }
                break;
                
                case EXTRA_FIELD_DATE:
                    $extraFields[$index]['editHTML'] = '<script type="text/javascript">DateInput(\'extraField'.$index.'\', false, \'MM-DD-YY\', \''.$data['value'].'\');</script>';
                break;
                                    
                case EXTRA_FIELD_TEXT:
                default:
                    $extraFields[$index]['editHTML'] = '
                        <input id="extraField'.$index.'" class="inputbox" name="extraField'.$index.'" value="'.htmlspecialchars($data['value']).'" style="width: 150px;" />
                    ';
                break;
            }
        }
        
        return $extraFields;
    }
    
    /**
     * Checks $_POST for values set by onEdit, and updates the associated extra fields
     * for the data item.
     *
     * @param integer data item DI
     * @return void
     */
    public function setValuesOnEdit($dataItemID)
    {
        $extraFields = $this->_getValuesWithSettings($dataItemID);

        for ($i = 0; $i < count($extraFields); $i++)
        {
            if (isset($_POST['extraField' . $i]) && $extraFields[$i]['value'] != $_POST['extraField' . $i])
            {
               $this->setValue($extraFields[$i]['fieldName'], $_POST['extraField' . $i], $dataItemID);
            }
        }
    }
    
    /**
     * Returns a static array of the types of extra fields which can be set with the extra
     * field editor.
     *
     * @return array extra fields
     */
    public static function getValuesTypes()
    {
        return array (
            EXTRA_FIELD_TEXT => array(
                'name' => 'Text Box',
                'hasOptions' => false
                ),
            EXTRA_FIELD_TEXTAREA => array(
                'name' => 'Multiline Text Box',
                'hasOptions' => false
                ),
            EXTRA_FIELD_CHECKBOX => array(
                'name' => 'Check Box',
                'hasOptions' => false
                ),
            EXTRA_FIELD_DROPDOWN => array(
                'name' => 'Dropdown List',
                'hasOptions' => true
                ),
            EXTRA_FIELD_RADIO => array(
                'name' => 'Radio Button List',
                'hasOptions' => true
                ),
            EXTRA_FIELD_DATE => array(
                'name' => 'Date',
                'hasOptions' => false
                ),
          );
    }            
    
    //TODO: PHPDOC
    //Takes the extra field settings result set and populates it with any values set for the extra fields.
    private function _getValuesWithSettings($dataItemID)
    {
        $extraFieldSettingsRS = $this->getSettings();
        $extraFieldRS = $this->getValues($dataItemID);
        
        foreach ($extraFieldSettingsRS as $index => $data)
        {
            $extraFieldSettingsRS[$index]['value'] = '';
            
            foreach ($extraFieldRS as $key => $val)
            {
                if ($key == $data['fieldName'])
                {
                    $extraFieldSettingsRS[$index]['value'] = $val;
                }
            }        
        }
        return $extraFieldSettingsRS;
    }
    

}
