<?php
pageHeaderInclude('modules/joborders/validator.js');
pageHeaderInclude('js/company.js');
pageHeaderInclude('js/sweetTitles.js');
pageTitle('EEO Reports');
ob_start();
?>

            <p class="note">Generate a report on Equal Employment Opportunity Statistics.</p>

            <form name="jobOrderReportForm" id="jobOrderReportForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get">
                <input type="hidden" name="m" value="reports">
                <input type="hidden" name="a" value="generateEEOReportPreview">
                
                <table>
                    <tr>
                        <td style="vertical-align: top;">
                            <table class="editTable" <?php if (isset($this->EEOReportStatistics)): ?>width="230"<?php else: ?>width="680"<?php endif; ?>>
                                <tr>
                                    <td class="tdVertical" style="width: 75px;">
                                        <label id="siteNameLabel" for="siteName">Date Range:</label>
                                    </td>
                                    <td class="tdData">
                                       <input type="radio" name="period" value="all" <?php if ($this->modePeriod == 'all'): ?>checked<?php endif; ?>>&nbsp;All time<br />
                                       <input type="radio" name="period" value="month" <?php if ($this->modePeriod == 'month'): ?>checked<?php endif; ?>>&nbsp;Last Month<br />
                                       <input type="radio" name="period" value="week" <?php if ($this->modePeriod == 'week'): ?>checked<?php endif; ?>>&nbsp;Last Week<br />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical" style="width: 75px;">
                                        <label id="companyNameLabel" for="companyName">Status:</label>
                                    </td>
                                    <td class="tdData">
                                       <input type="radio" name="status" value="all" <?php if ($this->modeStatus == 'all'): ?>checked<?php endif; ?>>&nbsp;All<br />
                                       <input type="radio" name="status" value="placed" <?php if ($this->modeStatus == 'placed'): ?>checked<?php endif; ?>>&nbsp;Placed<br />
                                       <input type="radio" name="status" value="rejected" <?php if ($this->modeStatus == 'rejected'): ?>checked<?php endif; ?>>&nbsp;Not in Consideration<br />
                                    </td>
                                </tr>
                            </table>
                            <input type="submit" class="button" name="submit" value="Preview Report" />&nbsp;
                        </td>
                        
                        <?php if (isset($this->EEOReportStatistics)): ?>
                            <td style="vertical-align: top;">
                                <table class="selectView" width="705">
                                    <tr>
                                        <td class="tdVertical" style="padding:10px;">
                                            <div style="text-align: center; font-size:25px; width:250px;">Report Preview:</div>
                                            <br />
                                            <?php if ($this->EEOSettingsRS['ethnicTracking'] == 1): ?>
                                                <table>
                                                    <tr>
                                                        <td>
                                                            <img src="<?php echo($this->urlEthnicGraph); ?>">
                                                        </td>
                                                        <td style="vertical-align: top; padding:20px;">
                                                            <table style="border-collapse: collapse;" style="width:200px;">
                                                                <tr>
                                                                    <td colspan="2">
                                                                        <br />
                                                                        Candidates by Ethnic Types:<br />
                                                                        <br />
                                                                    </td>
                                                                </tr>
                                                                <?php foreach ($this->EEOReportStatistics['rsEthnicStatistics'] as $data): ?>
                                                                    <tr>
                                                                        <td style="width:160px;">
                                                                            <?php $this->_($data['EEOEthnicType']); ?>:
                                                                        </td>
                                                                        <td>
                                                                            <?php $this->_($data['numberOfCandidates']); ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            <?php endif; ?>
                                            <br />
                                            <br />
                                            <?php if ($this->EEOSettingsRS['veteranTracking'] == 1): ?>
                                                <table>
                                                    <tr>
                                                        <td>
                                                            <img src="<?php echo($this->urlVeteranGraph); ?>">
                                                        </td>
                                                        <td style="vertical-align: top; padding:20px;">
                                                            <table style="border-collapse: collapse;" style="width:200px;">
                                                                <tr>
                                                                    <td colspan="2">
                                                                        <br />
                                                                        Candidates by Veteran Status:<br />
                                                                        <br />
                                                                    </td>
                                                                </tr>
                                                                <?php foreach ($this->EEOReportStatistics['rsVeteranStatistics'] as $data): ?>
                                                                    <tr>
                                                                        <td style="width:160px;">
                                                                            <?php $this->_($data['EEOVeteranType']); ?>:
                                                                        </td>
                                                                        <td>
                                                                            <?php $this->_($data['numberOfCandidates']); ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table> 
                                            <?php endif; ?>
                                            <br />
                                            <table>
                                                <tr>
                                                    <?php if ($this->EEOSettingsRS['genderTracking'] == 1): ?>
                                                        <td style="padding:5px;">
                                                            <table style="border-collapse: collapse;" width="300px;">
                                                                <tr>
                                                                    <td colspan="2">
                                                                        <img src="<?php echo($this->urlGenderGraph); ?>">
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    <?php endif; ?>
                                                    <?php if ($this->EEOSettingsRS['genderTracking'] == 1): ?>
                                                        <td style="padding:5px;">
                                                            <table style="border-collapse: collapse;" width="190px;">
                                                                <tr>
                                                                    <td colspan="2">
                                                                       <img src="<?php echo($this->urlDisabilityGraph); ?>">
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        <?php endif; ?>
                    </tr>
                </table>
            </form>

            <script type="text/javascript">
                document.jobOrderReportForm.siteName.focus();
            </script>
<?php
$AUIEO_CONTENT=  ob_get_clean();
?>