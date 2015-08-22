<?php
pageHeaderInclude('js/backup.js');
pageTitle('Settings');
ob_start();
?>

            <p class="note">Create Site Backup</p>

            <table class="searchTable" width="100%">
                <tr>
                    <td>
                        Create a backup of your entire CATS database (including all of your attachments).<br />
                        Note: Only one backup of your database can be stored on the server at a time.  Creating a new backup will
                        delete the previous backup.<br />
                        <br />
                    </td>
                </tr>
                    <td>
                    <span id="backupRunning" style="display:none;">
                        Backing up database, please wait... (Now would be a good time to take a coffee break!)
                        <br /><br />
                        Status:<br />
                    </span>
                    <span id="progressHistory">
                    </span>
                    <span id="progress">
                        Last backup:
                        <table class="attachmentsTable">
                            <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData): 
                                if(!file_exists($attachmentsData['retrievalURLLocal']) && strpos($attachmentsData['retrievalURLLocal'], ".bak"))
                                {
                                    $arr=explode(".bak",$attachmentsData['retrievalURLLocal']);
                                    $attachmentsData['retrievalURLLocal']=$arr[0].".sql";
                                    $arr=explode(".bak",$attachmentsData['originalFilename']);
                                    $attachmentsData['originalFilename']=$arr[0].".sql";
                                }
                                ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo $attachmentsData['retrievalURL']; ?>">
                                            <img src="images/file/zip.gif" alt="" width="16" height="16" border="0" />
                                        </a>
                                    </td>
                                    <td>
                                        (<?php $this->_($attachmentsData['fileSize']) ?>)&nbsp;
                                        <a href="<?php echo $attachmentsData['retrievalURLLocal']; ?>">
                                            <?php $this->_($attachmentsData['originalFilename']) ?>
                                        </a>
                                    </td>
                                    <td><?php $this->_($attachmentsData['dateCreated']) ?></td>
                                    <td>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=deleteBackup" title="Delete" onclick="javascript:return confirm('Delete this backup?');">
                                            <img src="images/actions/delete.gif" alt="" width="16" height="16" border="0" />
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                        <?php if (empty($this->attachmentsRS)): ?>
                            None<br />
                        <?php else: ?>
                            Click the file above to download the backup.<br />
                        <?php endif; ?>

                    <br />
                    <input type="button" class="button" value="Create Full System Backup" onclick="startBackup('settings:backup', '');" style="margin:3px; width:200px;"><br />
                    <input type="button" class="button" value="Create Attachments Backup" onclick="startBackup('settings:backup', '&attachmentsOnly=true');" style="margin:3px; width:200px;">
                    </span>
                    <span id="progressBar" style="display:none;">
                    <br /><br />
                    <div id="empty" style="background-color:#eeeeee;border:1px solid black;height:20px;width:300px;padding:0px;" align="left">
                        <div id="d2" style="position:relative;top:0px;left:0px;background-color:#2244ff;height:20px;width:0px;padding-top:5px;padding:0px;">
                            <div id="d1" style="position:relative;top:0px;left:0px;color:#ffffff;height:20px;text-align:center;font:bold;padding:0px;padding-top:1px;">
                            </div>
                        </div>
                    </div>
                    </span>
                    </td>
                    <span id="tempJs" style="display:none;"></span>
                    <iframe id="progressIFrame" style="display:none;"></iframe>
                </tr>
            </table>


    <?php $AUIEO_CONTENT=ob_get_clean(); ?>
