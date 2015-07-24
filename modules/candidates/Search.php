<?php
        pageHeaderInclude('modules/candidates/validator.js');
        pageHeaderInclude('js/searchSaved.js');
        pageHeaderInclude('js/export.js');
        pageHeaderInclude('js/searchAdvanced.js');
        pageHeaderInclude('js/highlightrows.js');
        pageHeaderInclude('js/sweetTitles.js');
        
        pageTitle('Candidates');
        ob_start();
        
include_once("modules/candidates/ClsCandidateFilter.php");
?>
<p class="note">Search Candidates</p>

<table class="searchTable" id="searchTable ">
    <tr>
        <td>
            <form name="searchForm" id="searchForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get" autocomplete="off">
                <input type="hidden" name="m" id="moduleName" value="candidates" />
                <input type="hidden" name="a" id="moduleAction" value="search" />
                <input type="hidden" name="getback" id="getback" value="getback" />

                <?php TemplateUtility::printSavedSearch($this->savedSearchRS); ?>
<input type="checkbox" name="bulk_resume" value="1" <?php echo (isset($_REQUEST["bulk_resume"]) && $_REQUEST["bulk_resume"]) ? "checked":""  ?> /> Search Bulk Resume<br />
                <label id="searchModeLabel" for="searchMode">Search By:</label>&nbsp;
                <select id="searchMode" name="mode" onclick="advancedSearchConsider();" class="selectBox">
                    <option value="searchByFullName"<?php if ($this->mode == "searchByFullName"): ?> selected<?php endif; ?>>Candidate Name</option>
                    <option value="searchByResume"<?php if ($this->mode == "searchByResume" || empty($this->mode)): ?> selected<?php endif; ?>>Resume Keywords</option>
                    <option value="searchByKeySkills"<?php if ($this->mode == "searchByKeySkills"): ?> selected<?php endif; ?>>Key Skills</option>
                    <option value="phoneNumber"<?php if ($this->mode == "phoneNumber"): ?> selected<?php endif; ?>>Phone Number</option>
                </select>&nbsp;
                <input type="text" class="inputbox" id="searchText" name="wildCardString" value="<?php if (!empty($this->wildCardString)) $this->_($this->wildCardString); ?>" style="width:250px" />&nbsp;*&nbsp;
                <input type="submit" class="button" id="searchCandidates" name="searchCandidates" value="Search" />
                <?php TemplateUtility::printAdvancedSearch('searchByKeySkills,searchByResume'); ?>
            </form>
        </td>
    </tr>
</table>

<script type="text/javascript">
    document.searchForm.wildCardString.focus();
    function sendEmail()
    {
            if(jQuery(".record_checkbox:checked").size())
            {
                var items="";
                var tmp="";
                jQuery(".record_checkbox:checked").each(function ()
                {
                    if(items=="")
                    {
                        tmp=jQuery(this).attr("name").split("_");
                        items=tmp[1];
                    }
                    else
                    {
                        tmp=jQuery(this).attr("name").split("_");
                        items=items+","+tmp[1];
                    }
                });
                window.location="index.php?m=candidates&a=emailCandidates&idlist=" + items;
            }
            else
            {
                alert("No item selected to add");
            }
    }
</script>
<?php if ($this->isResumeMode && $this->isResultsMode): ?>
    <br />
    <?php if (!empty($this->rs)): ?>
        <p class="note">Search Results &nbsp;<?php $this->_($this->pageStart); ?> to <?php $this->_($this->pageEnd); ?> of <?php $this->_($this->totalResults); ?></p>

    <?php else: ?>
        <p class="note">Search Results</p>
    <?php endif; ?>
