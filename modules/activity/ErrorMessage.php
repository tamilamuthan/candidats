<?php
pageHeaderInclude('js/highlightrows.js');
pageHeaderInclude('js/sweetTitles.js');
pageHeaderInclude('js/dataGrid.js');
pageTitle('Activities');
ob_start();

            if ($this->numActivities): ?>
            <table width="100%">
                <tr>
                    <td width="3%">
                        <img src="images/activities.gif" width="24" height="24" alt="Activities" style="border: none; margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Activities</h2></td>
                    <td align="right">
                        <?php $this->dataGrid->printNavigation(false); ?>&nbsp;&nbsp;<?php echo($this->quickLinks); ?>
                    </td>
                </tr>
            </table>

            <p class="note">
                <span style="float:left;">Activities - Page <?php echo($this->dataGrid->getCurrentPageHTML()); ?></span>
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

            <?php else: ?>

            <br /><br /><br /><br />

            <table cellpadding="0" cellspacing="0" border="0" width="956">
                <tr>
                <td style="padding-left: 62px;" align="center" valign="center">

                    <div style="text-align: center; width: 700px; line-height: 22px; font-size: 18px; font-weight: bold; color: #666666; padding-bottom: 20px;">
                    Activities are automatically recorded based on actions you perform.
                    </div>
                </td>

                </tr>
            </table>

            <?php endif; 
        
$AUIEO_CONTENT=ob_get_clean();

?>