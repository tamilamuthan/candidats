<?php
pageHeaderInclude('js/sorttable.js');
pageHeaderInclude('js/match.js');
pageHeaderInclude('js/pipeline.js');
pageHeaderInclude('js/attachment.js');
pageTitle('Job Order');
ob_start();
?>
<p class="note">Job Order Details</p>

<?php if ($this->data['isAdminHidden'] == 1): ?>
    <p class="warning">This Job Order is hidden.  Only CATS Administrators can view it or search for it.  To make it visible by the site users, click <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=administrativeHideShow&amp;jobOrderID=<?php echo($this->jobOrderID); ?>&amp;state=0" style="font-weight:bold;">Here.</a></p>
<?php endif; ?>

<?php if (isset($this->frozen)): ?>
    <table style="font-weight:bold; border: 1px solid #000; background-color: #ffed1a; padding:5px; margin-bottom:7px;" width="<?php /* if ($this->isModal): */ if(false): ?>100%<?php else: ?>100%<?php endif; ?>" id="candidateAlreadyInSystemTable">
        <tr>
            <td class="tdVertical" style="width:100%;">
                This Job Order is <?php $this->_($this->data['status']); ?> and can not be modified.
               <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>
                   <a id="edit_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=edit&amp;jobOrderID=<?php echo($this->jobOrderID); ?>">
                       <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />&nbsp;Edit
                   </a>
                   the Job Order to make it Active.&nbsp;&nbsp;
               <?php endif; ?>
            </td>
        </tr>
    </table>
<?php endif; 
/*$objArray=new ClsAuieoArray(4);
foreach($this->fields_detail as $record)
{
    $objArray->addData($record["fieldlabel"]);
    $objArray->addData(isset($this->data[$record["fieldname"]])?$this->data[$record["fieldname"]]:"");
}
trace($objArray->render());*/
?>

