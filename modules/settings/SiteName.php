<?php
pageHeaderInclude('modules/settings/validator.js');
pageTitle('Settings');
ob_start();
?>

            <p class="note">Change Site Name</p>

            <table class="searchTable" width="100%">
                <tr>
                    <td>
                        <form action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration" id="changeSiteNameForm" onsubmit="return checkSiteNameForm(document.changeSiteNameForm);" method="post" autocomplete="off">
                            <input type="hidden" name="postback" value="postback" />
                            <input type="hidden" name="administrationMode" value="changeSiteName" />
                            Current site name: <?php echo($_SESSION['CATS']->getSiteName())?><br />
                            <br />
                            <label id="siteNameLabel" for="siteName">New Site Name:</label>
                            <br />
                            <input type="text" name="siteName" id="siteName" value="<?php echo($_SESSION['CATS']->getSiteName())?>" style="width:250px;" /><br /><br />
                            <input type="submit" name="save" class = "button" value="Save" />
                            <input type="button" name="back" class = "button" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration';" />
                        </form>
                    </td>
                </tr>
            </table>
     <?php $AUIEO_CONTENT=ob_get_clean(); ?>
