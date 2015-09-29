<?php
pageHeaderInclude('js/sorttable.js');
pageHeaderInclude('js/match.js');
pageHeaderInclude('js/pipeline.js');
pageHeaderInclude('js/attachment.js');
pageHeaderInclude('js/xeditable/js/xeditable.js');
$arrModuleInfo=getModuleInfo("modulename");
$moduleInfo=$arrModuleInfo[$_REQUEST["m"]];
pageTitle('Job Order');
ob_start();
 /*if ($this->data['is_admin_hidden'] == 1): ?>
                <p class="warning">This Job Order is hidden.  Only CATS Administrators can view it or search for it.  To make it visible by the site users, click <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&a=administrativeHideShow&jobOrderID=<?php echo($this->jobOrderID); ?>&state=0" style="font-weight:bold;">Here.</a></p>
            <?php endif;*/ 
            echo $this->adminHidden;echo $this->frozen;
            $AUIEO_PREVIEW_FIELD=array();
            $singleActionMenu="";
            ob_start();
            TemplateUtility::printSingleQuickActionMenu(DATA_ITEM_JOBORDER, $this->data['joborder_id']);
            $other=ob_get_clean();
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Ttitle","class"=>$this->data['titleClass'],"data"=>$this->data['title'],"public"=>$this->data['public'],"other"=>$other);
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Duration","class"=>"previewtitle","data"=>$this->data['duration'],"public"=>false,"other"=>false);
            
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Company Name","class"=>"previewtitle","data"=>"<a href='index.php?m=companies&a=show&companyID={$this->data['company_id']}'>{$this->data['companyName']}</a>","public"=>false,"other"=>false);
            $openingAvailable="";
            if (isset($this->data['openings_available'] ) && $this->data['openings_available'] != $this->data['openings']) $openingAvailable=" ({$this->data['openings_available']} Available)";
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Openings","class"=>"previewtitle","data"=>$this->data['openings'].$openingAvailable,"public"=>false,"other"=>false,'key'=>'openings','sql'=>"index.php?m={$moduleInfo["modulename"]}&a=updateFieldData&field=openings&{$moduleInfo["primarykey"]}={$this->data[$moduleInfo["primarykey"]]}&data={$this->data['openings']}");
            
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Department","class"=>"previewtitle","data"=>$this->data['department'],"public"=>false,"other"=>false);
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Type","class"=>"previewtitle","data"=>$this->data['typeDescription'],"public"=>false,"other"=>false);
            
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"CATS Job ID","class"=>"previewtitle","data"=>$this->data['joborder_id'],"public"=>false,"other"=>false);
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Status","class"=>"previewtitle","data"=>$this->data['status'],"public"=>false,"other"=>false);
            
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Company Job ID","class"=>"previewtitle","data"=>$this->data['client_job_id'],"public"=>false,"other"=>false);
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Pipeline","class"=>"previewtitle","data"=>$this->data['pipeline'],"public"=>false,"other"=>false);
            
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Contact Name","class"=>"previewtitle","data"=>"<a href='index.php?m=contacts&a=show&contactID={$this->data['contact_id']}'>{$this->data['contactFullName']}</a>","public"=>false,"other"=>false);
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Submitted","class"=>"previewtitle","data"=>$this->data['submitted'],"public"=>false,"other"=>false);
            
            if($this->data['contact_id']>0)
            {
                $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Contact Phone","class"=>"previewtitle","data"=>$this->data['contactWorkPhone'],"public"=>false,"other"=>false,'key'=>'contactWorkPhone','sql'=>"index.php?m=contacts&a=updateFieldData&field=phone_work&contact_id={$this->data['contact_id']}&data={$this->data['contactWorkPhone']}");
            }
            else
            {
                $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Contact Phone","class"=>"previewtitle","data"=>$this->data['contactWorkPhone'],"public"=>false,"other"=>false);
            }
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Days Old","class"=>"previewtitle","data"=>$this->data['daysOld'],"public"=>false,"other"=>false);
            
            if($this->data['contact_id']>0)
            {
                $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Contact Email","class"=>"previewtitle","data"=>"<a href='mailto:{$this->data['contactEmail']}'>{$this->data['contactEmail']}</a>","public"=>false,"other"=>false,'key'=>'contactEmail','sql'=>"index.php?m=contacts&a=updateFieldData&field=email1&contact_id={$this->data['contact_id']}&data={$this->data['contactEmail']}");
            }
            else
            {
                $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Contact Email","class"=>"previewtitle","data"=>"<a href='mailto:{$this->data['contactEmail']}'>{$this->data['contactEmail']}</a>","public"=>false,"other"=>false);
            }
            $dateCreated="{$this->data['dateCreated']} ({$this->data['enteredByFullName']})";
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Created","class"=>"previewtitle","data"=>$dateCreated,"public"=>false,"other"=>false);
            
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Location","class"=>"previewtitle","data"=>$this->data['cityAndState'],"public"=>false,"other"=>false);
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Recruiter","class"=>"previewtitle","data"=>$this->data['recruiterFullName'],"public"=>false,"other"=>false);
            
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Max Rate","class"=>"previewtitle","data"=>$this->data['rate_max'],"public"=>false,"other"=>false,'key'=>'rate_max','sql'=>"index.php?m=joborders&a=updateFieldData&field=rate_max&joborder_id={$this->data["joborder_id"]}&data={$this->data['salary']}");
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Owner","class"=>"previewtitle","data"=>$this->data['ownerFullName'],"public"=>false,"other"=>false);
            
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Salary","class"=>"previewtitle","data"=>$this->data['salary'],"public"=>false,"other"=>false,'key'=>'salary','sql'=>"index.php?m=joborders&a=updateFieldData&field=salary&joborder_id={$this->data["joborder_id"]}&data={$this->data['salary']}");
            $AUIEO_PREVIEW_FIELD[]=array("caption"=>"Start Date","class"=>"previewtitle","data"=>$this->data['start_date'],"public"=>false,"other"=>false);
            //$AUIEO_PREVIEW_FIELD[]=array("caption"=>"Start Date","class"=>"previewtitle","data"=>$this->data['start_date'],"public"=>false,"other"=>false,'editable'=>'editable-bsdate','key'=>'start_date','sql'=>"index.php?m=joborders&a=updateFieldData&field=start_date&joborder_id={$this->data["joborder_id"]}&data={$this->data['start_date']}");
            
           $jsonRender=array();
            foreach($this->data as $k=>$v)
            {
                $jsonRender[$k]=$v;
            }
            
            $extraFieldData=array();
            for ($i = 0; $i < count($this->extraFieldRS); $i++)
            {
                $jsonRender["extra".$this->extraFieldRS[$i]["extraFieldSettingsID"]]=$this->extraFieldRS[$i]['display'];
                //$sql="select * from auieo_fields where id={$this->extraFieldRS["extraFieldSettingsID"]}";
                //$arrField=DatabaseConnection::getInstance()->getAssoc($sql);
                if($this->extraFieldRS[$i]["extraFieldType"]==8 || $this->extraFieldRS[$i]["extraFieldType"]<=4)
                    $AUIEO_PREVIEW_FIELD[]=array("caption"=>$this->extraFieldRS[$i]['fieldName'],"class"=>"previewtitle","data"=>$this->extraFieldRS[$i]['display'],"public"=>false,"other"=>false,'key'=>"extra".$this->extraFieldRS[$i]["extraFieldSettingsID"],'sql'=>"index.php?m=joborders&a=updateFieldData&field={$this->extraFieldRS[$i]['fieldName']}&joborder_id={$this->data["joborder_id"]}&data={$this->extraFieldRS[$i]['display']}");
                else
                    $AUIEO_PREVIEW_FIELD[]=array("caption"=>$this->extraFieldRS[$i]['fieldName'],"class"=>"previewtitle","data"=>$this->extraFieldRS[$i]['display'],"public"=>false,"other"=>false);
            }
            
            $AUIEO_JSON=  json_encode($jsonRender);
            /*if (isset($this->frozen)){ ?>
                <table style="font-weight:bold; border: 1px solid #000; background-color: #ffed1a; padding:5px; margin-bottom:7px;" width="<?php  if(false): ?>100%<?php else: ?>100%<?php endif; ?>" id="candidateAlreadyInSystemTable">
                    <tr>
                        <td class="tdVertical" style="width:100%;">
                            This Job Order is <?php $this->_($this->data['status']); ?> and can not be modified.
                           <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>
                               <a id="edit_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&a=edit&jobOrderID=<?php echo($this->jobOrderID); ?>">
                                   <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />&nbsp;Edit
                               </a>
                               the Job Order to make it Active.&nbsp;&nbsp;
                           <?php endif; ?>
                        </td>
                    </tr>
                </table>
            <?php }*/ ?>

            <table class="detailsOutside" width="100%" height="<?php echo((count($this->extraFieldRS)/2 + 12) * 22); ?>">
                <tr style="vertical-align:top;">
                    <td width="100%" height="100%">
                        <table class="detailsInside" height="100%">
                        <?php
 displayMultiColumnTable($AUIEO_PREVIEW_FIELD, 2);
                        
                  ?>


                        </table>
                    </td>
                </tr>
            </table>
            <?php if ($this->public): ?>
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
            <?php endif; 
            $this->subTemplate(dirname(__FILE__)."/AssignTagModal.php","AUIEO_TAG_UL");
            //include();
            ?>

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
                                                    <?php if (!isset($this->isPopup)): ?>
                                                        <?php if ($this->accessLevel >= ACCESS_LEVEL_DELETE): ?>
                                                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&a=deleteAttachment&jobOrderID=<?php echo($this->jobOrderID); ?>&attachmentID=<?php $this->_($attachmentsData['attachmentID']) ?>"  title="Delete" onclick="javascript:return confirm('Delete this attachment?');">
                                                                <img src="images/actions/delete.gif" alt="" width="16" height="16" border="0" />
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                    <?php if (!isset($this->isPopup)): ?>
                                        <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>
                                            <?php if (isset($this->attachmentLinkHTML)): ?>
                                                <?php echo($this->attachmentLinkHTML); ?>
                                            <?php else: ?>
                                                <a href="#" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&a=createAttachment&jobOrderID=<?php echo($this->jobOrderID); ?>', 400, 125, null); return false;">
                                            <?php endif; ?>
                                                <img src="images/paperclip_add.gif" width="16" height="16" border="0" alt="add attachment" class="absmiddle" />&nbsp;Add Attachment
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
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
<?php
$objRole=Users::getInstance()->getRole();
$allowDelete=$objRole->getModulePermission(400, JobOrders::actionMapping("delete"));
$allowEdit=$objRole->getModulePermission(400,  JobOrders::actionMapping("edit"));
if (!isset($this->isPopup)): ?>
            <div id="actionbar">
                <span style="float:left;">
                    <?php if ($allowEdit && $this->accessLevel >= ACCESS_LEVEL_EDIT): ?>
                        <a id="edit_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&a=edit&jobOrderID=<?php echo($this->jobOrderID); ?>">
                            <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />&nbsp;Edit
                        </a>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    <?php endif; ?>
                    <?php if ($allowDelete && $this->accessLevel >= ACCESS_LEVEL_DELETE): ?>
                        <a id="delete_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&a=delete&jobOrderID=<?php echo($this->jobOrderID); ?>" onclick="javascript:return confirm('Delete this job order?');">
                            <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;Delete
                        </a>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    <?php endif; ?>
                    <?php if ($this->accessLevel >= ACCESS_LEVEL_MULTI_SA): ?>
                        <?php if ($this->data['is_admin_hidden'] == 1): ?>
                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&a=administrativeHideShow&jobOrderID=<?php echo($this->jobOrderID); ?>&state=0">
                                <img src="images/resume_preview_inline.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;Administrative Show
                            </a>
                            <?php else: ?>
                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&a=administrativeHideShow&jobOrderID=<?php echo($this->jobOrderID); ?>&state=1">
                                <img src="images/resume_preview_inline.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;Administrative Hide
                            </a>
                        <?php endif; ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    <?php endif; ?>
                </span>
                <span style="float:right;">
                    <?php if (!empty($this->data['public']) && $this->careerPortalEnabled): ?>
                        <a id="public_link" href="<?php echo(CATSUtility::getAbsoluteURI()); ?>careers/<?php echo(CATSUtility::getIndexName()); ?>?p=showJob&ID=<?php echo($this->jobOrderID); ?>">
                            <img src="images/public.gif" width="16" height="16" class="absmiddle" alt="Online Application" border="0" />&nbsp;Online Application
                        </a>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    <?php endif; ?>
                    <?php /* TODO: Make report available for every site. */ ?>
                    <a id="report_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&a=customizeJobOrderReport&jobOrderID=<?php echo($this->jobOrderID); ?>">
                        <img src="images/reportsSmall.gif" width="16" height="16" class="absmiddle" alt="report" border="0" />&nbsp;Generate Report
                    </a>
                    <?php if ($this->privledgedUser): ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <a id="history_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=viewItemHistory&dataItemType=400&dataItemID=<?php echo($this->jobOrderID); ?>">
                            <img src="images/icon_clock.gif" width="16" height="16" class="absmiddle"  border="0" />&nbsp;View History
                        </a>
                    <?php endif; ?>
                </span>
            </div>
