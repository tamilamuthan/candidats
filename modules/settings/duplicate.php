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

ob_start();
TemplateUtility::printHeader('Settings', 'js/sorttable.js');
$AUIEO_HEADER=  ob_get_clean();
ob_start();
TemplateUtility::printTabs($this->active, $this->subActive); 
$tabs=  ob_get_clean();
$siteID=$_SESSION["CATS"]->getSiteID();
/* Bail out if dataItemType not set. */
if(!isset($_REQUEST['dataItemType']))
{
    CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Required input missing.');
    return;
}
$dataItemType = $_REQUEST['dataItemType'];
$dataItemID   = $_REQUEST['dataItemID'];
$AUIEO_MODULE = "";
$AUIEO_NAME = "";
switch ($dataItemType)
{
    case DATA_ITEM_CANDIDATE:
        include_once('./lib/Candidates.php');
        $dataItem = new Candidates($siteID);
        $AUIEO_MODULE = "candidates";
        $record=$dataItem->get($dataItemID);
        /* Bail out if record not found. */
        if(empty($record))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid data item ID for the Site '.$siteID);
            return;
        }
        $AUIEO_NAME = $record["firstName"]." ".$record["lastName"];
        break;

    case DATA_ITEM_COMPANY:
        include_once('./lib/Companies.php');
        $dataItem = new Companies($siteID);
        $AUIEO_MODULE = "companies";
        $record=$dataItem->get($dataItemID);
        /* Bail out if record not found. */
        if(empty($record))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid data item ID for the Site '.$siteID);
            return;
        }
        $AUIEO_NAME = $record["name"];
        break;

    case DATA_ITEM_CONTACT:
        include_once('./lib/Contacts.php');
        $dataItem = new Contacts($siteID);
        $AUIEO_MODULE = "contacts";
        $record=$dataItem->get($dataItemID);
        /* Bail out if record not found. */
        if(empty($record))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid data item ID for the Site '.$siteID);
            return;
        }
        $AUIEO_NAME = $record["firstName"]." ".$record["lastName"];
        break;

    default:
        /* Bail out if record not found. */
        if(empty($record))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid data item type.');
            return;
        }
}
ob_start();
if (!empty($this->rs))
{
    foreach ($this->rs as $rowNumber => $data)
    { ?>
    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
        <td valign="top" align="left">
                <?php $this->_($data['name']); ?>
        </td>
        <td valign="top" align="left">
            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=<?php echo $AUIEO_MODULE; ?>&a=copyto&dataItemType=<?php echo $dataItemType; ?>&dataItemID=<?php echo $dataItemID; ?>&siteID=<?php $this->_($data['siteID']); ?>">
                <?php echo "Copy"; ?>
            </a>
        </td>
    </tr>
<?php
    }
}
$AUIEO_CONTENT=ob_get_clean();
?>