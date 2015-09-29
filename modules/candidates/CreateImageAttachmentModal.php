<?php 
pageHeaderInclude('modules/candidates/validator.js');
pageHeaderInclude('Create Candidate Attachment');
pageTitle('Candidates');
ob_start();
 if (!$this->isFinishedMode): ?>
        <form name="createAttachmentForm" id="createAttachmentForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addEditImage" enctype="multipart/form-data" method="post" onsubmit="">
            <input type="hidden" name="postback" id="postback" value="postback" />
            <input type="hidden" id="candidateID" name="candidateID" value="<?php echo($this->candidateID); ?>" />
            <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData): ?>
                 <?php if ($attachmentsData['isProfileImage'] == '1'): ?>
                    <div style="text-align:center;">
                            <a href="<?php echo str_replace("&amp;", "&", $attachmentsData['retrievalURL']); ?>">
                            <img src="<?php $this->_($attachmentsData['retrievalURLLocal']) ?>" border="0" width="165">
                        </a>
                    </div>
                 <?php endif; ?>
            <?php endforeach; ?>
            <table class="editTable">
                <tr>
                    <td class="tdVertical">New Profile Picture:</td>
                    <td class="tdData"><input type="file" id="file" name="file" /></td>
                </tr>
            </table>
            <input type="submit" class="button" name="submit" id="submit" value="Set Image" />&nbsp;
            <input type="button" class="button" name="close" value="Close" onclick="parentHidePopWin();" />
        </form>
    <?php else: ?>
        <p>The picture has been saved..</p>

        <input type="button" name="close" value="Close" onclick="parentHidePopWin();" />
        <script type="text/javascript">
            parentHidePopWin();
        </script>
<?php endif; 
$AUIEO_CONTENT=ob_get_clean();
?>