<?php endif; ?>
            <br clear="all" />
            <br />

            <p class="note">Candidate Pipeline</p>

            <p id="ajaxPipelineControl">
                Number of visible entries:&nbsp;&nbsp;
                <select id="numberOfEntriesSelect" onchange="PipelineJobOrder_changeLimit(<?php $this->_($this->data['joborder_id']); ?>, this.value, <?php if (isset($this->isPopup)) echo(1); else echo(0); ?>, 'ajaxPipelineTable', '<?php echo($this->sessionCookie); ?>', 'ajaxPipelineTableIndicator', '<?php echo(CATSUtility::getIndexName()); ?>');" class="selectBox">
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
                PipelineJobOrder_populate(<?php $this->_($this->data['joborder_id']); ?>, 0, <?php $this->_($this->pipelineEntriesPerPage); ?>, 'dateCreatedInt', 'desc', <?php if (isset($this->isPopup)) echo(1); else echo(0); ?>, 'ajaxPipelineTable', '<?php echo($this->sessionCookie); ?>', 'ajaxPipelineTableIndicator', '<?php echo(CATSUtility::getIndexName()); ?>');
            </script>

<?php if (!isset($this->isPopup)): ?>
            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT && empty($this->frozen)): ?>
                <a href="#" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&a=considerCandidateSearch&jobOrderID=<?php echo($this->jobOrderID); ?>', 820, 550, null); return false;">
                    <img src="images/consider.gif" width="16" height="16" class="absmiddle" alt="add candidate" border="0" />&nbsp;Add Candidate to This Job Order Pipeline
                </a>
            <?php endif; ?>

<?php endif; ?>
   
<?php 
$AUIEO_CONTENT=ob_get_clean(); ?>