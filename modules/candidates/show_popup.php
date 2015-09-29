<?php 
//trace("======");
/* 
 * CandidATS
 * Sites Management
 *
 * Copyright (C) 2014 - 2015 Auieo Software Private Limited, Parent Company of Unicomtech.
 * 
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */
$indexName=CATSUtility::getIndexName();
ob_start();
pageHeaderInclude('js/activity.js');
pageHeaderInclude('js/sorttable.js');
pageHeaderInclude('js/match.js');
pageHeaderInclude('js/lib.js');
pageHeaderInclude('js/pipeline.js');
pageHeaderInclude('js/attachment.js');

pageTitle('Candidate - '.$this->data['firstName'].' '.$this->data['lastName']);
//TemplateUtility::printHeader('Candidate - '.$this->data['firstName'].' '.$this->data['lastName'], array( 'js/activity.js', 'js/sorttable.js', 'js/match.js', 'js/lib.js', 'js/pipeline.js', 'js/attachment.js'));

$AUIEO_HEADER=  ob_get_clean();
ob_start();
TemplateUtility::printSingleQuickActionMenu(DATA_ITEM_CANDIDATE, $this->data['candidateID']);
$AUIEO_SINGLE_MENU=  ob_get_clean();

$AUIEO_ADDRESS=nl2br(htmlspecialchars($this->data['address']));
$AUIEO_WEBSITE="";
if (!empty($this->data['webSite']))
{
    $AUIEO_WEBSITE="<a href='{$this->data['webSite']}' target='_blank'>{$this->data['webSite']}</a>";
}
ob_start();
for ($i = 0; $i < intval(count($this->extraFieldRS)/2); $i++)
{
    echo "<tr>
        <td class='vertical'>{$this->extraFieldRS[$i]['fieldName']}:</td>
        <td class='data'>{$this->extraFieldRS[$i]['display']}</td>
    </tr>";
}
$AUIEO_EXTRA_FIELD = ob_get_clean();
$AUIEO_EXTRA_FIELD2="";
for ($i = (intval(count($this->extraFieldRS))/2); $i < (count($this->extraFieldRS)); $i++)
{ 
    $AUIEO_EXTRA_FIELD2=$AUIEO_EXTRA_FIELD2. "<tr>
        <td class='vertical'>{$this->extraFieldRS[$i]['fieldName']}:</td>
        <td class='data'>{$this->extraFieldRS[$i]['display']}</td>
    </tr>";
}
$AUIEO_CONTENT="";
ob_start();

foreach ($this->attachmentsRS as $rowNumber => $attachmentsData)
{
    if ($attachmentsData['isProfileImage'] == '1')
    {
?>
        <table class="detailsInside">
            <tr>
                <td style="text-align:center;" class="vertical">
&nbsp;&nbsp;
                    Picture:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </td>
            </tr>
            <tr>
                <td class="data">
                    <a href="attachments/<?php $this->_($attachmentsData['directoryName']) ?>/<?php $this->_($attachmentsData['storedFilename']) ?>">
                        <img src="attachments/<?php $this->_($attachmentsData['directoryName']) ?>/<?php $this->_($attachmentsData['storedFilename']) ?>" border="0" alt="" width="125" />
                    </a>
                </td>
            </tr>
        </table>
    <?php 
    }
}
if($this->EEOSettingsRS['enabled'] == 1)
{
    echo "<table class='detailsOutside' width='100%'>
                    <tr>
                        <td>
                            <table class='detailsInside'>";
    
    for ($i = 0; $i < intval(count($this->EEOValues)/2); $i++)
    {
        echo "<tr><td class='vertical'>{$this->EEOValues[$i]['fieldName']}:</td>";
        if($this->EEOSettingsRS['canSeeEEOInfo'])
        {
            echo "<td class='data'>{$this->EEOValues[$i]['fieldValue']}</td>";
        }
        else
        { 
            echo "<td class='data'><i><a href='javascript:void(0);' title='Ask an administrator to see the EEO info, or have permission granted to see it.'>(Hidden)</a></i></td>";
        }
        echo "</tr>";
    }
    echo "</table>
                        </td>
                            <td class='{$this->candidateShowClass}' valign='top'>
 
                            <table class='detailsInside'>";
for ($i = (intval(count($this->EEOValues))/2); $i < intval(count($this->EEOValues)); $i++)
{
    echo "<tr><td class='vertical'>{$this->EEOValues[$i]['fieldName']}:</td>";
    if($this->EEOSettingsRS['canSeeEEOInfo'])
    {
        echo "<td class='data'>{$this->EEOValues[$i]['fieldValue']}</td>";
    }
    else
    {
        echo "<td class='data'><i><a href='javascript:void(0);' title='Ask an administrator to see the EEO info, or have permission  granted to see it.'>(Hidden)</a></i></td>";
    } 
    echo "</tr>";
} 
echo "</table>
                        </td>
                    </tr>
                </table>";
} ?>

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
                                        <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData):
                                            if ($attachmentsData['isProfileImage'] != '1'): ?>
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
                                                        
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
                
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
<?php 

