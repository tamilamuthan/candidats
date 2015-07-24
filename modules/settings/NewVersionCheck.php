<?php
pageHeaderInclude('modules/settings/validator.js');

pageTitle('Settings');

ob_start();
?>


        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: Administration</h2></td>
                </tr>
            </table>

            <p class="note">New Version</p>

            <table class="searchTable" width="100%">
                <tr>
                    <td>
                        <?php if ($this->versionCheckPref): ?>
                            <p>
                                Your current version is: <span class="bold"><?php echo(CATSUtility::getVersion()); ?> Build <?php echo(CATSUtility::getBuild()); ?></span>.<br />
                                <?php if ($this->newVersion): ?>
                                    <span class="bold">A new version is available!</span><br />
                                    Go to <a href="http://candidats.sourceforge.net/" target="_blank">candidats.sourceforge.net</a> to learn more.<br />
                                <?php else: ?>
                                    CandidATS reports your version is up to date.<br />
                                <?php endif; ?>
                            </p>
                            <?php $this->_($this->newVersionNews); ?>
                        <?php else: ?>
                            <span>Version checking is currently disabled.</span><br /><br />
                        <?php endif; ?>
                        <!-- <form action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration" id="changeNewVersionForm" method="post">
                            <input type="hidden" name="postback" value="postback" />
                            <input type="hidden" name="administrationMode" value="changeVersionCheck" />

                            <input type="checkbox" name="versionCheck" id="versionCheck"<?php if ($this->versionCheckPref): ?> checked<?php endif; ?> />Enable New Version Check (Recommended)<br /><br />
                            <input type="submit" name="save" class = "button" value="Save" />
                            <input type="button" name="back" class = "button" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration';" />
                        </form> -->
                    </td>
                </tr>
            </table>
        </div>
    <?php 
$AUIEO_CONTENT=ob_get_clean();
			?>
