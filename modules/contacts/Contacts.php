
<?php
pageHeaderInclude('js/highlightrows.js');
pageHeaderInclude('js/export.js');
pageHeaderInclude('js/dataGrid.js');
pageTitle('Contacts');

ob_start();
$this->dataGrid->printNavigation(true);
$AUIEO_PRINT_NAVIGATION=  ob_get_clean(); 

ob_start();
$this->dataGrid->printActionArea();
$AUIEO_PRINT_ACTION_AREA=ob_get_clean();

$AUIEO_REMOVE_FILTER=$this->dataGrid->getJSAddRemoveFilterFromCheckbox('OwnerID', '==',  $this->userID);

ob_start();
if ($this->dataGrid->getFilterValue('OwnerID') ==  $this->userID): ?>checked<?php endif;

$AUIEO_FILTER_VALUE_CHECKED=ob_get_clean(); 

ob_start(); ?>
<form name="contactsViewSelectorForm" id="contactsViewSelectorForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get">
    <input type="hidden" name="m" value="contacts" />
    <input type="hidden" name="a" value="listByView" />

    <table class="viewSelector">
        <tr>
            <td valign="top" align="right" nowrap="nowrap">
                <?php $this->dataGrid->printNavigation(false); ?>
            </td>
            <td valign="top" align="right" nowrap="nowrap">
                <input type="checkbox" name="onlyMyCompanies" id="onlyMyContacts" <?php echo $AUIEO_FILTER_VALUE_CHECKED; ?> onclick="<?php echo $AUIEO_REMOVE_FILTER; ?>" />
                <label for="onlyMyContacts">Only My Contacts</label>&nbsp;
            </td>
            <td valign="top" align="right" nowrap="nowrap">
                <input type="checkbox" name="onlyHotCompanies" id="onlyHotContacts" <?php if ($this->dataGrid->getFilterValue('IsHot') == '1'): ?>checked<?php endif; ?> onclick="<?php echo $this->dataGrid->getJSAddRemoveFilterFromCheckbox('IsHot', '==', '\'1\''); ?>" />
                <label for="onlyHotContacts">Only Hot Contacts</label>&nbsp;
            </td>
        </tr>
    </table>
</form>

            <p class="note">
                <span style="float:left;">
                    Contacts - Page <?php echo($this->dataGrid->getCurrentPageHTML()); ?>
                    (<?php echo($this->dataGrid->getNumberOfRows()); ?> Items)
                    <?php if ($this->dataGrid->getFilterValue('OwnerID') ==  $this->userID): ?>(Only My Contacts)<?php endif; ?>
                    <?php if ($this->dataGrid->getFilterValue('IsHot') == '1'): ?>(Only Hot Contacts)<?php endif; ?>
                </span>
                <span style="float:right;">
                    <?php $this->dataGrid->drawRowsPerPageSelector(); ?>
                    <?php $this->dataGrid->drawShowFilterControl(); ?>
                </span>&nbsp;
            </p>

            <?php $this->dataGrid->drawFilterArea(); ?>
            <?php $this->dataGrid->draw();  ?>

            

            

            <?php 
$AUIEO_CONTENT=ob_get_clean();
			?>

       