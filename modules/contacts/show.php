<?php 
$objRole=Users::getInstance()->getRole();
$allowDelete=$objRole->getModulePermission(300, Contacts::actionMapping("delete"));
$allowEdit=$objRole->getModulePermission(300, Contacts::actionMapping("edit"));
pageHeaderInclude('js/activity.js');
pageHeaderInclude('js/sorttable.js');
pageHeaderInclude('js/match.js');
pageHeaderInclude('js/lib.js');
pageHeaderInclude('js/pipeline.js');
pageHeaderInclude('js/attachment.js');
pageHeaderInclude('js/xeditable/js/xeditable.js');
pageTitle('Contact - '.$this->data['first_name'].' '.$this->data['last_name']);
$arrModuleInfo=getModuleInfo("modulename");
$moduleInfo=$arrModuleInfo[$_REQUEST["m"]];
ob_start();
TemplateUtility::printSingleQuickActionMenu(DATA_ITEM_COMPANY, $this->contactID);
$other=ob_get_clean();

$other2="&nbsp;
                                        <a id='vCard' href='index.php?m=contacts&amp;a=downloadVCard&contactID={$this->contactID}'>
                                            <img src='images/vcard.gif' class='absmiddle' alt='vCard' border='0' />
                                        </a>";

$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Name","class"=>$this->data['titleClassContact'],"data"=>$this->data['first_name']." ".$this->data['last_name'],"public"=>false,"other"=>$other.$other2);
$data="";
if($this->data['reports_to'] == -1 || $this->data['reports_to'] == 0 || $this->data['reportsToTitle'] == '')
{ 
    $data = "(None)";
}
else
{
       $data = "<a href='index.php?m=contacts&a=show&contactID={$this->data["reportsTo"]}'>
            <img src='images/contact_small.gif' border='0' />&nbsp;
       {$this->data['reportsToFirstName']}&nbsp;{$this->data['reportsToLastName']}
        </a>
        &nbsp;({$this->data['reportsToTitle']})";
}
$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Reports To","class"=>"previewtitle","data"=>$data,"public"=>false,"other"=>false);

$other="";
if ($this->data['left_company'])
{ 
    $other="&nbsp;(no longer associated with company)";
}
$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Company","class"=>"previewtitle","data"=>"<a href='index.php?m=companies&a=show&companyID={$this->data['company_id']}'>{$this->data['companyName']}</a>","public"=>false,"other"=>$other);
$AUIEO_PREVIEW_FIELD[]=array("caption"=>"E-Mail","class"=>"previewtitle","data"=>$this->data['email1'],"public"=>false,"other"=>false);

$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Title","class"=>"previewtitle","data"=>$this->data['title'],"public"=>false,"other"=>false,'key'=>'title','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=title&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['title']}");
$AUIEO_PREVIEW_FIELD[]=array("caption"=>"2nd E-Mail","class"=>"previewtitle","data"=>$this->data['email2'],"public"=>false,"other"=>false,'key'=>'email2','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=email2&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['email2']}");

$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Department","class"=>"previewtitle","data"=>$this->data['department'],"public"=>false,"other"=>false);
$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Address","class"=>"previewtitle","data"=>nl2br(htmlspecialchars($this->data['address']))."
    <br />{$this->data['cityAndState']} {$this->data['zip']}","public"=>false,"other"=>false);
    
$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Work Phone","class"=>"previewtitle","data"=>$this->data['phone_work'],"public"=>false,"other"=>false,'key'=>'phone_work','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=phone_work&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['phone_work']}");
$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Created","class"=>"previewtitle","data"=>$this->data['dateCreated']."({$this->data['enteredByFullName']})","public"=>false,"other"=>false);

$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Cell Phone","class"=>"previewtitle","data"=>$this->data['phone_cell'],"public"=>false,"other"=>false,'key'=>'phone_cell','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=phone_cell&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['phone_cell']}");
$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Other Phone","class"=>"previewtitle","data"=>$this->data['phone_other'],"public"=>false,"other"=>false,'key'=>'phone_other','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=phone_other&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['phone_other']}");

