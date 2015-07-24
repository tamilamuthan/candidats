<?php
pageHeaderInclude('modules/companies/validator.js');
pageHeaderInclude('js/searchSaved.js');
pageHeaderInclude('js/sweetTitles.js');
pageHeaderInclude('js/searchAdvanced.js');
pageHeaderInclude('js/highlightrows.js');
pageHeaderInclude('js/export.js');
pageTitle('Companies');
ob_start();
?>

<?php 
include_once("modules/companies/ClsCompaniesFilter.php");
?>
<p class="note">Search Companies</p>
<table class="searchTable" id="searchTable">
    <tr>
        <td>
            <form name="searchForm" id="searchForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get" autocomplete="off">
                <input type="hidden" name="m" id="moduleName" value="companies" />
                <input type="hidden" name="a" id="moduleAction" value="search" />
                <input type="hidden" name="getback" id="getback" value="getback" />

                <?php TemplateUtility::printSavedSearch($this->savedSearchRS); ?>

                <label id="searchModeLabel" for="searchMode">Search By:</label>&nbsp;
                <select id="searchMode" name="mode" onclick="advancedSearchConsider();" class="selectBox">
                    <option value="searchByName"<?php if ($this->mode == "searchByName"){ ?> selected<?php } ?>>Name</option>
                    <option value="searchByKeyTechnologies"<?php if ($this->mode == "searchByKeyTechnologies"){ ?> selected<?php } ?>>Key Technologies</option>
                </select>&nbsp;
                <input type="text" class="inputbox" id="searchText" name="wildCardString" value="<?php if (!empty($this->wildCardString)) echo(urldecode($this->wildCardString)); ?>" style="width:250px" />&nbsp;*&nbsp;
                <input type="submit" class="button" id="searchCompanies" name="searchCompanies" value="Search" />
                <?php TemplateUtility::printAdvancedSearch('searchByKeyTechnologies'); ?>
            </form>
        </td>
    </tr>
</table>

<script type="text/javascript">
    document.searchForm.wildCardString.focus();
</script>

<?php if ($this->isResultsMode){ ?>
    <br />
    <p class="note">Search Results (<?php echo(count($this->rs)); ?>)</p>
<?php
echo ClsCompaniesFilter::getInstance()->getFilter();
?>
    <?php if (!empty($this->rs)){ ?>
        <?php echo($this->exportForm['header']); ?>
        <table class="sortable" width="100%" onmouseover="javascript:trackTableHighlight(event)">
            <tr>
                <th>
                </th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('name', 'Name'); ?>
                </th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('phone1', 'Primary Phone'); ?>
                </th>
                <th align="left" nowrap="nowrap">Key Technologies</th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('city', 'Created'); ?>
                </th>
                <th align="left" nowrap="nowrap">
                    <?php $this->pager->printSortLink('owner_user.last_name', 'Owner'); ?>
                </th>
            </tr>

            <?php foreach ($this->rs as $rowNumber => $data): ?>
                <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                    <td valign="top" nowrap="nowrap">
                        <input type="checkbox" id="checked_<?php echo($data['companyID']); ?>" name="checked_<?php echo($data['companyID']); ?>" />
                        <a href="javascript:void(0);" onclick="window.open('<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php $this->_($data['companyID']); ?>')" title="View in New Window">
                            <img src="images/new_window.gif" alt="(Preview)" border="0" width="15" height="15" />
                        </a>
                    </td>
                    <td valign="top" align="left">
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php $this->_($data['companyID']); ?>" class="<?php $this->_($data['linkClass']); ?>">
                            <?php $this->_($data['name']); ?>
                        </a>
                    </td>
                    <td valign="top" align="left" nowrap="nowrap"><?php $this->_($data['phone1']); ?></td>
                    <td valign="top" align="left" nowrap="nowrap"><?php $this->_($data['keyTechnologies']); ?></td>
                    <td valign="top" align="left" nowrap="nowrap"><?php $this->_($data['dateCreated']); ?></td>
                    <td valign="top" align="left" nowrap="nowrap"><?php $this->_($data['ownerAbbrName']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php echo($this->exportForm['footer']); ?>
        <div style="float: right"><?php $this->pager->printNavigation('name'); ?></div>
        <?php echo($this->exportForm['menu']); ?>
    <?php }else{ ?>
        <p>No matching entries found.</p>
    <?php }
    }
$AUIEO_CONTENT=ob_get_clean(); ?>