echo "</tr>";
foreach ($this->pipelinesRS as $rowNumber => $pipelinesData)
{
    ob_start();
    TemplateUtility::printAlternatingRowClass($rowNumber);
    $rowClass=ob_get_clean();
    echo "<tr class='{$rowClass}' id='pipelineRow{$rowNumber}'>
                        <td valign='top'>
                            <span id='pipelineOpen{$rowNumber}'>
                                <a href='javascript:void(0);' onclick=\"document.getElementById('pipelineDetails{$rowNumber}').style.display=''; document.getElementById('pipelineClose{$rowNumber}').style.display = ''; document.getElementById('pipelineOpen{$rowNumber}').style.display = 'none'; PipelineDetails_populate({$pipelinesData['candidateJobOrderID']}, 'pipelineInner{$rowNumber}', '{$this->sessionCookie}');\">
                                    <img src='images/arrow_next.png' alt='' border='0' title='Show History' />
                                </a>
                            </span>
                            <span id='pipelineClose{$rowNumber}' style='display: none;'>
                                <a href='javascript:void(0);' onclick=\"document.getElementById('pipelineDetails{$rowNumber}').style.display = 'none'; document.getElementById('pipelineClose{$rowNumber}').style.display = 'none'; document.getElementById('pipelineOpen{$rowNumber}').style.display = '';\">
                                    <img src='images/arrow_down.png' alt='' border='0' title='Hide History' />
                                </a>
                            </span>
                        </td>
                        <td valign='top'>
                            {$pipelinesData['ratingLine']}
                        </td>
                        <td valign='top'>
                            <a href='{$indexName}?m=joborders&a=show&jobOrderID={$pipelinesData['jobOrderID']}' class='{$pipelinesData['linkClass']}'>
                                {$pipelinesData['title']}
                            </a>
                        </td>
                        <td valign='top'>
                            <a href='{$indexName}?m=companies&companyID={$pipelinesData['companyID']}&a=show'>
                                {$pipelinesData['companyName']}
                            </a>
                        </td>
                        <td valign='top'>{$pipelinesData['ownerAbbrName']}</td>
                        <td valign='top'>{$pipelinesData['dateCreated']}</td>
                        <td valign='top'>{$pipelinesData['addedByAbbrName']}</td>
                        <td valign='top' nowrap='nowrap'>{$pipelinesData['status']}</td>";
 
?>
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

<?php 
}
echo "</table>";

?>
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
<?php 
    echo "</tr>";
foreach ($this->activityRS as $rowNumber => $activityData)
{ ?>
                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                        <td align="left" valign="top" id="activityDate<?php echo($activityData['activityID']); ?>"><?php $this->_($activityData['dateCreated']) ?></td>
                        <td align="left" valign="top" id="activityType<?php echo($activityData['activityID']); ?>"><?php $this->_($activityData['typeDescription']) ?></td>
                        <td align="left" valign="top"><?php $this->_($activityData['enteredByAbbrName']) ?></td>
                        <td align="left" valign="top" id="activityRegarding<?php echo($activityData['activityID']); ?>"><?php $this->_($activityData['regarding']) ?></td>
                        <td align="left" valign="top" id="activityNotes<?php echo($activityData['activityID']); ?>"><?php echo($activityData['notes']); ?></td>
<?php 
    
    echo "</tr>";
}
echo "</table>";

$AUIEO_CONTENT=  ob_get_clean();
?>