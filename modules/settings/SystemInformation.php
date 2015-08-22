<?php
pageHeaderInclude('modules/settings/validator.js');
pageTitle('Settings');
ob_start();
 ?>

            <p class="note">System Information</p>

            <table class="editTable" width="700">
                    <tr>
                        <td class="tdVertical" colspan="2"><span style="font-weight: bold;">General Information</span></td>
                    </tr>
                    <tr>
                        <td class="tdVertical" style="width:250px;">
                            Operating System:
                        </td>
                        <td class="tdData">
                            <?php echo(php_uname()); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical" style="width:250px;">
                            Operating System Type:
                        </td>
                        <td class="tdData">
                            CATS thinks your operating system is <span class="bold"><?php $this->_($this->OSType); ?>.</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical" style="width:250px;">
                            PHP Version:
                        </td>
                        <td class="tdData">
                            <?php echo(PHP_VERSION); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical" style="width:250px;">
                            Database Version:
                        </td>
                        <td class="tdData">
                            <?php $this->_($this->databaseVersion); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical" style="width:250px;">
                            Installation Directory:
                        </td>
                        <td class="tdData">
                            <?php $this->_($this->installationDirectory); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical" colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="tdVertical" colspan="2"><span style="font-weight: bold;">Module Schema Version Information</span></td>
                    </tr>
                    <?php foreach ($this->schemaVersions as $rowIndex => $row): ?>
                    <tr>
                        <td class="tdVertical" style="width:250px;">
                            <?php $this->_($row['name']); ?>
                        </td>
                        <td class="tdData">
                            <?php echo($row['version']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
            </table>
            <input type="button" name="back" class="button" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration';" />

<?php
$AUIEO_CONTENT=  ob_get_clean();
?>