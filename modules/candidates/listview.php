<?php

pageHeaderInclude('js/highlightrows.js');
pageHeaderInclude('js/export.js');
pageHeaderInclude('js/dataGrid.js');
pageTitle('Candidates');
ob_start();
 ?>
<table width="100%">
    <tr>
        <td width="3%">
            
        </td>
        <td></td>
        <td align="right">
            <form name="candidatesViewSelectorForm" id="candidatesViewSelectorForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get">
                <input type="hidden" name="m" value="candidates" />
                <input type="hidden" name="a" value="listByView" />

                <table class="viewSelector">
                    <tr>
                        <td valign="top" align="right" nowrap="nowrap">
                            <?php $this->dataGrid->printNavigation(false); ?>
                        </td>
                        <td valign="top" align="right" nowrap="nowrap">
                            <input type="checkbox" name="onlyMyCandidates" id="onlyMyCandidates" <?php if ($this->dataGrid->getFilterValue('OwnerID') ==  $this->userID): ?>checked<?php endif; ?> onclick="<?php echo $this->dataGrid->getJSAddRemoveFilterFromCheckbox('OwnerID', '==',  $this->userID); ?>" />
                            Only My Candidates&nbsp;
                        </td>
                        <td valign="top" align="right" nowrap="nowrap">
                            <input type="checkbox" name="onlyHotCandidates" id="onlyHotCandidates" <?php if ($this->dataGrid->getFilterValue('IsHot') == '1'): ?>checked<?php endif; ?> onclick="<?php echo $this->dataGrid->getJSAddRemoveFilterFromCheckbox('IsHot', '==', '\'1\''); ?>" />
                            <label for="onlyHotCandidates">Only Hot Candidates</label>&nbsp;
                        </td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>
</table>

<?php if ($this->topLog != ''): ?>
<div style="margin: 20px 0px 20px 0px;">
    <?php echo $this->topLog; ?>
</div>
<?php endif; ?>



<p class="note">
    <span style="float:left;">Candidates - Page <?php echo($this->dataGrid->getCurrentPageHTML()); ?> (<?php echo($this->dataGrid->getNumberOfRows()); ?> Items)</span>
    <span style="float:right;">
        <?php $this->dataGrid->drawRowsPerPageSelector(); ?>
        <?php $this->dataGrid->drawShowFilterControl(); ?>
    </span>&nbsp;
</p>

<?php $this->dataGrid->drawFilterArea(); ?>
<?php $this->dataGrid->draw();  ?>

<div style="display:block;">
    <span style="float:left;">
        <?php $this->dataGrid->printActionArea(); ?>
    </span>
    <span style="float:right;">
        <?php $this->dataGrid->printNavigation(true); ?>
    </span>&nbsp;
</div>





<?php 
$AUIEO_CONTENT=ob_get_clean();
?>
