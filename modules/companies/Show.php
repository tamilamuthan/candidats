<?php 
/* 
 * CandidATS
 * Companies
 *
 * Copyright (C) 2014 - 2015 Auieo Software Private Limited, Parent Company of Unicomtech.
 * 
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

pageHeaderInclude('js/sorttable.js');
pageHeaderInclude('js/attachment.js');
pageHeaderInclude('js/xeditable/js/xeditable.js');
pageTitle('Company - '.$this->data['name']);
$arrModuleInfo=getModuleInfo("modulename");
$moduleInfo=$arrModuleInfo[$_REQUEST["m"]];
$objRole=Users::getInstance()->getRole();
$allowDelete=$objRole->getModulePermission(200, Companies::actionMapping("delete"));
$allowEdit=$objRole->getModulePermission(200, Companies::actionMapping("edit"));
$AUIEO_CONTENT="";

ob_start();
TemplateUtility::printSingleQuickActionMenu(DATA_ITEM_COMPANY, $this->companyID);
$other=ob_get_clean();

$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Name","class"=>$this->data['titleClass'],"data"=>$this->data['name'],"public"=>false,"other"=>$other);
$data="<a href='index.php?m=contacts&a=show&contactID={$this->data['billingContact']}'>{$this->data['billingContactFullName']}</a>";
$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Billing Contact","class"=>"previewtitle","data"=>$data,"public"=>false,"other"=>false);

$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Primary Phone","class"=>"previewtitle","data"=>$this->data['phone1'],"public"=>false,"other"=>false,'key'=>'phone1','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=phone1&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['phone1']}");
$data=" <a href='{$this->data['url']}' target='_blank'>{$this->data['url']}</a>";
$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Web Site","class"=>"previewtitle","data"=>$data,"public"=>false,"other"=>false,'key'=>'url','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=url&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['url']}");

$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Secondary Phone","class"=>"previewtitle","data"=>$this->data['phone2'],"public"=>false,"other"=>false,'key'=>'phone2','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=phone2&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['phone2']}");
$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Key Technologies","class"=>"previewtitle","data"=>$this->data['key_technologies'],"public"=>false,"other"=>false,'key'=>'key_technologies','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=key_technologies&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['key_technologies']}");

$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Fax Number","class"=>"previewtitle","data"=>$this->data['fax_number'],"public"=>false,"other"=>false,'key'=>'fax_number','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=fax_number&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['fax_number']}");
$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Created","class"=>"previewtitle","data"=>$this->data['dateCreated']."({$this->data['enteredByFullName']})","public"=>false,"other"=>false);

$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Address","class"=>"previewtitle","data"=>nl2br(htmlspecialchars($this->data['address']))." 
    ".$this->data['googleMaps']."
        <br />{$this->data['cityAndState']}
        <br />{$this->data['zip']}","public"=>false,"other"=>false);
$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Owner","class"=>"previewtitle","data"=>$this->data['ownerFullName'],"public"=>false,"other"=>false);

$jsonRender=array();
foreach($this->data as $k=>$v)
{
    $jsonRender[$k]=$v;
}

$extraFieldData=array();
for ($i = 0; $i < count($this->extraFieldRS); $i++)
{
    $jsonRender["extra".$this->extraFieldRS[$i]["extraFieldSettingsID"]]=$this->extraFieldRS[$i]['display'];
    if($this->extraFieldRS[$i]["extraFieldType"]==8 || $this->extraFieldRS[$i]["extraFieldType"]<=4)
        $AUIEO_PREVIEW_FIELD[]=array("caption"=>$this->extraFieldRS[$i]['fieldName'],"class"=>"previewtitle","data"=>$this->extraFieldRS[$i]['display'],"public"=>false,"other"=>false,'key'=>"extra".$this->extraFieldRS[$i]["extraFieldSettingsID"],'sql'=>"index.php?m=joborders&a=updateFieldData&field={$this->extraFieldRS[$i]['fieldName']}&joborder_id={$this->data["joborder_id"]}&data={$this->extraFieldRS[$i]['display']}");
    else
        $AUIEO_PREVIEW_FIELD[]=array("caption"=>$this->extraFieldRS[$i]['fieldName'],"class"=>"previewtitle","data"=>$this->extraFieldRS[$i]['display'],"public"=>false,"other"=>false);
}
$AUIEO_JSON=  json_encode($jsonRender);
ob_start();
echo "<table class='detailsOutside' width='100%'>
                    <tr>
                        <td><table class='detailsInside'>";
            displayMultiColumnTable($AUIEO_PREVIEW_FIELD);
            echo "</table></td></tr></table>";
?>

            <!-- CONTACT INFO -->

            <?php if (count($this->departmentsRS) > 0): ?>

                <table class="detailsOutside" width="100%">

                    <tr>

                        <td>

                            <table class="detailsInside">

                                <tr>

                                    <td valign="top" class="vertical">Departments:</td>

                                    <td valign="top" class="data">

                                        <?php foreach ($this->departmentsRS as $departmentRecord){ ?>

                                            <?php $this->_($departmentRecord['name']); ?>

                                            <br />

                                        <?php } ?>

                                    </td>

                                </tr>

                            </table>

                        </td>

                    </tr>

                </table>

            <?php endif; ?>

            <!-- /CONTACT INFO -->



            <!-- CONTACT INFO -->

            <table class="detailsOutside" width="100%">

                <tr>

                    <td>

                        <table class="detailsInside">

                            <tr>

                                <td valign="top" class="vertical">Attachments:</td>

                                <td valign="top" class="data">

                                    <table class="attachmentsTable">

                                        <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData){ ?>

                                            <tr>

                                                <td>

                                                    <?php echo $attachmentsData['retrievalLink']; ?>

                                                        <img src="<?php $this->_($attachmentsData['attachmentIcon']) ?>" alt="" width="16" height="16" border="0" />

                                                        &nbsp;

                                                        <?php $this->_($attachmentsData['originalFilename']) ?>

                                                    </a>

                                                </td>

                                                <td><?php $this->_($attachmentsData['dateCreated']) ?></td>

                                                <td>

                                                    <?php if ($this->accessLevel >= ACCESS_LEVEL_DELETE): ?>

                                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&a=deleteAttachment&companyID=<?php echo($this->companyID); ?>&attachmentID=<?php $this->_($attachmentsData['attachmentID']) ?>"  title="Delete" onclick="javascript:return confirm('Delete this attachment?');">

                                                            <img src="images/actions/delete.gif" alt="" width="16" height="16" border="0" />

                                                        </a>

                                                    <?php endif; ?>

                                                </td>

                                            </tr>

                                        <?php } ?>

                                    </table>

                                    <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                                            <?php if (isset($this->attachmentLinkHTML)): ?>

                                                <?php echo($this->attachmentLinkHTML); ?>

                                            <?php else: ?>

                                                <a href="#" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=companies&a=createAttachment&companyID=<?php echo($this->companyID); ?>', 400, 125, null); return false;">

                                            <?php endif; ?>

                                            <img src="images/paperclip_add.gif" width="16" height="16" border="0" alt="add attachment" class="absmiddle" />&nbsp;Add Attachment

                                        </a>

                                    <?php endif; ?>

                                </td>

                            </tr>

                            <tr>

                                <td valign="top" class="vertical">Misc. Notes:</td>

                                <?php if ($this->isShortNotes): ?>

                                    <td id="shortNotes" style="display:block;" class="data">

                                        <?php echo($this->data['shortNotes']); ?><span class="moreText">...</span>&nbsp;

                                        <p><a href="#" class="moreText" onclick="toggleNotes(); return false;">[More]</a></p>

                                    </td>

                                    <td id="fullNotes" style="display:none;" class="data">

                                        <?php echo($this->data['notes']); ?>&nbsp;

                                        <p><a href="#" class="moreText" onclick="toggleNotes(); return false;">[Less]</a></p>

                                    </td>

                                <?php else: ?>

                                    <td id="shortNotes" style="display:block;" class="data">

                                        <?php echo($this->data['notes']); ?>

                                    </td>

                                <?php endif; ?>

                            </tr>
                        </table>

                    </td>

                </tr>

            </table>

            <!-- /CONTACT INFO -->



            <?php if ($allowEdit && $this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                <a id="edit_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&a=edit&companyID=<?php echo($this->companyID); ?>">

                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />&nbsp;Edit

                </a>

                &nbsp;&nbsp;&nbsp;&nbsp;

            <?php endif; ?>

            <?php if ($allowDelete && $this->accessLevel >= ACCESS_LEVEL_DELETE && $this->data['default_company'] != 1): ?>

                <a id="delete_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&a=delete&companyID=<?php echo($this->companyID); ?>" onclick="javascript:return confirm('Delete this company?');">

                    <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;Delete

                </a>

                &nbsp;&nbsp;&nbsp;&nbsp;

            <?php endif;

            if ($this->privledgedUser)

            { 

?>

                <a id="history_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=viewItemHistory&dataItemType=200&dataItemID=<?php echo($this->companyID); ?>">

                    <img src="images/icon_clock.gif" width="16" height="16" class="absmiddle"  border="0" />&nbsp;View History

                </a>

                &nbsp;&nbsp;&nbsp;&nbsp;

<?php

                if($this->companyID>1)

                {

?>

                <a id="history_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=transfer&dataItemType=200&dataItemID=<?php echo($this->companyID); ?>">

                    Transfer

                </a>&nbsp;&nbsp;&nbsp;&nbsp;

<?php

                }

            }

?>

            <br clear="all" />

            <br />



            <p class="note">Job Orders</p>

            <table class="sortable" width="100%">

                <tr>

                    <th align="left" width="30" nowrap="nowrap">ID</th>

                    <th align="left" width="200">Title</th>

                    <th align="left" width="15">Type</th>

                    <th align="left" width="15">Status</th>

                    <th align="left" width="60">Created</th>

                    <th align="left" width="60">Modified</th>

                    <th align="left" width="60">Start</th>

                    <th align="left" width="15">Age</th>

                    <th align="left" width="10">S</th>

                    <th align="left" width="10">P</th>

                    <th align="left" width="65">Recruiter</th>

                    <th align="left" width="68">Owner</th>

                    <th align="left" width="25">Action</th>

                </tr>



                <?php foreach ($this->jobOrdersRS as $rowNumber => $jobOrdersData){ ?>

                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['jobOrderID']) ?></td>

                        <td valign="top">

                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&a=show&jobOrderID=<?php $this->_($jobOrdersData['jobOrderID']) ?>">

                                <?php $this->_($jobOrdersData['title']) ?>

                            </a>

                        </td>

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['type']) ?></td>

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['status']) ?></td>

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['dateCreated']) ?></td>

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['dateModified']) ?></td>

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['startDate']) ?></td>

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['daysOld']) ?></td>

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['submitted']); ?></td>

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['pipeline']); ?></td>

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['recruiterAbbrName']); ?></td>

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['ownerAbbrName']); ?></td>

                        <td valign="top" align="center">

                            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&a=edit&jobOrderID=<?php $this->_($jobOrdersData['jobOrderID']) ?>">

                                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />

                                </a>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php } ?>

            </table>



            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&a=add&selected_company_id=<?php echo($this->companyID); ?>" title="Add Job Order">

                    <img src="images/actions/job_order.gif" width="16" height="16" class="absmiddle" alt="New Job Order" border="0" />&nbsp;Add Job Order

                </a>

            <?php endif; ?>

            <br clear="all" />

            <br />



            <!-- CONTACT INFO -->

            <p class="note">Contacts</p>

            <table class="sortable" width="100%">

                <tr>

                    <th align="left" nowrap="nowrap">First Name</th>

                    <th align="left" nowrap="nowrap">Last Name</th>

                    <th align="left">Title</th>

                    <th align="left">Department</th>

                    <th align="left" nowrap="nowrap">Work Phone</th>

                    <th align="left" nowrap="nowrap">Cell Phone</th>

                    <th align="left">Created</th>

                    <th align="left">Owner</th>

                    <th align="center">Action</th>

                </tr>



                <?php if (count($this->contactsRSWC) != 0): ?>

                 <?php foreach ($this->contactsRSWC as $rowNumber => $contactsData){ ?>

                    <tr id="ContactsDefault<?php echo($rowNumber) ?>" class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">

                        <td valign="top" align="left">

                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&a=show&contactID=<?php $this->_($contactsData['contactID']) ?>" class="<?php $this->_($contactsData['linkClass']); ?>">

                                <?php $this->_($contactsData['firstName']) ?>

                            </a>

                        </td>

                        <td valign="top" align="left">

                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&a=show&contactID=<?php $this->_($contactsData['contactID']) ?>" class="<?php $this->_($contactsData['linkClass']); ?>">

                                <?php $this->_($contactsData['lastName']) ?>

                            </a>

                        </td>

                        <td valign="top" align="left"><?php $this->_($contactsData['title']) ?></td>

                        <td valign="top" align="left"><?php $this->_($contactsData['department']) ?></td>

                        <td valign="top" align="left"><?php $this->_($contactsData['phoneWork']) ?></td>

                        <td valign="top" align="left"><?php $this->_($contactsData['phoneCell']) ?></td>

                        <td valign="top" align="left"><?php $this->_($contactsData['dateCreated']) ?></td>

                        <td valign="top" align="left"><?php $this->_($contactsData['ownerAbbrName']); ?></td>

                        <td valign="top" align="center">

                            <?php if (!empty($contactsData['email1'])): ?>

                                <a href="mailto:<?php $this->_($contactsData['email1']); ?>" title="Send E-Mail (<?php $this->_($contactsData['email1']); ?>)">

                                    <img src="images/actions/email.gif" width="16" height="16" alt="" class="absmiddle" border="0" />

                                </a>

                            <?php else: ?>

                                <img src="images/actions/email_no.gif" title="No E-Mail Address" width="16" height="16" alt="" class="absmiddle" border="0" />

                            <?php endif; ?>

                            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&a=edit&contactID=<?php $this->_($contactsData['contactID']) ?>">

                                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />

                                </a>

                            <?php endif; ?>

                        </td>

                    </tr>

                 <?php } ?>

               <?php endif; ?>



                <?php /* The following are hidden by default */ ?>

                <?php if (count($this->contactsRSWC) != count($this->contactsRS) && count($this->contactsRS) != 0) : ?>

                 <?php foreach ($this->contactsRS as $rowNumber => $contactsData){ ?>

                    <tr id="ContactsFull<?php echo($rowNumber) ?>" class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>" style="display:none;">

                        <td valign="top" align="left">

                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&a=show&contactID=<?php $this->_($contactsData['contactID']) ?>" class="<?php $this->_($contactsData['linkClass']); ?>">

                                <?php $this->_($contactsData['firstName']) ?>

                            </a>

                        </td>

                        <td valign="top" align="left">

                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&a=show&contactID=<?php $this->_($contactsData['contactID']) ?>" class="<?php $this->_($contactsData['linkClass']); ?>">

                                <?php $this->_($contactsData['lastName']) ?>

                            </a>

                        </td>

                        <td valign="top" align="left"><?php $this->_($contactsData['title']) ?></td>

                        <td valign="top" align="left"><?php $this->_($contactsData['department']) ?></td>

                        <td valign="top" align="left"><?php $this->_($contactsData['phoneWork']) ?></td>

                        <td valign="top" align="left"><?php $this->_($contactsData['phoneCell']) ?></td>

                        <td valign="top" align="left"><?php $this->_($contactsData['dateCreated']) ?></td>

                        <td valign="top" align="left"><?php $this->_($contactsData['ownerAbbrName']); ?></td>

                        <td valign="top" align="center">

                            <?php if (!empty($contactsData['email1'])): ?>

                                <a href="mailto:<?php $this->_($contactsData['email1']); ?>">

                                    <img src="images/actions/email.gif" width="16" height="16" alt="" class="absmiddle" border="0" title="Send E-Mail (<?php $this->_($contactsData['email1']); ?>)"/>

                                </a>

                            <?php else: ?>

                                <img src="images/actions/email_no.gif" title="No E-Mail Address" width="16" height="16" alt="" class="absmiddle" border="0" />

                            <?php endif; ?>

                            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&a=edit&contactID=<?php $this->_($contactsData['contactID']) ?>">

                                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />

                                </a>

                            <?php endif; ?>

                        </td>

                    </tr>

                 <?php } ?>

                <?php endif; ?>



            </table>



            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&a=add&selected_company_id=<?php echo($this->companyID); ?>" title="Add Contact">

                    <img src="images/actions/add_contact.gif" width="16" height="16" class="absmiddle" alt="add contact" border="0" title="Add Contact"/>&nbsp;Add Contact

                </a>

            <?php endif; ?>

            <?php if (count($this->contactsRSWC) != count($this->contactsRS)) : ?>

                &nbsp;

                <a href="javascript:void(0)" id="linkShowAll" onclick="javascript:for (i = 0; i< <?php echo(count($this->contactsRSWC)); ?>; i++) document.getElementById('ContactsDefault'+i).style.display='none'; for (i = 0; i< <?php echo(count($this->contactsRS)); ?>; i++) document.getElementById('ContactsFull'+i).style.display=''; document.getElementById('linkShowAll').style.display='none'; document.getElementById('linkHideSome').style.display='';">

                    <img src="images/actions/add_contact.gif" width="16" height="16" class="absmiddle" alt="add contact" border="0" title="Show All"/>

                    &nbsp;Show contacts who have left (<?php echo(count($this->contactsRS) - count($this->contactsRSWC)); ?>)

                </a>

                <a href="javascript:void(0)" id="linkHideSome" style="display:none;" onclick="javascript:for (i = 0; i< <?php echo(count($this->contactsRSWC)); ?>; i++) document.getElementById('ContactsDefault'+i).style.display=''; for (i = 0; i< <?php echo(count($this->contactsRS)); ?>; i++) document.getElementById('ContactsFull'+i).style.display='none'; document.getElementById('linkShowAll').style.display=''; document.getElementById('linkHideSome').style.display='none';">

                    <img src="images/actions/add_contact.gif" width="16" height="16" class="absmiddle" alt="add contact" border="0" title="Hide Some"/>

                    &nbsp;Hide contacts who have left (<?php echo(count($this->contactsRS) - count($this->contactsRSWC)); ?>)

                </a>

            <?php endif; ?>

            <!-- /CONTACT INFO -->

<?php
$this->subTemplate(dirname(__FILE__)."/AssignTagModal.php","AUIEO_TAG_UL");
 $AUIEO_CONTENT=ob_get_clean();

?>

