<?php
/**
 * CATS
 * Data Export Library
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
 * @version    $Id: Export.php 3813 2007-12-05 23:16:22Z brian $
 */
 
include_once('./lib/Candidates.php');
include_once('./lib/Contacts.php');
include_once('./lib/Companies.php');
include_once('./lib/JobOrders.php');

/**
 *	Data Export Utility Library
 *	@package    CATS
 *	@subpackage Library
 */
class ExportUtility
{
    /**
     * Generates HTML code for export forms / menus.
     *
     * @param flag data item type being exported
     * @param string comma-separated list of data item IDs
     * @param integer number of pixles right the export box should be displayed
     * @return array containing parts of html code for the export form.
     */
    public static function getForm($dataItemType, $IDs, $popUpOffset = 35, $linkOffset = 5)
    {
        $indexName = CATSUtility::getIndexName();

        /* Build form header. */
        $header = '<form name="selectedObjects" action="'
            . $indexName . '" method="get">' . "\n"
            . '<input type="hidden" name="m" value="export" />' . "\n"
            . '<input type="hidden" name="onlySelected" value="true" />' . "\n"
            . '<input type="hidden" name="dataItemType" value="'. $dataItemType . '" />' . "\n";

        /* Build form menu. */
        $allRecordsURL = sprintf(
            '%s?m=export&amp;a=export&amp;dataItemType=%s',
            $indexName,
            $dataItemType
        );
        if($_REQUEST["m"]=="candidates")
        {
            $objSearchDS=new SearchDataStructure($_REQUEST["m"]);
            $objSearchDS->loadFromURL();
            $_SESSION["AUIEO"]["CANDIDATS"]["SearchDS"]=$objSearchDS;
            $allRecordsJoborderURL = sprintf(
                '%s?m=candidates&a=considerForJobSearch&candidateID=SearchDS',
                $indexName
            );
        }
        $currentPageURL = sprintf(
            '%s?m=export&amp;a=export&amp;dataItemType=%s&amp;ids=%s',
            $indexName,
            $dataItemType,
            $IDs
        );
        if($_REQUEST["m"]=="candidates")
        {
            $arrID=explode(",",$IDs);
            foreach($arrID as $ind=>$id)
            {
                $arrID[$ind]="candidateID[]=".trim($id);
            }
            $candidateIDs=implode("&",$arrID);
            $currentPageJoborderURL = sprintf(
                '%s?m=candidates&a=considerForJobSearch&%s',
                $indexName,
                $candidateIDs
            );
        }
        $link="";
        $menu =
              '<div style="float: left; margin-left: 4px; margin-right: ' . $linkOffset . 'px;">'
            . '<form name="selectAll" action="#">'
            . '<input type="checkbox" name="allBox" title="Select All" onclick="toggleChecksAll();" />'
            . '</form>'
            . '</div>'
            . '<a href="#" id="exportBoxLink" onclick="showBox(\'ExportBox\'); return false;">Export</a> | <a href="#" id="deleteBoxLink" onclick="deleteSelected(); return false;">Delete</a>'.$link;
        if($_REQUEST["m"]=="candidates")
        {
            $menu = $menu . ' | <a href="#" id="addToJoborderBoxLink" onclick="showBox(\'JoborderBox\'); return false;">Add to joborder</a>';
        }
        $menu = $menu . '<br />'
            . '<div class="exportPopup" id="ExportBox" align="left" onmouseover="showBox(\'ExportBox\');" onmouseout="hideBox(\'ExportBox\');">'
            . '<a href="' . $allRecordsURL . '">Export All Records</a><br />'
            . '<a href="' . $currentPageURL . '">Export Current Page</a><br />'
            . '<a href="#" onclick="checkSelected(); return false;">Export Selected Records</a><br />'
            . '</div>';
        if($_REQUEST["m"]=="candidates")
        {
            $menu=$menu.'
                    <div class="exportPopup" id="JoborderBox" align="left" onmouseover="showBox(\'JoborderBox\');" onmouseout="hideBox(\'JoborderBox\');">'
                . '<a href="javascript:void(0);" onclick="showPopWin(\''.$allRecordsJoborderURL.'\', 750, 390, null); return false;">All Records</a><br />'
                . '<a href="javascript:void(0);" onclick="showPopWin(\''.$allRecordsJoborderURL.'\', 750, 390, null); return false;">Current Page</a><br />'
                . '<a href="#" onclick="checkSelected(); return false;">Selected Records</a>'
                . '</div>';
        }
        $footer = '</form>';

        return array(
            'header' => $header,
            'footer' => $footer,
            'menu'   => $menu
        );
    }
}


/**
 *	Data Export Library
 *	@package    CATS
 *	@subpackage Library
 */
class Export
{
    private $_siteID;
    private $_dataItemType;
    private $_separator;
    private $_rs;
    private $_IDs;


    public function __construct($dataItemType, $IDs, $separator, $siteID)
    {
        $this->_siteID = $siteID;
        $this->_dataItemType = $dataItemType;
        $this->_separator = $separator;
        $this->_IDs = $IDs;
    }


    /**
     * Creates and returns output to be written to a CSV / etc. file.
     *
     * @return string formatted output
     */
    public function getFormattedOutput()
    {
        switch ($this->_dataItemType)
        {
            case DATA_ITEM_CANDIDATE:
                $dataItem = new Candidates($this->_siteID);
                break;
            
            case DATA_ITEM_JOBORDER:
                $dataItem = new JobOrders($this->_siteID);
                break;
           
             case DATA_ITEM_COMPANY:
                $dataItem = new Companies($this->_siteID);
                break;
            
            default:
                return false;
                break;
        }

        $this->_rs = $dataItem->getExport($this->_IDs);
        if (empty($this->_rs))
        {
            return false;
        }

        /* Column names. */
        $outputString = implode(
            $this->_separator, array_keys($this->_rs[0])
        ) . "\r\n";

        foreach ($this->_rs as $rowIndex => $row)
        {
            foreach ($row as $key => $value)
            {
                /* Escape any double-quotes and place the value inside
                 * double quotes.
                 */
                $this->_rs[$rowIndex][$key] = '"' . str_replace('"', '""', $value) . '"';
            }

            $outputString .= implode(
                $this->_separator, $this->_rs[$rowIndex]
            ) . "\r\n";
        }

        return $outputString;
    }
}

?>
