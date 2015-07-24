<?php
pageHeaderInclude('modules/settings/validator.js');
pageHeaderInclude('js/sorttable.js');
pageTitle('Settings');
ob_start();
 ?>
<p class="note">Profile</p>

<?php if ($this->isDemoUser): ?>
    Note that as a demo user, you do not have privileges to modify any settings.
    <br /><br />
<?php endif; ?>

<table width="100%">
    <tr>
        <td width="100%">
            <table class="searchTable" width="100%">
                <tr>
                    <td width="230">
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=showUser&amp;userID=<?php echo($this->userID); ?>&amp;privledged=false">
                            <img src="images/bullet_black.gif" alt="" border="0" />View Profile
                        </a>
                    </td>
                    <td>
                        View your current profile to verify your information is correct.
                    </td>
                </tr>
                <tr>
                    <td>
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=myProfile&amp;s=changePassword">
                            <img src="images/bullet_black.gif" alt="" border="0" />Change Password
                        </a>
                    </td>
                    <td>
                        Change your CATS login password.
                    </td>
                </tr>
                <!--<tr>
                    <td>
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=myProfile&amp;s=notificationOptions">
                            <img src="images/bullet_black.gif" alt="" border="0" />Change Notification Options
                        </a>
                    </td>
                    <td>
                        Change how CATS notifies you of new events.
                    </td>
                </tr>-->
            </table>
        </td>
    </tr>
</table>
<?php $AUIEO_CONTENT=ob_get_clean(); ?>
