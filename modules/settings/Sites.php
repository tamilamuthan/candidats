<?php 
/* 
 * CandidATS
 * Sites Management
 *
 * Copyright (C) 2014 - 2015 Auieo Software Private Limited, Parent Company of Unicomtech.
 * 
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

ob_start();
TemplateUtility::printHeader('Settings', 'js/sorttable.js');
$AUIEO_HEADER=  ob_get_clean();

$AUIEO_CONTENT="";
ob_start();
if (!empty($this->rs))
{
    foreach ($this->rs as $rowNumber => $data)
    { ?>
                <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                    <td valign="top" align="left">
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=showSite&siteID=<?php $this->_($data['siteID']); ?>">
                            <?php $this->_($data['name']); ?>
                        </a>
                    </td>
                    <td valign="top" align="left">
                        
                            <?php echo $data['isDemo']>0?"Yes":"No"; ?>
                    </td>
                </tr>
<?php 
    }
$AUIEO_CONTENT=  ob_get_clean();
} 
?>
        