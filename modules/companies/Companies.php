<?php
pageHeaderInclude('js/highlightrows.js');
pageHeaderInclude('js/export.js');
pageHeaderInclude('js/dataGrid.js');
pageTitle('Companies');
ob_start();
 ?>
<form name="companiesViewSelectorForm" id="companiesViewSelectorForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get">
    <input type="hidden" name="m" value="companies" />
    <input type="hidden" name="a" value="listByView" />
    <table class="viewSelector">
        <tr>
            <td valign="top" align="right" nowrap="nowrap">
                <?php $this->dataGrid->printNavigation(false); ?>
            </td>
            <td valign="top" align="right" nowrap="nowrap">
                <input type="checkbox" name="onlyMyCompanies" id="onlyMyCompanies" <?php if ($this->dataGrid->getFilterValue('OwnerID') ==  $this->userID): ?>checked<?php endif; ?> onclick="<?php echo $this->dataGrid->getJSAddRemoveFilterFromCheckbox('OwnerID', '==',  $this->userID); ?>" />
                <label for="onlyMyCompanies">Only My Companies</label>&nbsp;
            </td>
            <td valign="top" align="right" nowrap="nowrap">
                <input type="checkbox" name="onlyHotCompanies" id="onlyHotCompanies" <?php if ($this->dataGrid->getFilterValue('IsHot') == '1'): ?>checked<?php endif; ?> onclick="<?php echo $this->dataGrid->getJSAddRemoveFilterFromCheckbox('IsHot', '==', '\'1\''); ?>" />
                <label for="onlyHotCompanies">Only Hot Companies</label>&nbsp;
            </td>
        </tr>
    </table>
</form>
<?php if ($this->errMessage != ''): ?>
<div id="errorMessage" style="padding: 25px 0px 25px 0px; border-top: 1px solid #800000; border-bottom: 1px solid #800000; background-color: #f7f7f7;margin-bottom: 15px;">
<table>
    <tr>
        <td align="left" valign="center" style="padding-right: 5px;">
            <img src="images/large_error.gif" align="left">
        </td>
        <td align="left" valign="center">
            <span style="font-size: 12pt; font-weight: bold; color: #800000; line-height: 12pt;">There was a problem with your request:</span>
            <div style="font-size: 10pt; font-weight: bold; padding: 3px 0px 0px 0px;"><?php echo $this->errMessage; ?></div>
        </td>
    </tr>
</table>
</div>
</div>
<?php endif; ?>

<p class="note">
    <span style="float:left;">Companies  -
        Page <?php echo($this->dataGrid->getCurrentPageHTML()); ?>
        (<?php echo($this->dataGrid->getNumberOfRows()); ?> Items)
        <?php if ($this->dataGrid->getFilterValue('OwnerID') ==  $this->userID): ?>(Only My Companies)<?php endif; ?>
        <?php if ($this->dataGrid->getFilterValue('IsHot') == '1'): ?>(Only Hot Companies)<?php endif; ?>
    </span>
    <span style="float:right;">
        <?php $this->dataGrid->drawRowsPerPageSelector(); ?>
        <?php $this->dataGrid->drawShowFilterControl(); ?>
    </span>&nbsp;
</p>

<?php $this->dataGrid->drawFilterArea(); ?>
<?php $this->dataGrid->draw();  ?>

<div style="display:block;">
    <span style="float:left;">
        <?php $this->dataGrid->printActionArea(); ?>&nbsp;
    </span>
    <span style="float:right;">
        <?php $this->dataGrid->printNavigation(true); ?>
    </span>&nbsp;
</div>
<?php $AUIEO_CONTENT=ob_get_clean(); ?>