<?php
echo ClsCandidateFilter::getInstance()->getFilter();
echo($this->exportForm['header']);
?>
    <table class="sortable" width="100%">
        <thead>
            <tr>
                <th nowrap>&nbsp;</th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('firstName', 'First Name'); ?>
                </th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('lastName', 'Last Name'); ?>
                </th>
                <th align="left" nowrap="nowrap">Resume</th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('city', 'City'); ?>
                </th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('state', 'State'); ?>
                </th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('dateCreatedSort', 'Created'); ?>
                </th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('dateModifiedSort', 'Modified'); ?>
                </th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('ownerSort', 'Owner'); ?>
                </th>
            </tr>
        </thead>

        <?php if (!empty($this->rs)): ?>
            <?php foreach ($this->rs as $rowNumber => $data): ?>
                <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                    <?php if ($data['candidateID'] > 0): ?>
                        <td valign="top" nowrap>
                            <input class="record_checkbox" type="checkbox" id="checked_<?php echo($data['candidateID']); echo($data['attachmentID']); ?>" name="checked_<?php echo($data['candidateID']); ?>" />
                            <a href="javascript:void(0);" onClick="window.open('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>')" title="View in New Window">
                                <img src="images/new_window.gif" class="abstop" alt="(Preview)" border="0" width="15" height="15" />
                            </a>
                        </td>
                        <td valign="top">
                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>">
                                <?php $this->_($data['firstName']); ?>
                            </a>
                        </td>
                        <td valign="top">
                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>">
                                <?php $this->_($data['lastName']); ?>
                            </a>
                        </td>
                    <?php else: ?>
                        <td>&nbsp;</td>
                        <td valign="top" nowrap="nowrap">
                            <?php
                            $arrUrl=array();
                            foreach($_GET as $k=>$v)
                            {
                                if($k=="attachmentID") continue;
                                if($k=="getback") continue;
                                if($k=="m")
                                {
                                    $k="return_m";
                                }
                                else if($k=="a")
                                {
                                    $k="return_a";
                                }
                                $arrUrl[]="{$k}={$v}";
                            }
                            $url=implode("&",$arrUrl);
                            ?>
                            <a href='<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=delete&amp;<?php echo $url; ?>&amp;attachmentID=<?php $this->_($data['attachmentID']); ?>'>Delete</a>
                        </td>
                        <td valign="top" colspan="2">
                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=add&amp;<?php echo $url; ?>&amp;attachmentID=<?php $this->_($data['attachmentID']); ?>">
                                <img src="images/candidate_tiny.gif" width="16" height="16" border="0" class="absmiddle" alt="" title="Create Candidate Profile" />
                            </a>
                            &nbsp;Bulk Resume
                        </td>
                    <?php endif; ?>
                    <td valign="top">
                        <a href="#" onclick="window.open('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=viewResume&amp;wildCardString=<?php $this->_(urlencode($this->wildCardString)); ?>&amp;attachmentID=<?php $this->_($data['attachmentID']); ?>', 'viewResume', 'scrollbars=1,width=700,height=600')">
                            <img src="images/resume_preview_inline.gif" class="abstop" alt="(Preview)" border="0" width="15" height="15" />
                        </a>&nbsp;
                        <?php echo($data['excerpt']); ?>
                    </td>
                    <td valign="top"><?php $this->_($data['city']); ?></td>
                    <td valign="top"><?php $this->_($data['state']); ?></td>
                    <td valign="top"><?php $this->_($data['dateCreated']); ?></td>
                    <td valign="top"><?php $this->_($data['dateModified']); ?></td>
                    <td valign="top" nowrap="nowrap"><?php $this->_($data['ownerAbbrName']); ?>&nbsp;</td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No matching entries found.</td>
            </tr>
        <?php endif; ?>
    </table>
    <?php echo($this->exportForm['footer']); ?>
    <?php echo($this->exportForm['menu']); ?>
    <?php if (!empty($this->rs)): ?>
        <div style="float: right"><?php $this->pager->printNavigation(); ?></div>
        <br />
    <?php endif; ?>
<?php elseif ($this->isResultsMode): ?>
    <br />
    <p class="note">Search Results (<?php echo(count($this->rs)); ?>)</p>
<?php
echo ClsCandidateFilter::getInstance()->getFilter();
?>
    <?php if (!empty($this->rs)): ?>
        <?php echo($this->exportForm['header']); ?>
        <table class="sortable" width="100%" onmouseover="javascript:trackTableHighlight(event)">
            <tr>
                <th nowrap>&nbsp;</th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('firstName', 'First Name'); ?>
                </th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('lastName', 'Last Name'); ?>
                </th>
                <th align="left" nowrap="nowrap">Key Skills</th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('city', 'City'); ?>
                </th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('state', 'State'); ?>
                </th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('dateCreated', 'Created'); ?>
                </th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('dateModified', 'Modified'); ?>
                </th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('owner_user.last_name', 'Owner'); ?>
                </th>
            </tr>

            <?php foreach ($this->rs as $rowNumber => $data): ?>
                <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                    <td nowrap>
                        <input class="record_checkbox" type="checkbox" id="checked_<?php echo($data['candidateID']); ?>" name="checked_<?php echo($data['candidateID']); ?>" />
                        <a href="javascript:void(0);" onClick="window.open('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>')" title="View in New Window">
                            <img src="images/new_window.gif" class="abstop" alt="(Preview)" border="0" width="15" height="15" />
                        </a>&nbsp;
                    </td>
                    <td>
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>">
                            <?php $this->_($data['firstName']); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>">
                            <?php $this->_($data['lastName']); ?>
                        </a>
                    </td>
                    <td>
                        <?php if (isset($data['resumeID'])): ?>
                            <a href="javascript:void(0);" onclick="window.open('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=viewResume&amp;wildCardString=<?php $this->_(urlencode($this->wildCardString)); ?>&amp;attachmentID=<?php $this->_($data['resumeID']); ?>', 'viewResume', 'scrollbars=1,width=700,height=600')" Title="View resume">
                                <img src="images/resume_preview_inline.gif" class="abstop" alt="(Preview)" border="0" width="15" height="15" />
                            </a>
                        <?php endif; ?>
                        <?php $this->_($data['keySkills']); ?>&nbsp;
                    </td>
                    <td><?php $this->_($data['city']); ?>&nbsp;</td>
                    <td><?php $this->_($data['state']); ?>&nbsp;</td>
                    <td><?php $this->_($data['dateCreated']); ?>&nbsp;</td>
                    <td><?php $this->_($data['dateModified']); ?>&nbsp;</td>
                    <td nowrap="nowrap"><?php $this->_($data['ownerAbbrName']); ?>&nbsp;</td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php echo($this->exportForm['footer']); ?>
        <?php echo($this->exportForm['menu']); ?>
    <?php else: ?>
        <p>No matching entries found.</p>
    <?php endif; ?>
<?php endif;
$AUIEO_CONTENT=ob_get_clean(); ?>