$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Owner","class"=>"previewtitle","data"=>$this->data['dateCreated']."({$this->data['ownerFullName']})","public"=>false,"other"=>false);
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
ob_start();
echo "<table class='detailsOutside' width='100%'>
                    <tr>
                        <td><table class='detailsInside'>";
            displayMultiColumnTable($AUIEO_PREVIEW_FIELD);
            echo "</table></td></tr></table>";
?>

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

                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=calendar&amp;view=DAYVIEW&amp;month=<?php echo($calendarData['month']); ?>&amp;year=20<?php echo($calendarData['year']); ?>&amp;day=<?php echo($calendarData['day']); ?>&amp;showEvent=<?php echo($calendarData['eventID']); ?>">

                                            <img src="<?php $this->_($calendarData['typeImage']) ?>" alt="" border="0">

                                            <?php $this->_($calendarData['dateShow']) ?>:

                                            <?php $this->_($calendarData['title']); ?>

                                        </a>

                                    </div>

                                <?php endforeach; ?>

                                <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                                    <a href="#" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=addActivityScheduleEvent&amp;contactID=<?php echo($this->contactID); ?>&amp;onlyScheduleEvent=true', 600, 200, null); return false;">

                                        <img src="images/calendar_add.gif" width="16" height="16" border="0" alt="Schedule Event" class="absmiddle" />&nbsp;Schedule Event

                                    </a>

                                <?php endif; ?>

                                </td>

                            </tr>

                        </table>

                    </td>

                </tr>

            </table>

            <?php if ($allowEdit && $this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                <a id="edit_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=edit&amp;contactID=<?php echo($this->contactID); ?>">

                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />&nbsp;Edit

                </a>

                &nbsp;&nbsp;&nbsp;&nbsp;

            <?php endif; ?>

            <?php if ($allowDelete && $this->accessLevel >= ACCESS_LEVEL_DELETE): ?>

                <a id="delete_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=delete&amp;contactID=<?php echo($this->contactID); ?>" onclick="javascript:return confirm('Delete this candidate?');">

                    <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;Delete

                </a>

                &nbsp;&nbsp;&nbsp;&nbsp;

            <?php endif; ?>

            <?php if ($this->privledgedUser): ?>

                <a id="history_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=viewItemHistory&amp;dataItemType=300&amp;dataItemID=<?php echo($this->contactID); ?>">

                    <img src="images/icon_clock.gif" width="16" height="16" class="absmiddle"  border="0" />&nbsp;View History

                </a>

                &nbsp;&nbsp;&nbsp;&nbsp;

            <?php endif; ?>



            <br clear="all" />

            <br />



            <p class="note">Job Orders</p>

            <table class="sortable" width="100%">

                <tr>

                    <th align="left" width="200">Title</th>

                    <th align="left" width="15">Type</th>

                    <th align="left" width="15">Status</th>

                    <th align="left" width="60">Created</th>

                    <th align="left" width="60">Modified</th>

                    <th align="left" width="60">Start</th>

                    <th align="right" width="15">Age</th>

                    <th align="right" width="10">S</th>

                    <th align="right" width="10">P</th>

                    <th align="left" width="65">Recruiter</th>

                    <th align="left" width="68">Owner</th>

                </tr>



                <?php foreach ($this->jobOrdersRS as $rowNumber => $jobOrdersData): ?>

                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">

                        <td valign="top">

                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php $this->_($jobOrdersData['jobOrderID']) ?>">

                                <?php $this->_($jobOrdersData['title']) ?>

                            </a>

                        </td>

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['type']) ?></td>

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['status']) ?></td>

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['dateCreated']) ?></td>

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['dateModified']) ?></td>

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['startDate']) ?></td>

                        <td valign="top" align="right"><?php $this->_($jobOrdersData['daysOld']) ?></td>

                        <td valign="top" align="right"><?php $this->_($jobOrdersData['submitted']); ?></td>

                        <td valign="top" align="right"><?php $this->_($jobOrdersData['pipeline']); ?></td>

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['recruiterAbbrName']); ?></td>

                        <td valign="top" align="left"><?php $this->_($jobOrdersData['ownerAbbrName']); ?></td>

                    </tr>

                <?php endforeach; ?>

            </table>

            

            <br clear="all" />

            <br />

            <p class="note">Emails <span style="float:right;"><a href="index.php?m=contacts&a=emailContacts&idlist=<?php echo $this->contactID; ?>">Send Email</a></span></p>

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

                foreach ($this->email_list as $rowNumber => $emailData): 

                    

                    $arrTmp=explode("Message:",$emailData['text']);

    $arrTmp=explode("Subject:",$arrTmp[0]);

    $subject=isset($arrTmp[1])?trim($arrTmp[1]):"";

                    ?>

                    <tr class="<?php echo getAlternatingRowClass($rowNumber); ?>" id="email<?php echo($rowNumber); ?>">

                        <td valign="top">

                            <?php echo($emailData['date']); ?>

                        </td>

                        <td valign="top">

                            <?php echo($subject); ?>

                        </td>

                        <td valign="top">

                            <?php echo($emailData['from_address']); ?>

                        </td>

                        <td valign="top">

                                <?php echo $emailData['recipients']; ?>

                        </td>

                        <td valign="top">

                                <?php echo htmlentities($emailData['for_module']) ?>

                        </td>

                        <td valign="top">

                                <?php //echo htmlentities($emailData['edit']) ?>

                               <a href="index.php?m=contacts&a=editemail&email_history_id=<?php echo $emailData["email_history_id"]; ?>">Edit </a>

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



                <?php endforeach; ?>

            </table>

            

            

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

                    <th align="left" width="40">Action</th>

                </tr>



                <?php foreach ($this->activityRS as $rowNumber => $activityData): ?>

                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">

                        <td align="left" valign="top" id="activityDate<?php echo($activityData['activityID']); ?>"><?php $this->_($activityData['dateCreated']) ?></td>

                        <td align="left" valign="top" id="activityType<?php echo($activityData['activityID']); ?>"><?php $this->_($activityData['typeDescription']) ?></td>

                        <td align="left" valign="top"><?php $this->_($activityData['enteredByAbbrName']) ?></td>

                        <td align="left" valign="top" id="activityRegarding<?php echo($activityData['activityID']); ?>"><?php $this->_($activityData['regarding']) ?></td>

                        <td align="left" valign="top" id="activityNotes<?php echo($activityData['activityID']); ?>"><?php $this->_($activityData['notes']) ?></td>

                        <td align="center" >

                            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                                <a href="#" id="editActivity<?php echo($activityData['activityID']); ?>" onclick="Activity_editEntry(<?php echo($activityData['activityID']); ?>, <?php echo($this->contactID); ?>, <?php echo(DATA_ITEM_CONTACT); ?>, '<?php echo($this->sessionCookie); ?>'); return false;">

                                    <img src="images/actions/edit.gif" width="16" height="16" alt="" class="absmiddle" border="0" title="Edit"/>

                                </a>

                            <?php endif; ?>

                            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                                <a href="#" id="deleteActivity<?php echo($activityData['activityID']); ?>" onclick="Activity_deleteEntry(<?php echo($activityData['activityID']); ?>, '<?php echo($this->sessionCookie); ?>'); return false;">

                                    <img src="images/actions/delete.gif" width="16" height="16" alt="" class="absmiddle" border="0" title="Delete"/>

                                </a>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </table>

            <div id="addActivityDiv">

                <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>

                    <a href="#" id="addActivityLink" title="Log an Activity / Schedule Event" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=addActivityScheduleEvent&amp;contactID=<?php echo($this->contactID); ?>', 600, 375, null); return false;">

                        <img src="images/new_activity_inline.gif" width="16" height="16" class="absmiddle" title="Log an Activity / Schedule Event" alt="Log an Activity / Schedule Event" border="0" />&nbsp;Log an Activity / Schedule Event

                    </a>

                <?php endif; ?>

                <img src="images/indicator2.gif" id="addActivityIndicator" alt="" style="visibility: hidden; margin-left: 5px;" height="16" width="16" />

            </div>

<?php
$this->subTemplate(dirname(__FILE__)."/AssignTagModal.php","AUIEO_TAG_UL");
$AUIEO_CONTENT=  ob_get_clean();

?>