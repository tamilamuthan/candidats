<?php
/* 
 * CandidATS
 * Module Table Field Handler
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
 
class AuieoFields 
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
                auieo_fields.field_name AS fieldName,
                auieo_fields.id AS extraFieldSettingsID,
                auieo_fields.extra_field_type as extraFieldType,
                auieo_fields.extra_field_options as extraFieldOptions,
                auieo_fields.site_id AS siteID
            FROM
                auieo_fields
            WHERE
                auieo_fields.site_id = %s
            AND
                auieo_fields.data_item_type = %s
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
                field_name,
                site_id,
                date_created,
                data_item_type,
                extra_field_type
             )
             VALUES (
                %s,
                %s,
                NOW(),
                %s,
                %s
             )",
             $this->_db->makeQueryString($fieldName),
             $this->_siteID,
             $this->_dataItemType,
             $this->_db->makeQueryInteger($fieldType)
        );
        $this->_db->query($sql);
        
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
                field_name = %s
             AND
                site_id = %s
             AND
                data_item_type = %s",
             $this->_db->makeQueryString($fieldName),
             $this->_siteID,
             $this->_dataItemType
        );
        $this->_db->query($sql);
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
                auieo_fields.extra_field_options as extraFieldOptions
            FROM
                auieo_fields
            WHERE
                auieo_fields.site_id = %s
            AND
                auieo_fields.data_item_type = %s
            AND
                auieo_fields.field_name = %s",
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
               extra_field_options = %s
             WHERE
               auieo_fields.site_id = %s
             AND
               auieo_fields.data_item_type = %s
             AND
               auieo_fields.field_name = %s
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
                auieo_fields.extra_field_options as extraFieldOptions
            FROM
                auieo_fields
            WHERE
                auieo_fields.site_id = %s
            AND
                auieo_fields.data_item_type = %s
            AND
                auieo_fields.field_name = %s",
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
               extra_field_options = %s
             WHERE
               auieo_fields.site_id = %s
             AND
               auieo_fields.data_item_type = %s
             AND
               auieo_fields.field_name = %s
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
                auieo_fields.field_name = %s",
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
                auieo_fields.field_name = %s",
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
                auieo_fields.field_name = %s",
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
                auieo_fields.field_name = %s",
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
                auieo_fields.field_name = %s
            WHERE
                auieo_fields.site_id = %s
            AND
                auieo_fields.data_item_type = %s
            AND
                auieo_fields.field_name = %s",
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
                extra_field.field_name = %s
            WHERE
                extra_field.site_id = %s
            AND
                extra_field.data_item_type = %s
            AND
                extra_field.field_name = %s",
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
        $sql = sprintf(
            "SELECT
                extra_field.field_name AS fieldName,
                extra_field.value AS value,
                extra_field.extra_field_id AS extraFieldSettingsID,
                extra_field.data_item_id AS dataItemID
            FROM
                extra_field
            WHERE
                extra_field.data_item_id = %s
            AND
                extra_field.data_item_type = %s
            AND
                extra_field.site_id = %s",
            $this->_db->makeQueryInteger($record_id),
            $this->_dataItemType,
            $this->_siteID
            
        );

        return $this->_db->getAllAssoc($sql);
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
            $objSQL->addWhereNew("auieo_fields","field_name", $record["field_name"]);
            $objSQL->addWhereNew("auieo_fields","site_id", $siteID);
            $sql=$objSQL->render();
            $row=$this->_db->getAssoc($sql);
            if(empty($row)) continue;
            $sql="update extra_field set site_id={$siteID} where field_name='{$record["field_name"]}' 
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
        $objSQL=new ClsNaanalSQL();
        $objSQL->addTable("auieo_fields");
        $objSQL->addWhereNew("auieo_fields","data_item_type", $this->_dataItemType);
        $objSQL->addWhereNew("auieo_fields","field_name", $field);
        $objSQL->addWhereNew("auieo_fields","site_id", $this->_siteID);
        $sql=$objSQL->render();
        $row=$this->_db->getAssoc($sql);
        if(empty($row)) return false;
        /* Delete old entries. */
        $sql = sprintf(
            "DELETE FROM
                extra_field
            WHERE
                extra_field.field_name = %s
            AND
                extra_field.data_item_id = %s
            AND
                extra_field.site_id = %s
            AND
                extra_field.data_item_type = %s",
            $this->_db->makeQueryString($field),
            $this->_db->makeQueryInteger($moduleID),
            $this->_siteID,
            $this->_dataItemType
        );
        $this->_db->query($sql);

        /* Don't set empty values at all. 0 is okay. */
        if (empty($value) && $value !== 0 && $value !== '0')
        {
            return false;
        }

        $sql = sprintf(
            "INSERT INTO extra_field (
                data_item_id,
                field_name,
                value,
                import_id,
                site_id,
                data_item_type
            )
            VALUES (
                %s,
                %s,
                %s,
                0,
                %s,
                %s
            )",
            $this->_db->makeQueryInteger($moduleID),
            $this->_db->makeQueryString($field),
            $this->_db->makeQueryString($value),
            $this->_siteID,
            $this->_dataItemType
        );

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
        $sql = sprintf(
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

        return (boolean) $this->_db->query($sql);
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
               return array('select'       => 'extra_field'.$uniqueIndex.'.value AS extra_field_value'.$uniqueIndex,
                          'join'         => 'LEFT JOIN extra_field AS extra_field' . $uniqueIndex . ' '.
                                            'ON '.$column.' = extra_field' . $uniqueIndex . '.data_item_id '.
                                            'AND extra_field' . $uniqueIndex . '.field_name = ' . $db->makeQueryString($data['fieldName']) . ' '.
                                            'AND extra_field' . $uniqueIndex . '.data_item_type = ' . $this->_dataItemType,
                          'pagerRender'          => 'return ($rsData[\'extra_field_value' . $uniqueIndex . '\'] == \'Yes\' ? \'Yes\' : \'No\');',
                          'exportRender'          => 'return ($rsData[\'extra_field_value' . $uniqueIndex . '\'] == \'Yes\' ? \'Yes\' : \'No\');',
                          'sortableColumn'         => 'extra_field_value' . $uniqueIndex,
                          'pagerWidth'  => 45,
                          'filter' => 'IF (extra_field'.$uniqueIndex.'.value = "Yes", "Yes", "No")');
            break;
            
            case EXTRA_FIELD_DATE:
                return array('select'  => 'extra_field'.$uniqueIndex.'.value AS extra_field_value'.$uniqueIndex,
                          'join'    => 'LEFT JOIN extra_field AS extra_field' . $uniqueIndex . ' '.
                                       'ON '.$column.' = extra_field' . $uniqueIndex . '.data_item_id '.
                                       'AND extra_field' . $uniqueIndex . '.field_name = ' . $db->makeQueryString($data['fieldName']) . ' '.
                                       'AND extra_field' . $uniqueIndex . '.data_item_type = ' . $this->_dataItemType,
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
                          'sortableColumn'       => 'extra_field_value' . $uniqueIndex,
                          'pagerWidth' => 110,
                          'filter' => 'extra_field'.$uniqueIndex.'.value');
            
            case EXTRA_FIELD_TEXT:
            default:
                return array('select'  => 'extra_field'.$uniqueIndex.'.value AS extra_field_value'.$uniqueIndex,
                          'join'    => 'LEFT JOIN extra_field AS extra_field' . $uniqueIndex . ' '.
                                       'ON '.$column.' = extra_field' . $uniqueIndex . '.data_item_id '.
                                       'AND extra_field' . $uniqueIndex . '.field_name = ' . $db->makeQueryString($data['fieldName']) . ' '.
                                       'AND extra_field' . $uniqueIndex . '.data_item_type = ' . $this->_dataItemType,
                          'pagerRender'     => 'return htmlspecialchars($rsData[\'extra_field_value' . $uniqueIndex . '\']);',
                          'sortableColumn'    => 'extra_field_value' . $uniqueIndex,
                          'pagerWidth'   => 110,
                          'filter' => 'extra_field'.$uniqueIndex.'.value',
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
            
            foreach ($extraFieldRS as $index2 => $data2)
            {
                if ($data2['fieldName'] == $data['fieldName'])
                {
                    $extraFieldSettingsRS[$index]['value'] = $data2['value'];
                }
            }        
        }

        return $extraFieldSettingsRS;
    }
    

}
