<?php /* $Id: ChangePassword.tpl 1927 2007-02-22 06:03:24Z will $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'js/sorttable.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php 
if(isset($this->active))
{
    TemplateUtility::printTabs($this->active, $this->subActive); 
}
?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: Customize Filter</h2></td>
                </tr>
            </table>

            <p class="note">Show Grouping</p>

            <form name="showgrouping" id="showgrouping" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=filtergrouping" method="post">
                <input type="hidden" name="postback" id="postback" value="postback" />

                <?php if (isset($this->isDemoUser) && $this->isDemoUser): ?>
                    Note that as a demo user, you do not have privileges to modify any settings.
                    <br /><br />
                <?php endif; ?>

                <table class="searchTable">
                    <tr>
                        <td colspan="2">
                            <span class="bold">Show Grouping</span>
                            <br />
                            <br />
                            <span id='passwordErrorMessage' style="font:smaller; color: red">
                                <?php if (isset($this->errorMessage)): ?>
                                        <?php $this->_($this->errorMessage); ?>
                                <?php endif; ?>
                            </span>
                        </td>
                    </tr>


                    <tr>
                        <td>
                            <label id="groupingLabel" for="grouping">Grouping:</label>&nbsp;
                        </td>
                        <td>
                            <input type="checkbox" class="inputbox" id="grouping" name="grouping" <?php echo $this->checked; ?> />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <br />
                            <input type="submit" class="button" id="butGrouping" name="butGrouping" value="Grouping" />
                            <input type="reset"  class="button" id="reset"          name="reset"          value="Reset" />
                            <input type="button" name="back" class = "button" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings';" />
                       </td>
                    </tr>
                </table>
            </form>

        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>