<table class="detailsOutside" width="100%" height="<?php echo((count($this->extraFieldRS)/2 + 12) * 22); ?>">
    <tr style="vertical-align:top;">
        <td width="50%" height="100%">
            <table class="detailsInside" height="100%">
                <tr>
                    <td class="vertical">Title:</td>
                    <td class="data" width="300">
                        <span class="<?php echo($this->data['titleClass']); ?>"><?php $this->_($this->data['title']); ?></span>
                        <?php echo($this->data['public']) ?>
                        <?php TemplateUtility::printSingleQuickActionMenu(DATA_ITEM_JOBORDER, $this->data['jobOrderID']); ?>
                    </td>
                </tr>

                <tr>
                    <td class="vertical">Company Name:</td>
                    <td class="data">
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php echo($this->data['companyID']); ?>">
                            <?php echo($this->data['companyName']); ?>
                        </a>
                    </td>
                </tr>

                <tr>
                    <td class="vertical">Department:</td>
                    <td class="data">
                        <?php echo($this->data['department']); ?>
                    </td>
                </tr>

                <tr>
                    <td class="vertical">CATS Job ID:</td>
                    <td class="data" width="300"><?php $this->_($this->data['jobOrderID']); ?></td>
                </tr>

                <tr>
                    <td class="vertical">Company Job ID:</td>
                    <td class="data"><?php echo($this->data['companyJobID']); ?></td>
                </tr>

                <!-- CONTACT INFO -->
                <tr>
                    <td class="vertical">Contact Name:</td>
                    <td class="data">
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php echo($this->data['contactID']); ?>">
                            <?php echo($this->data['contactFullName']); ?>
                        </a>
                    </td>
                </tr>

                <tr>
                    <td class="vertical">Contact Phone:</td>
                    <td class="data"><?php echo($this->data['contactWorkPhone']); ?></td>
                </tr>

                <tr>
                    <td class="vertical">Contact Email:</td>
                    <td class="data">
                        <a href="mailto:<?php $this->_($this->data['contactEmail']); ?>"><?php $this->_($this->data['contactEmail']); ?></a>
                    </td>
                </tr>
                <!-- /CONTACT INFO -->

                <tr>
                    <td class="vertical">Location:</td>
                    <td class="data"><?php $this->_($this->data['cityAndState']); ?></td>
                </tr>

                <tr>
                    <td class="vertical">Max Rate:</td>
                    <td class="data"><?php $this->_($this->data['maxRate']); ?></td>
                </tr>

                <tr>
                    <td class="vertical">Salary:</td>
                    <td class="data"><?php $this->_($this->data['salary']); ?></td>
                </tr>

                <tr>
                    <td class="vertical">Start Date:</td>
                    <td class="data"><?php $this->_($this->data['startDate']); ?></td>
                </tr>

                <?php for ($i = 0; $i < intval(count($this->extraFieldRS)/2); $i++): ?>
                   <tr>
                        <td class="vertical"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</td>
                        <td class="data"><?php echo($this->extraFieldRS[$i]['display']); ?></td>
                  </tr>
                <?php endfor; ?>

                <?php eval(Hooks::get('JO_TEMPLATE_SHOW_BOTTOM_OF_LEFT')); ?>

            </table>
        </td>

        <td width="50%" height="100%" style="vertical-align:top;" >
            <table class="detailsInside" height="100%">
                <tr>
                    <td class="vertical">Duration:</td>
                    <td class="data"><?php $this->_($this->data['duration']); ?></td>
                </tr>

                <tr>
                    <td class="vertical">Openings:</td>
                    <td class="data"><?php $this->_($this->data['openings']); if ($this->data['openingsAvailable'] != $this->data['openings']): ?> (<?php $this->_($this->data['openingsAvailable']); ?> Available)<?php endif; ?></td>
                </tr>

                <tr>
                    <td class="vertical">Type:</td>
                    <td class="data"><?php $this->_($this->data['typeDescription']); ?></td>
                </tr>

                <tr>
                    <td class="vertical">Status:</td>
                    <td class="data"><?php $this->_($this->data['status']); ?></td>
                </tr>

                <tr>
                    <td class="vertical">Pipeline:</td>
                    <td class="data"><?php $this->_($this->data['pipeline']) ?></td>
                </tr>

                <tr>
                    <td class="vertical">Submitted:</td>
                    <td class="data"><?php $this->_($this->data['submitted']) ?></td>
                </tr>

                <tr>
                    <td class="vertical">Days Old:</td>
                    <td class="data"><?php $this->_($this->data['daysOld']); ?></td>
                </tr>

                <tr>
                    <td class="vertical">Created:</td>
                    <td class="data"><?php $this->_($this->data['dateCreated']); ?> (<?php $this->_($this->data['enteredByFullName']); ?>)</td>
                </tr>

                <tr>
                    <td class="vertical">Recruiter:</td>
                    <td class="data"><?php $this->_($this->data['recruiterFullName']); ?></td>
                </tr>

                <tr>
                    <td class="vertical">Owner:</td>
                    <td class="data"><?php $this->_($this->data['ownerFullName']); ?></td>
                </tr>

                <?php for ($i = (intval(count($this->extraFieldRS))/2); $i < (count($this->extraFieldRS)); $i++): ?>
                    <tr>
                        <td class="vertical"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</td>
                        <td class="data"><?php echo($this->extraFieldRS[$i]['display']); ?></td>
                    </tr>
                <?php endfor; ?>

                <?php eval(Hooks::get('JO_TEMPLATE_SHOW_BOTTOM_OF_RIGHT')); ?>
            </table>
        </td>
    </tr>
</table>

