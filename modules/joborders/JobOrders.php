<?php
pageHeaderInclude('js/highlightrows.js');
pageHeaderInclude('js/sweetTitles.js');
pageHeaderInclude('js/export.js');
pageHeaderInclude('js/dataGrid.js');
pageTitle('Job Orders');
ob_start();
if ($this->totalJobOrders){ ?>
            <table width="100%">
                <tr>
                    <td width="3%">
                        
                    </td>
                    <td></td>

                    <td align="right">
                        <form name="jobOrdersViewSelectorForm" id="jobOrdersViewSelectorForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get">
                            <input type="hidden" name="m" value="joborders" />
                            <input type="hidden" name="a" value="list" />

                            <table class="viewSelector">
                                <tr>
                                    <td>
                                        <select name="view" id="view" onchange="<?php echo($this->dataGrid->getJSAddFilter('Status', '==', 'this.value', 'true')); ?>" class="selectBox">
                                            <option value="Active / OnHold / Full"<?php if ($this->dataGrid->getFilterValue('Status') == 'Active / OnHold / Full'): ?> selected="selected"<?php endif; ?>>Active / On Hold / Full</option>
                                            <option value="Active"<?php if ($this->dataGrid->getFilterValue('Status') == 'Active'): ?> selected="selected"<?php endif; ?>>Active</option>
                                            <option value="OnHold / Full"<?php if ($this->dataGrid->getFilterValue('Status') == 'OnHold / Full'): ?> selected="selected"<?php endif; ?>>On Hold / Full</option>
                                            <option value="Closed / Canceled"<?php if ($this->dataGrid->getFilterValue('Status') == 'Closed / Canceled'): ?> selected="selected"<?php endif; ?>>Closed / Canceled</option>
                                            <option value="Upcoming / Lead"<?php if ($this->dataGrid->getFilterValue('Status') == 'Upcoming / Lead'): ?> selected="selected"<?php endif; ?>>Upcoming / Lead</option>
                                            <option value=""<?php if ($this->dataGrid->getFilterValue('Status') == ''): ?> selected="selected"<?php endif; ?>>All</option>
                                        </select>
                                    </td>

                                    <td valign="top" align="right" nowrap="nowrap">
                                        <input type="checkbox" name="onlyMyJobOrders" id="onlyMyJobOrders" <?php if ($this->dataGrid->getFilterValue('OwnerID') ==  $this->userID): ?>checked<?php endif; ?> onclick="<?php echo $this->dataGrid->getJSAddRemoveFilterFromCheckbox('OwnerID', '==',  $this->userID); ?>" />
                                        <label for="onlyMyJobOrders">Only My Job Orders</label>&nbsp;

                                    </td>
                                    <td valign="top" align="right" nowrap="nowrap">
                                        <input type="checkbox" name="onlyHotJobOrders" id="onlyHotJobOrders" <?php if ($this->dataGrid->getFilterValue('IsHot') == '1'): ?>checked<?php endif; ?> onclick="<?php echo $this->dataGrid->getJSAddRemoveFilterFromCheckbox('IsHot', '==', '\'1\''); ?>" />
                                        <label for="onlyHotJobOrders">Only Hot Job Orders</label>&nbsp;
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </td>
                </tr>
            </table>
<?php } ?>

            <?php if ($this->errMessage != ''){ ?>
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
            <?php } ?>

            <?php if ($this->totalJobOrders){ ?>
            <p class="note">
                <span style="float:left;">Job Orders  -
                    Page <?php echo($this->dataGrid->getCurrentPageHTML()); ?>
                    (<?php echo($this->dataGrid->getNumberOfRows()); ?> Items)
                    (<?php if ($this->dataGrid->getFilterValue('Status') != '') echo ($this->dataGrid->getFilterValue('Status')); else echo ('All'); ?>)
                    <?php if ($this->dataGrid->getFilterValue('OwnerID') ==  $this->userID): ?>(Only My Job Orders)<?php endif; ?>
                    <?php if ($this->dataGrid->getFilterValue('IsHot') == '1'): ?>(Only Hot Job Orders)<?php endif; ?>
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
            <?php }else{ ?>

            <br /><br /><br /><br />
            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                <td style="padding-left: 62px;" align="center" valign="center">

                    <div style="text-align: center; width: 600px; line-height: 22px; font-size: 18px; font-weight: bold; color: #666666; padding-bottom: 20px;">
                    Add a job order, then attach candidates
                    to the pipeline with their status (interviewing, qualifying, etc.)
                    </div>

                    <a href="javascript:void(0);"  onclick="showPopWin('<?php echo CATSUtility::getIndexName(); ?>?m=joborders&amp;a=addJobOrderPopup', 400, 250, null);">
                    <div class="addJobOrderButton">&nbsp;</div>
                    </a>
                </td>

                </tr>
            </table>

            <?php } 
			$AUIEO_CONTENT=ob_get_clean();
			?>
       
