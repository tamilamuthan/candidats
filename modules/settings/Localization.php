<?php
pageHeaderInclude('modules/settings/validator.js');
pageTitle('Settings');
ob_start();
?>

            <p class="note">Localization</p>

            <table class="searchTable" width="100%">
                <tr>
                    <td>
                        <div style="width: 700px;">These options affect how CATS formats numbers, dates, and time. <span style="font-weight:bold;">You (and your other site users) will need to log out and log back in for these settings to take effect.</span></div>
                        <br />
                        <form action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration" id="localizationForm" method="post">
                            <input type="hidden" name="postback" value="postback" />
                            <input type="hidden" name="administrationMode" value="localization" />

                            <table class="editTable" width="700">
                                <tr>
                                    <td>Please choose your time zone.</td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 10px;"><?php TemplateUtility::printTimeZoneSelect('timeZone', 'width: 420px;', '', $this->timeZone); ?></td>
                                </tr>

                                <tr>
                                    <td>Please choose your preferred date format.</td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 5px;">
                                        <select id="dateFormat" name="dateFormat" style="width: 150px;">
                                            <option value="mdy"<?php if (!$this->isDateDMY): ?> selected<?php endif; ?>>MM-DD-YYYY (US)</option>
                                            <option value="dmy"<?php if ($this->isDateDMY): ?> selected<?php endif; ?>>DD-MM-YYYY (UK)</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        <input type="submit" class="button" value="Save (And Logout)" />&nbsp;
                        <input type="button" name="back" class="button" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration';" />
                        </form>
                    </td>
                </tr>
            </table>
    <?php $AUIEO_CONTENT=ob_get_clean(); ?>