<?php if ($this->isPublic): ?>
<div style="background-color: #E6EEFE; padding: 10px; margin: 5px 0 12px 0; border: 1px solid #728CC8;">
    <b>This job order is public<?php if ($this->careerPortalURL === false): ?>.</b><?php else: ?>
        and will be shown on your
        <?php if ($_SESSION['CATS']->getAccessLevel() >= ACCESS_LEVEL_SA): ?>
            <a style="font-weight: bold;" href="<?php $this->_($this->careerPortalURL); ?>">Careers Website</a>.
        <?php else: ?>
            Careers Website.
        <?php endif; ?></b>
    <?php endif; ?>

    <?php if ($this->questionnaireID !== false): ?>
        <br />Applicants must complete the "<i><?php echo $this->questionnaireData['title']; ?></i>" (<a href="<?php echo CATSUtility::getIndexName(); ?>?m=settings&a=careerPortalQuestionnaire&questionnaireID=<?php echo $this->questionnaireID; ?>">edit</a>) questionnaire when applying.
    <?php else: ?>
        <br />You have not attached any
        <?php if ($_SESSION['CATS']->getAccessLevel() >= ACCESS_LEVEL_SA): ?>
            <a href="<?php echo CATSUtility::getIndexName(); ?>?m=settings&a=careerPortalSettings">Questionnaires</a>.
        <?php else: ?>
            Questionnaires.
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php endif; ?>

<table class="detailsOutside" width="100%">
    <tr>
        <td>
            <table class="detailsInside">
                <tr>
                    <td valign="top" class="vertical">Attachments:</td>
                    <td valign="top" class="data">
                        <table class="attachmentsTable">
                            <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData): ?>
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
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td valign="top" class="vertical">Description:</td>

                    <td class="data" colspan="2">
                        <?php if($this->data['description'] != ''): ?>
                        <div id="shortDescription" style="overflow: auto; height:170px; border: #AAA 1px solid; padding:5px;">
                            <?php echo($this->data['description']); ?>
                        </div>
                        <?php endif; ?>
                    </td>

                </tr>

                <tr>
                    <td valign="top" class="vertical">Internal Notes:</td>

                    <td class="data" style="width:320px;">
                        <?php if($this->data['notes'] != ''): ?>
                            <div id="shortDescription" style="overflow: auto; height:240px; border: #AAA 1px solid; padding:5px;">
                                <?php echo($this->data['notes']); ?>
                            </div>
                        <?php endif; ?>
                    </td>

                    <td style="vertical-align:top;">
                        <?php echo($this->pipelineGraph);  ?>
                    </td>

                </tr>
            </table>
        </td>
    </tr>
</table>
<br clear="all" />
<br />

<p class="note">Candidate Pipeline</p>

<p id="ajaxPipelineControl">
    Number of visible entries:&nbsp;&nbsp;
    <select id="numberOfEntriesSelect" onchange="PipelineJobOrder_changeLimit(<?php $this->_($this->data['jobOrderID']); ?>, this.value, <?php echo(1); ?>, 'ajaxPipelineTable', '<?php echo($this->sessionCookie); ?>', 'ajaxPipelineTableIndicator', '<?php echo(CATSUtility::getIndexName()); ?>');" class="selectBox">
        <option value="15" <?php if ($this->pipelineEntriesPerPage == 15): ?>selected<?php endif; ?>>15 entries</option>
        <option value="30" <?php if ($this->pipelineEntriesPerPage == 30): ?>selected<?php endif; ?>>30 entries</option>
        <option value="50" <?php if ($this->pipelineEntriesPerPage == 50): ?>selected<?php endif; ?>>50 entries</option>
        <option value="99999" <?php if ($this->pipelineEntriesPerPage == 99999): ?>selected<?php endif; ?>>All entries</option>
    </select>&nbsp;
    <span id="ajaxPipelineNavigation">
    </span>&nbsp;
    <img src="images/indicator.gif" alt="" id="ajaxPipelineTableIndicator" />
</p>

<div id="ajaxPipelineTable">
</div>
<script type="text/javascript">
    PipelineJobOrder_populate(<?php $this->_($this->data['jobOrderID']); ?>, 0, <?php $this->_($this->pipelineEntriesPerPage); ?>, 'dateCreatedInt', 'desc', <?php echo(1); ?>, 'ajaxPipelineTable', '<?php echo($this->sessionCookie); ?>', 'ajaxPipelineTableIndicator', '<?php echo(CATSUtility::getIndexName()); ?>');
</script>
    
<?php $AUIEO_CONTENT=ob_get_clean(); ?>