<?php 
/* 
 * CandidATS
 * Show
 *
 * Copyright (C) 2014 - 2015 Auieo Software Private Limited, Parent Company of Unicomtech.
 * 
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

pageHeaderInclude('js/activity.js');
pageHeaderInclude('js/sorttable.js');
pageHeaderInclude('js/match.js');
pageHeaderInclude('js/lib.js');
pageHeaderInclude('js/pipeline.js');
pageHeaderInclude('js/attachment.js');
pageHeaderInclude('js/xeditable/js/xeditable.js');
pageTitle('Candidate - '.$this->data['first_name'].' '.$this->data['last_name']);
$arrModuleInfo=getModuleInfo("modulename");
$moduleInfo=$arrModuleInfo[$_REQUEST["m"]];
ob_start();
$this->subTemplate(dirname(__FILE__)."/AssignTagModal.php","AUIEO_TAG_UL");
$AUIEO_HEADER=  ob_get_clean();
$AUIEO_CONTENT="";
ob_start();
if ($this->data['is_admin_hidden'] == 1)
{
    ?>
    <p class="warning">This Candidate is hidden.  Only CATS Administrators can view it or search for it.  To make it visible by the site users, click <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&a=administrativeHideShow&candidateID=<?php echo($this->candidateID); ?>&state=0" style="font-weight:bold;">Here.</a></p>
<?php 
}
$profileImage = false;
foreach ($this->attachmentsRS as $rowNumber => $attachmentsData)
{
    if ($attachmentsData['isProfileImage'] == '1')
    {
         $profileImage = "<a href='{$attachmentsData['retrievalURL']}'><img height='75' border='0' alt='' src='{$attachmentsData['retrievalURLLocal']}' /></a>";
    }
}
$AUIEO_PREVIEW_FIELD=array();
if($profileImage)
{
    $delete="";
     if ($this->accessLevel >= ACCESS_LEVEL_DELETE)
     {
         $delete="<a onclick=\"javascript:return confirm('Delete this attachment?');\" href='index.php?m=candidates&amp;a=deleteAttachment&amp;candidateID=2290&amp;attachmentID=2144'>
<img width='16' height='16' border='0' title='Delete' alt='' src='images/actions/delete.gif'>
</a> ";
     }
    $data="<img height='100' border='0' alt='' src='' />";
    $AUIEO_PREVIEW_FIELD[]=array("caption"=>"{$delete}Picture","class"=>$this->data['titleClass'],"data"=>$profileImage,"public"=>false,"other"=>false);
    $AUIEO_PREVIEW_FIELD[]=array("caption"=>"","class"=>"previewtitle","data"=>"","public"=>false,"other"=>false);
}
$data="{$this->data['first_name']} {$this->data['middle_name']} {$this->data['last_name']}";
if ($this->data['is_active'] != 1)
{ 
    $data.= "&nbsp;<span style='color:orange;'>(INACTIVE)</span>";
}
ob_start();
TemplateUtility::printSingleQuickActionMenu(DATA_ITEM_CANDIDATE, $this->data['candidate_id']);
$other=ob_get_clean();

            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Name","class"=>$this->data['titleClass'],"data"=>$data,"public"=>false,"other"=>$other);
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Date Available","class"=>"previewtitle","data"=>$this->data['dateAvailable'],"public"=>false,"other"=>false);
            
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"E-Mail","class"=>"previewtitle","data"=>$this->data['email1'],"public"=>false,"other"=>false,'key'=>'email1','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=email1&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['email1']}");
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Current Employer","class"=>"previewtitle","data"=>$this->data['current_employer'],"public"=>false,"other"=>false);
            
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"2nd E-Mail","class"=>"previewtitle","data"=>$this->data['email2'],"public"=>false,"other"=>false,'key'=>'email2','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=email2&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['email2']}");
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Key Skills","class"=>"previewtitle","data"=>$this->data['key_skills'],"public"=>false,"other"=>false,'key'=>'key_skills','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=key_skills&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['key_skills']}");
            
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Home Phone","class"=>"previewtitle","data"=>$this->data['phone_home'],"public"=>false,"other"=>false,'key'=>'phone_home','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=phone_home&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['phone_home']}");
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Can Relocate","class"=>"previewtitle","data"=>$this->data['can_relocate'],"public"=>false,"other"=>false);
            
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Cell Phone","class"=>"previewtitle","data"=>$this->data['phone_cell'],"public"=>false,"other"=>false,'key'=>'phone_cell','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=phone_cell&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['phone_cell']}");
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Current Pay","class"=>"previewtitle","data"=>$this->data['current_pay'],"public"=>false,"other"=>false,'key'=>'current_pay','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=current_pay&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['current_pay']}");
            
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Work Phone","class"=>"previewtitle","data"=>$this->data['phone_work'],"public"=>false,"other"=>false,'key'=>'phone_work','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=phone_work&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['phone_work']}");
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Desired Pay","class"=>"previewtitle","data"=>$this->data['desired_pay'],"public"=>false,"other"=>false,'key'=>'desired_pay','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=desired_pay&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['desired_pay']}");
            
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Best Time To Call","class"=>"previewtitle","data"=>$this->data['best_time_to_call'],"public"=>false,"other"=>false,'key'=>'best_time_to_call','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=best_time_to_call&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['best_time_to_call']}");            
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Pipeline","class"=>"previewtitle","data"=>$this->data['pipeline'],"public"=>false,"other"=>false);
            
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Address","class"=>"previewtitle","data"=>"{$this->data['cityAndState']}<br />".$this->data['cityAndState']." ".$this->data['zip'],"public"=>false,"other"=>false);
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Submitted","class"=>"previewtitle","data"=>$this->data['submitted'],"public"=>false,"other"=>false);
            
            $data=""; 
            if (!empty($this->data['webSite']))
            {
                $data= "<a href='{$this->data['webSite']}' target='_blank'>{$this->data['webSite']}</a>";
             } 
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Web Site","class"=>"previewtitle","data"=>$data,"public"=>false,"other"=>false);
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Created","class"=>"previewtitle","data"=>"{$this->data['dateCreated']}({$this->data['enteredByFullName']})","public"=>false,"other"=>false);
            
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Source","class"=>"previewtitle","data"=>$this->data['source'],"public"=>false,"other"=>false);
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Owner","class"=>"previewtitle","data"=>$this->data['ownerFullName'],"public"=>false,"other"=>false);
        
            for ($i = 0; $i < count($this->extraFieldRS); $i++)
            {
                $AUIEO_PREVIEW_FIELD[]=array("caption"=>$this->extraFieldRS[$i]['fieldName'],"class"=>"previewtitle","data"=>$this->extraFieldRS[$i]['display'],"public"=>false,"other"=>false);
            }
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
        $AUIEO_PREVIEW_FIELD[]=array("caption"=>$this->extraFieldRS[$i]['fieldName'],"class"=>"previewtitle","data"=>$this->extraFieldRS[$i]['display'],"public"=>false,"other"=>false,'key'=>"extra".$this->extraFieldRS[$i]["extraFieldSettingsID"],'sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field={$this->extraFieldRS[$i]['fieldName']}&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->extraFieldRS[$i]['display']}");
    else
        $AUIEO_PREVIEW_FIELD[]=array("caption"=>$this->extraFieldRS[$i]['fieldName'],"class"=>"previewtitle","data"=>$this->extraFieldRS[$i]['display'],"public"=>false,"other"=>false);
}
$AUIEO_JSON=  json_encode($jsonRender);
            echo "<table class='detailsOutside' width='100%'>
                    <tr>
                        <td><table class='detailsInside'>";
            displayMultiColumnTable($AUIEO_PREVIEW_FIELD);
            echo "</table></td></tr></table>";
             if($this->EEOSettingsRS['enabled'] == 1): ?>
                <table class="detailsOutside" width="100%">
                    <tr>
                        <td>
                            <table class="detailsInside">
                                <?php for ($i = 0; $i < intval(count($this->EEOValues)/2); $i++): ?>
                                    <tr>
                                        <td class="vertical"><?php $this->_($this->EEOValues[$i]['fieldName']); ?>:</td>
                                        <?php if($this->EEOSettingsRS['canSeeEEOInfo']): ?>
                                            <td class="data"><?php $this->_($this->EEOValues[$i]['fieldValue']); ?></td>
                                        <?php else: ?>
                                            <td class="data"><i><a href="javascript:void(0);" title="Ask an administrator to see the EEO info, or have permission granted to see it.">(Hidden)</a></i></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endfor; ?>
                            </table>
                        </td>
                        <?php if ($profileImage): ?>
                            <td width="390" height="100%" valign="top">
                        <?php else: ?>
                            </td><td width="50%" height="100%" valign="top">
                        <?php endif; ?>
                            <table class="detailsInside">
                                <?php for ($i = (intval(count($this->EEOValues))/2); $i < intval(count($this->EEOValues)); $i++): ?>
                                    <tr>
                                        <td class="vertical"><?php $this->_($this->EEOValues[$i]['fieldName']); ?>:</td>
                                        <?php if($this->EEOSettingsRS['canSeeEEOInfo']): ?>

                                            <td class="data"><?php $this->_($this->EEOValues[$i]['fieldValue']); ?></td>

                                        <?php else: ?>

                                            <td class="data"><i><a href="javascript:void(0);" title="Ask an administrator to see the EEO info, or have permission  granted to see it.">(Hidden)</a></i></td>

                                        <?php endif; ?>

                                    </tr>

                                <?php endfor; ?>

                            </table>

                        </td>

                    </tr>

                </table>

            <?php endif; ?>



            <table class="detailsOutside" width="100%">

                <tr>

                    <td>

                        <table class="detailsInside">

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



                            <tr>

                                <td valign="top" class="vertical">Upcoming Events:</td>

                                <td id="shortNotes" style="display:block;" class="data">

                                <?php foreach ($this->calendarRS as $rowNumber => $calendarData): ?>

                                    <div>

                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=calendar&view=DAYVIEW&month=<?php echo($calendarData['month']); ?>&year=20<?php echo($calendarData['year']); ?>&day=<?php echo($calendarData['day']); ?>&showEvent=<?php echo($calendarData['eventID']); ?>">

                                            <img src="<?php $this->_($calendarData['typeImage']) ?>" alt="" border="0" />

                                            <?php $this->_($calendarData['dateShow']) ?>:

                                            <?php $this->_($calendarData['title']); ?>

                                        </a>

                                    </div>

                                <?php endforeach; ?>

                                <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                                    <a href="#" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&a=addActivityChangeStatus&candidateID=<?php echo($this->candidateID); ?>&jobOrderID=-1&onlyScheduleEvent=true', 600, 350, null); return false;">

                                        <img src="images/calendar_add.gif" width="16" height="16" border="0" alt="Schedule Event" class="absmiddle" />&nbsp;Schedule Event

                                    </a>

                                <?php endif; ?>

                                </td>

                            </tr>



                            <?php if (isset($this->questionnaires) && !empty($this->questionnaires)): ?>

                            <tr>

                                <td valign="top" class="vertical" valign="top" align="left">Questionnaires:</td>

                                <td valign="top" class="data" valign="top" align="left">

                                    <table cellpadding="0" cellspacing="0" border="0">

                                    <tr>

                                        <td style="border-bottom: 1px solid #c0c0c0; font-weight: bold; padding-right: 10px;">Title (Internal)</td>

                                        <td style="border-bottom: 1px solid #c0c0c0; font-weight: bold; padding-right: 10px;">Completed</td>

                                        <td style="border-bottom: 1px solid #c0c0c0; font-weight: bold; padding-right: 10px;">Description (Public)</td>

                                    </tr>

                                    <?php foreach ($this->questionnaires as $questionnaire): ?>

                                    <tr>

                                        <td style="padding-right: 10px;" nowrap="nowrap"><a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&a=show_questionnaire&candidateID=<?php echo($this->candidateID); ?>&questionnaireTitle=<?php echo urlencode($questionnaire['questionnaireTitle']); ?>&print=no"><?php echo $questionnaire['questionnaireTitle']; ?></a></td>

                                        <td style="padding-right: 10px;" nowrap="nowrap"><?php echo date('F j. Y', strtotime($questionnaire['questionnaireDate'])); ?></td>

                                        <td style="padding-right: 10px;" nowrap="nowrap"><?php echo $questionnaire['questionnaireDescription']; ?></td>

                                        <td style="padding-right: 10px;" nowrap="nowrap">

                                            <a id="edit_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&a=show_questionnaire&candidateID=<?php echo($this->candidateID); ?>&questionnaireTitle=<?php echo urlencode($questionnaire['questionnaireTitle']); ?>&print=no">

                                                <img src="images/actions/view.gif" width="16" height="16" class="absmiddle" alt="view" border="0" />&nbsp;View

                                            </a>

                                            &nbsp;

                                            <a id="edit_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&a=show_questionnaire&candidateID=<?php echo($this->candidateID); ?>&questionnaireTitle=<?php echo urlencode($questionnaire['questionnaireTitle']); ?>&print=yes">

                                                <img src="images/actions/print.gif" width="16" height="16" class="absmiddle" alt="print" border="0" />&nbsp;Print

                                            </a>

                                        </td>

                                    </tr>

                                    <?php endforeach; ?>

                                    </table>

                                </td>

                            </tr>

                            <?php endif; ?>



                            <tr>

                                <td valign="top" class="vertical">Attachments:</td>

                                <td valign="top" class="data">

                                    <table class="attachmentsTable">

                                        <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData): ?>

                                            <?php if ($attachmentsData['isProfileImage'] != '1'): ?>

                                                <tr>

                                                    <td>

                                                        <?php echo $attachmentsData['retrievalLink']; ?>

                                                            <img src="<?php $this->_($attachmentsData['attachmentIcon']) ?>" alt="" width="16" height="16" border="0" />

                                                            &nbsp;

                                                            <?php $this->_($attachmentsData['originalFilename']) ?>

                                                        </a>

                                                    </td>

                                                    <td><?php echo($attachmentsData['previewLink']); ?></td>

                                                    <td><?php $this->_($attachmentsData['dateCreated']) ?></td>

                                                    <td>

                                                        <?php if (!$this->isPopup): ?>

                                                            <?php if ($this->accessLevel >= ACCESS_LEVEL_DELETE): ?>

                                                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&a=deleteAttachment&candidateID=<?php echo($this->candidateID); ?>&attachmentID=<?php $this->_($attachmentsData['attachmentID']) ?>" onclick="javascript:return confirm('Delete this attachment?');">

                                                                    <img src="images/actions/delete.gif" alt="" width="16" height="16" border="0" title="Delete" />

                                                                </a>

                                                            <?php endif; ?>

                                                        <?php endif; ?>

                                                    </td>

                                                </tr>

                                            <?php endif; ?>

                                        <?php endforeach; ?>

                                    </table>

                                    <?php if (!$this->isPopup): ?>

                                        <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                                            <?php if (isset($this->attachmentLinkHTML)): ?>

                                                <?php echo($this->attachmentLinkHTML); ?>

                                            <?php else: ?>

                                                <a href="#" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&a=createAttachment&candidateID=<?php echo($this->candidateID); ?>', 400, 125, null); return false;">

                                            <?php endif; ?>

                                                <img src="images/paperclip_add.gif" width="16" height="16" border="0" alt="Add Attachment" class="absmiddle" />&nbsp;Add Attachment

                                            </a>

                                        <?php endif; ?>

                                    <?php endif; ?>

                                </td>

                            </tr>
                        </table>

                    </td>

                </tr>

            </table>

<?php 
$objRole=Users::getInstance()->getRole();
$allowDelete=$objRole->getModulePermission(100,  Candidates::actionMapping("delete"));
$allowEdit=$objRole->getModulePermission(100,  Candidates::actionMapping("edit"));
if (!$this->isPopup): ?>
            
            <?php if ($allowEdit && $this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                <a id="edit_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&a=edit&candidateID=<?php echo($this->candidateID); ?>">

                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />&nbsp;Edit

                </a>

                &nbsp;&nbsp;&nbsp;&nbsp;

            <?php endif; ?>

            <?php 
            if ($allowDelete && $this->accessLevel >= ACCESS_LEVEL_DELETE){ ?>

                <a id="delete_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&a=delete&candidateID=<?php echo($this->candidateID); ?>" onclick="javascript:return confirm('Delete this candidate?');">

                    <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;Delete

                </a>

                &nbsp;&nbsp;&nbsp;&nbsp;

<?php 

    }

    if ($this->privledgedUser)

    { 

?>

                <a id="history_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=viewItemHistory&dataItemType=100&dataItemID=<?php echo($this->candidateID); ?>">

                    <img src="images/icon_clock.gif" width="16" height="16" class="absmiddle"  border="0" />&nbsp;View History

                </a>&nbsp;&nbsp;&nbsp;&nbsp;

                <a id="history_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=transfer&dataItemType=100&dataItemID=<?php echo($this->candidateID); ?>">

                    Transfer

                </a>&nbsp;&nbsp;&nbsp;&nbsp;

                <a id="history_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=duplicate&dataItemType=100&dataItemID=<?php echo($this->candidateID); ?>">

                    Copy

                </a>&nbsp;&nbsp;&nbsp;&nbsp;

<?php 

    } 

    if ($this->accessLevel >= ACCESS_LEVEL_MULTI_SA): ?>

                <?php if ($this->data['is_admin_hidden'] == 1): ?>

                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&a=administrativeHideShow&candidateID=<?php echo($this->candidateID); ?>&state=0">

                        <img src="images/resume_preview_inline.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;Administrative Show

                    </a>

                    <?php else: ?>

                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&a=administrativeHideShow&candidateID=<?php echo($this->candidateID); ?>&state=1">

                        <img src="images/resume_preview_inline.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;Administrative Hide

                    </a>

                <?php endif; ?>

                &nbsp;&nbsp;&nbsp;&nbsp;

            <?php endif; ?>

<?php endif; ?>

                

                <br clear="all" />

            <br />

            <p class="note">Emails <span style="float:right;"><a href="index.php?m=candidates&a=emailCandidates&idlist=<?php echo $this->candidateID; ?>">Send Email</a></span></p>

            <table class="sortablepair">

                <tr>

                    <th align="left">Date</th>

                    <th align="left">Subject</th>

                    <th align="left">From</th>

                    <th align="left">To</th>

                    <th align="left">Module</th>

					<th align="left">Edit</th>

                </tr>



<?php 

foreach ($this->email_list as $rowNumber => $emailData)

{

    $arrTmp=explode("Message:",$emailData['text']);

    $arrTmp=explode("Subject:",$arrTmp[0]);

    $subject=isset($arrTmp[1])?trim($arrTmp[1]):"";

?>

                    <tr class="<?php echo getAlternatingRowClass($rowNumber); ?>" id="email<?php echo($rowNumber); ?>">

                        <td valign="top">

                            <?php echo($emailData['date']); ?>

                        </td>

                        <td valign="top">

                            <?php  echo($subject); ?>

                        </td>

                        <td valign="top">

                            <?php  echo($emailData['from_address']); ?>

                        </td>

                        <td valign="top">

                                <?php echo $emailData['recipients']; ?>

                        </td>

                        <td valign="top">

                                <?php echo htmlentities($emailData['for_module']) ?>

                        </td>

						<td valign="top">

						<a href="index.php?m=candidates&a=editemail&email_history_id=<?php echo $emailData["email_history_id"]; ?>">Edit</a>

						</td>

                    </tr>

                    <tr class="<?php echo getAlternatingRowClass($rowNumber); ?>" id="emailDetails<?php echo($rowNumber); ?>" style="display:none;">

                        <td colspan="11" align="center">

                            <table width="98%" border="1" class="detailsOutside" style="margin: 5px;">

                                <tr>

                                    <td align="left" style="padding: 6px 6px 6px 6px; background-color: white; clear: both;">

                                        <div style="overflow: auto; height: 200px;" id="emailInner<?php echo($rowNumber); ?>">

                                            <img src="images/indicator.gif" alt="" />&nbsp;&nbsp;Loading email details...

                                        </div>

                                    </td>

                                </tr>

                            </table>

                        </td>

                    </tr>



                <?php } ?>

            </table>

                

            <br clear="all" />

            <br />



            <p class="note">Job Order Pipeline</p>

            <table class="sortablepair" width="100%">

                <tr>

                    <th></th>

                    <th align="left">Match</th>

                    <th align="left">Title</th>

                    <th align="left">Company</th>

                    <th align="left">Owner</th>

                    <th align="left">Added</th>

                    <th align="left">Entered By</th>

                    <th align="left">Status</th>

<?php if (!$this->isPopup): ?>

                    <th align="center">Action</th>

<?php endif; ?>

                </tr>



                <?php foreach ($this->pipelinesRS as $rowNumber => $pipelinesData): ?>

                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>" id="pipelineRow<?php echo($rowNumber); ?>">

                        <td valign="top">

                            <span id="pipelineOpen<?php echo($rowNumber); ?>">

                                <a href="javascript:void(0);" onclick="document.getElementById('pipelineDetails<?php echo($rowNumber); ?>').style.display=''; document.getElementById('pipelineClose<?php echo($rowNumber); ?>').style.display = ''; document.getElementById('pipelineOpen<?php echo($rowNumber); ?>').style.display = 'none'; PipelineDetails_populate(<?php echo($pipelinesData['candidateJobOrderID']); ?>, 'pipelineInner<?php echo($rowNumber); ?>', '<?php echo($this->sessionCookie); ?>');">

                                    <img src="images/arrow_next.png" alt="" border="0" title="Show History" />

                                </a>

                            </span>

                            <span id="pipelineClose<?php echo($rowNumber); ?>" style="display: none;">

                                <a href="javascript:void(0);" onclick="document.getElementById('pipelineDetails<?php echo($rowNumber); ?>').style.display = 'none'; document.getElementById('pipelineClose<?php echo($rowNumber); ?>').style.display = 'none'; document.getElementById('pipelineOpen<?php echo($rowNumber); ?>').style.display = '';">

                                    <img src="images/arrow_down.png" alt="" border="0" title="Hide History" />

                                </a>

                            </span>

                        </td>

                        <td valign="top">

                            <?php echo($pipelinesData['ratingLine']); ?>

                        </td>

                        <td valign="top">

                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&a=show&jobOrderID=<?php echo($pipelinesData['jobOrderID']); ?>" class="<?php $this->_($pipelinesData['linkClass']) ?>">

                                <?php $this->_($pipelinesData['title']) ?>

                            </a>

                        </td>

                        <td valign="top">

                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&companyID=<?php echo($pipelinesData['companyID']); ?>&a=show">

                                <?php $this->_($pipelinesData['companyName']) ?>

                            </a>

                        </td>

                        <td valign="top"><?php $this->_($pipelinesData['ownerAbbrName']) ?></td>

                        <td valign="top"><?php $this->_($pipelinesData['dateCreated']) ?></td>

                        <td valign="top"><?php $this->_($pipelinesData['addedByAbbrName']) ?></td>

                        <td valign="top" nowrap="nowrap"><?php $this->_($pipelinesData['status']) ?></td>

<?php if (!$this->isPopup): ?>

                        <td align="center" nowrap="nowrap">

                            <?php eval(Hooks::get('CANDIDATE_TEMPLATE_SHOW_PIPELINE_ACTION')); ?>

                            <?php if ($_SESSION['CATS']->getAccessLevel() >= ACCESS_LEVEL_EDIT && !$_SESSION['CATS']->hasUserCategory('sourcer')): ?>

                                <?php if ($pipelinesData['ratingValue'] < 0): ?>

                                    <a href="#" id="screenLink<?php echo($pipelinesData['candidateJobOrderID']); ?>" onclick="moImageValue<?php echo($pipelinesData['candidateJobOrderID']); ?> = 0; setRating(<?php echo($pipelinesData['candidateJobOrderID']); ?>, 0, 'moImage<?php echo($pipelinesData['candidateJobOrderID']); ?>', '<?php echo($_SESSION['CATS']->getCookie()); ?> '); return false;">

                                        <img id="screenImage<?php echo($pipelinesData['candidateJobOrderID']); ?>" src="images/actions/screen.gif" width="16" height="16" class="absmiddle" alt="" border="0" title="Mark as Screened" />

                                    </a>

                                <?php else: ?>

                                    <img src="images/actions/blank.gif" width="16" height="16" class="absmiddle" alt="" border="0" />

                                <?php endif; ?>

                            <?php endif; ?>

                            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                                <a href="#" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&a=addActivityChangeStatus&candidateID=<?php echo($this->candidateID); ?>&jobOrderID=<?php echo($pipelinesData['jobOrderID']); ?>', 600, 480, null); return false;" >

                                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="" border="0" title="Log an Activity / Change Status"/>

                                </a>

                            <?php endif; ?>

                            <?php if ($this->accessLevel >= ACCESS_LEVEL_DELETE): ?>

                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&a=removeFromPipeline&candidateID=<?php echo($this->candidateID); ?>&jobOrderID=<?php echo($pipelinesData['jobOrderID']); ?>"  onclick="javascript:return confirm('Delete from <?php $this->_(str_replace('\'', '\\\'', $pipelinesData['title'])); ?> (<?php $this->_(str_replace('\'', '\\\'', $pipelinesData['companyName'])); ?>) pipeline?')">

                                    <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="" border="0" title="Remove from Pipeline"/>

                                </a>

                            <?php endif; ?>

                        </td>

<?php endif; ?>

                    </tr>

                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>" id="pipelineDetails<?php echo($rowNumber); ?>" style="display:none;">

                        <td colspan="11" align="center">

                            <table width="98%" border="1" class="detailsOutside" style="margin: 5px;">

                                <tr>

                                    <td align="left" style="padding: 6px 6px 6px 6px; background-color: white; clear: both;">

                                        <div style="overflow: auto; height: 200px;" id="pipelineInner<?php echo($rowNumber); ?>">

                                            <img src="images/indicator.gif" alt="" />&nbsp;&nbsp;Loading pipeline details...

                                        </div>

                                    </td>

                                </tr>

                            </table>

                        </td>

                    </tr>



                <?php endforeach; ?>

            </table>



<?php if (!$this->isPopup): ?>

            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                <a href="#" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&a=considerForJobSearch&candidateID=<?php echo($this->candidateID); ?>', 750, 390, null); return false;">

                    <img src="images/consider.gif" width="16" height="16" class="absmiddle" alt="Add to Pipeline" border="0" />&nbsp;Add This Candidate to Job Order Pipeline

                </a>

            <?php endif; ?>

<?php endif; ?>

            <br clear="all" />

            <br />



            <p class="note">Activity</p>



            <table id="activityTable" class="sortable" width="100%">

                <tr>

                    <th align="left" width="125">Date</th>

                    <th align="left" width="90">Type</th>

                    <th align="left" width="90">Entered</th>

                    <th align="left" width="250">Regarding</th>

                    <th align="left">Notes</th>

<?php if (!$this->isPopup): ?>

                    <th align="left" width="40">Action</th>

<?php endif; ?>

                </tr>



                <?php foreach ($this->activityRS as $rowNumber => $activityData): ?>

                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">

                        <td align="left" valign="top" id="activityDate<?php echo($activityData['activityID']); ?>"><?php $this->_($activityData['dateCreated']) ?></td>

                        <td align="left" valign="top" id="activityType<?php echo($activityData['activityID']); ?>"><?php $this->_($activityData['typeDescription']) ?></td>

                        <td align="left" valign="top"><?php $this->_($activityData['enteredByAbbrName']) ?></td>

                        <td align="left" valign="top" id="activityRegarding<?php echo($activityData['activityID']); ?>"><?php $this->_($activityData['regarding']) ?></td>

                        <td align="left" valign="top" id="activityNotes<?php echo($activityData['activityID']); ?>"><?php echo($activityData['notes']); ?></td>

<?php if (!$this->isPopup): ?>

                        <td align="center" >

                            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                                <a href="#" id="editActivity<?php echo($activityData['activityID']); ?>" onclick="Activity_editEntry(<?php echo($activityData['activityID']); ?>, <?php echo($this->candidateID); ?>, <?php echo(DATA_ITEM_CANDIDATE); ?>, '<?php echo($this->sessionCookie); ?>'); return false;">

                                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="" border="0" title="Edit" />

                                </a>

                            <?php endif; ?>

                            <?php if ($this->accessLevel >= ACCESS_LEVEL_DELETE): ?>

                                <a href="#" id="deleteActivity<?php echo($activityData['activityID']); ?>" onclick="Activity_deleteEntry(<?php echo($activityData['activityID']); ?>, '<?php echo($this->sessionCookie); ?>'); return false;">

                                    <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="" border="0" title="Delete" />

                                </a>
                            <?php endif; ?>
                        </td>
<?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </table>
<?php if (!$this->isPopup){ ?>
            <div id="addActivityDiv">
                <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT){ ?>
                    <a href="#" id="addActivityLink" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&a=addActivityChangeStatus&candidateID=<?php echo($this->candidateID); ?>&jobOrderID=-1', 600, 480, null); return false;">
                        <img src="images/new_activity_inline.gif" width="16" height="16" class="absmiddle" title="Log an Activity / Change Status" alt="Log an Activity / Change Status" border="0" />&nbsp;Log an Activity
                    </a>
                <?php } ?>
                <img src="images/indicator2.gif" id="addActivityIndicator" alt="" style="visibility: hidden; margin-left: 5px;" height="16" width="16" />
            </div>
<?php 
}
$AUIEO_CONTENT=  ob_get_clean();
?>