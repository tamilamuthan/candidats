<?php

pageHeaderInclude('modules/joborders/validator.js');
pageHeaderInclude('js/sweetTitles.js');
pageHeaderInclude('js/searchAdvanced.js');
pageHeaderInclude('js/highlightrows.js');
pageHeaderInclude('js/export.js');
pageHeaderInclude('js/searchSaved.js');
pageTitle('Job Orders');
ob_start();
?>
<?php 
include_once("modules/joborders/ClsJobOrdersFilter.php");
?>
            <p class="note">Search Job Orders</p>

            <table class="searchTable" id="searchTable">
                <tr>
                    <td>
                        <form name="searchForm" id="searchForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get" autocomplete="off">
                            <input type="hidden" name="m" id="moduleName" value="joborders" />
                            <input type="hidden" name="a" id="moduleAction" value="search" />
                            <input type="hidden" name="getback" id="getback" value="getback" />

                            <?php TemplateUtility::printSavedSearch($this->savedSearchRS); ?>

                            <label id="searchModeLabel" for="searchMode">Search By:</label>&nbsp;
                            <select id="searchMode" name="mode" onclick="advancedSearchConsider();" class="selectBox">
                                <option value="searchByJobTitle"<?php if ($this->mode == "searchByJobTitle"): ?> selected="selected"<?php endif; ?>>Job Title</option>
                                <option value="searchByCompanyName"<?php if ($this->mode == "searchByCompanyName"): ?> selected="selected"<?php endif; ?>>Company Name</option>
                            </select>&nbsp;
                            <input type="text" class="inputbox" id="searchText" name="wildCardString" value="<?php if (!empty($this->wildCardString)) echo(urldecode($this->wildCardString)); ?>" style="width:250px" />&nbsp;*&nbsp;
                            <input type="submit" class="button" id="searchJobOrders" name="searchJobOrders" value="Search" />
                            <?php TemplateUtility::printAdvancedSearch('searchByKeySkills,searchByResume'); ?>
                        </form>
                    </td>
                </tr>
            </table>

            <script type="text/javascript">
                document.searchForm.wildCardString.focus();
            </script>

            <?php if ($this->isResultsMode): ?>
                <br />
                <p class="note">Search Results</p>
<?php

echo ClsJobOrdersFilter::getInstance()->getFilter();
?>
                <?php if (!empty($this->rs)): ?>
                    <?php echo($this->exportForm['header']); ?>

                    <table class="sortable" width="100%" onmouseover="javascript:trackTableHighlight(event)">
                        <tr>
                            <th></th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('title', 'Title'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('companyName', 'Company'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('type', 'Type'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('status', 'Status'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('dateCreated', 'Created'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('startDate', 'Start'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('recruiterLastName', 'Recruiter'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('owner_user.last_name', 'Owner'); ?>
                            </th>
                        </tr>

                        <?php foreach ($this->rs as $rowNumber => $data): ?>
                            <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                                <td valign="top" nowrap="nowrap">
                                    <input type="checkbox" id="checked_<?php echo($data['jobOrderID']); ?>" name="checked_<?php echo($data['jobOrderID']); ?>" />
                                     <a href="javascript:void(0);" onclick="window.open('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php $this->_($data['jobOrderID']); ?>')" title="View in New Window">
                                        <img src="images/new_window.gif" alt="(Preview)" border="0" width="15" height="15" />
                                    </a>
                                </td>
                                <td valign="top">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php $this->_($data['jobOrderID']); ?>" class="<?php $this->_($data['linkClass']); ?>">
                                        <?php $this->_($data['title']); ?>
                                    </a>
                                </td>
                                <td valign="top">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php $this->_($data['companyID']); ?>">
                                        <?php $this->_($data['companyName']); ?>
                                    </a>
                                </td>
                                <td align="left" valign="top"><?php $this->_($data['type']); ?>&nbsp;</td>
                                <td align="left" valign="top"><?php $this->_($data['status']); ?>&nbsp;</td>
                                <td align="left" valign="top"><?php $this->_($data['dateCreated']); ?>&nbsp;</td>
                                <td align="left" valign="top"><?php $this->_($data['startDate']); ?>&nbsp;</td>
                                <td align="left" valign="top" nowrap="nowrap"><?php $this->_($data['recruiterAbbrName']); ?>&nbsp;</td>
                                <td align="left" valign="top" nowrap="nowrap"><?php $this->_($data['ownerAbbrName']); ?>&nbsp;</td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php echo($this->exportForm['footer']); ?>
                    <?php echo($this->exportForm['menu']); ?>
                <?php else: ?>
                    <p>No matching entries found.</p>
                <?php endif; ?>
            <?php endif; ?>
    <?php $AUIEO_CONTENT=ob_get_clean(); ?>