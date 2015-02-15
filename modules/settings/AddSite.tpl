<?php /* $Id: AddSite.tpl 3810 2007-12-05 19:13:25Z brian $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'js/sorttable.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%" valign="bottom">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td valign="bottom"><h2>Settings: Add Site</h2></td>
                </tr>
            </table>

            <p class="note">
                <span style="float: left;">Add Site</span>
                <span style="float: right;"><a href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=manageUsers'>Back to User Management</a></span>&nbsp;
            </p>

            <form name="addSiteForm" id="addSiteForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=addSite" method="post" onsubmit="return checkAddSiteForm(document.addSiteForm);" autocomplete="off">
                <input type="hidden" name="postback" id="postback" value="postback" />

                <table width="930">
                    <tr>
                        <td align="left" valign="top">
                            <table class="editTable" width="550">
                                <tr>
                                    <td class="tdVertical">
                                        <label id="siteNameLabel" for="siteName">Site Name:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" class="inputbox" id="siteName" name="siteName" style="width: 150px;" />&nbsp;*
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="lastNameLabel" for="unixName">Unix Name:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" class="inputbox" id="unixName" name="unixName" style="width: 150px;" />&nbsp;*
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="emailLabel" for="isDemo">Is Demo:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="checkbox" class="inputbox" id="isDemo" name="isDemo" value="1" />
                                    </td>
                                </tr>


                                <?php if($this->EEOSettingsRS['enabled'] == 1): ?>
                                     <tr>
                                        <td class="tdVertical">Allowed to view EEO Information:</td>
                                        <td class="tdData">
                                            <span id="eeoIsVisibleCheckSpan">
                                                <input type="checkbox" name="eeoIsVisible" id="eeoIsVisible" onclick="if (this.checked) document.getElementById('eeoVisibleSpan').style.display='none'; else document.getElementById('eeoVisibleSpan').style.display='';">
                                                &nbsp;This user is <span id="eeoVisibleSpan">not </span>allowed to edit and view candidate's EEO information.
                                            </span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </td>

                    </tr>
                </table>

                <input type="submit" class="button" name="submit" id="submit" value="Add Site" />&nbsp;
                <input type="reset"  class="button" name="reset"  id="reset"  value="Reset" />&nbsp;
                <input type="button" class="button" name="back"   id="back"   value="Cancel" onclick="javascript:goToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=manageSites');" />
            </form>
        </div>
    </div>

    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>
