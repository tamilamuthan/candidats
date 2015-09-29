<?php

pageHeaderInclude('js/highlightrows.js');
pageHeaderInclude('js/sweetTitles.js');
pageHeaderInclude('js/export.js');
pageHeaderInclude('js/dataGrid.js');
pageTitle('Lists');
ob_start();
if ($this->dataGrid->getNumberOfRows()): ?>
            <table width="100%">
                <tr>
                    <td width="3%">
                        <img src="images/job_orders.gif" width="24" height="24" border="0" alt="Job Orders" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Lists: Home</h2></td>
                    <td align="right">
                        <form name="jobOrdersViewSelectorForm" id="jobOrdersViewSelectorForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get">
                            <input type="hidden" name="m" value="joborders" />
                            <input type="hidden" name="a" value="list" />

                            <table class="viewSelector">
                                <tr>
                                    <td>
                                    </td>
                                 </tr>
                            </table>
                        </form>
                    </td>
                </tr>
            </table>

            <p class="note">
                <span style="float:left;">Lists  -
                    Page <?php echo($this->dataGrid->getCurrentPageHTML()); ?> (<?php echo($this->dataGrid->getNumberOfRows()); ?> Items)
                </span>
                <span style="float:right;">
                    <?php $this->dataGrid->drawRowsPerPageSelector(); ?>
                </span>&nbsp;
            </p>

            <?php $this->dataGrid->drawFilterArea(); ?>
            <?php $this->dataGrid->draw();  ?>

            <div style="display:block;">
                <span style="float:left;">

                </span>
                <span style="float:right;">
                    <?php $this->dataGrid->printNavigation(true); ?>
                </span>&nbsp;
            </div>
            <?php else: ?>

            <br /><br /><br /><br />

            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                <td style="padding-left: 62px;" align="center" valign="center">

                    <div style="text-align: center; width: 600px; line-height: 22px; font-size: 18px; font-weight: bold; color: #666666; padding-bottom: 20px;">
                    Create lists to group candidates, job orders, companies and contacts and perform actions on them quickly.
                    <br /><br />
                    <span style="font-size: 14px; font-weight: normal;">
                    Create lists from the <b>job orders, candidates, companies </b>or<b> contacts</b> tab.
                    </span>
                    </div>
                </td>

                </tr>
            </table>

            <?php endif; 
			$AUIEO_CONTENT=ob_get_clean();
			?>

        