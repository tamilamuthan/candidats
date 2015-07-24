
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
<?php if(isset($_REQUEST["msg"])) echo "<span style='color:red;'>".$_REQUEST["msg"]."</span>";  ?>
            <p class="note">Passwords</p>

            <table class="searchTable" width="100%">
                <tr>
                    <td>
                        <table class="editTable" width="700">
                            <tr>
                                <td class="tdVertical" style="width:320px;">
                                    Allow retrieval of forgotten passwords through email:
                                </td>
                                <td class="tdData">
                                    <input type="checkbox" name="ForgottenPasswords" disabled>
                                </td>
                            </tr>
                        </table>
                        <input type="button" name="back" class="button" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration';" />
                    </td>
                </tr>
            </table>
        </div>
    <?php 
$AUIEO_CONTENT=ob_get_clean();
			?>

