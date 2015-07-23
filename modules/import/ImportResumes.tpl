<?php /* $Id: ImportResumes.tpl 3584 2007-11-12 23:20:53Z will $ */ ?>
<?php TemplateUtility::printHeader('Import', array('modules/import/import.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, ''); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/reports.gif" width="24" height="24" border="0" alt="Import" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Import Data</h2></td>
                </tr>
            </table>

            <p class="note" id="importHide2">Import Data - Step 2</p>

            <?php if (isset($this->errorMessage)): ?>

                <p class="warning" id="importHide0">Error!</p>

                <table class="searchTable" id="importHide1" width="100%">
                    <tr>
                        <td>
                            <?php echo($this->errorMessage); ?>
                        </td>
                    </tr>
                </table>

                <br />

            <?php elseif (isset($this->successMessage)): ?>

                <p class="note" id="importHide0">Success</p>

                <table class="searchTable" id="importHide1" width="100%">
                    <tr>
                        <td>
                            <?php echo($this->successMessage); ?>
                        </td>
                    </tr>
                </table>

                <br />

           <?php endif; ?>
            <table class="searchTable" id="importTable1" width="100%">
                <tr>
                    <td>CATS may discard or fail to read some of the submitted data which it does not
                    understand how to use. Do not discard the original data!
                    </td>
                </tr>

            </table>

            <br />

            <form name="importDataForm" id="importDataForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=import&amp;a=importUploadResume" enctype="multipart/form-data" method="post" autocomplete="off" onsubmit="document.getElementById('nextSpan').style.display='none'; document.getElementById('uploadingSpan').style.display='';">
                <table class="searchTable" width="100%" id="importHide3">
                    <tr>
                        <td class="tdVertical">
                            <label id="fileLabel" for="file">Import:</label>
                        </td>
                        <td class="tdData">
                            <img src="images/file/doc.gif">&nbsp;Resume&nbsp;<a href="javascript:void(0);" onclick="showPopWin('index.php?m=import&a=whatIsBulkResumes', 420, 275, null);">(How do I use bulk resumes?)</a>
                        </td>
                    </tr>

                    <tr id="importMultiple">
                        <td class="tdVertical">
                            <label id="fileLabel" for="file">
                                <br />
                                Multiple File Import:
                            </label>
                        </td>
                        <td class="tdData">
                            <?php if($this->allowAspFlashUploader == true): ?>
                                <br />
                                Step 1:<br />
                                Upload resumes you wish to parse to the CATS server.  CATS can parse doc, pdf, txt, and <br />rtf format resumes, and can also accept zip format archives of resumes.
                                
                                  <br />
                                  When you are finishing uploading your resumes, press the below button to continue.<br />
                                <input type="button" class="button" value="Step 2: Parse Resumes" onclick="document.location.href='?m=import&a=showMassImport';" />
                            <?php elseif($this->allowMultipleFiles == true): ?>
                                <br />
                                To import multiple files, add the files you would like to import to the 'upload' directory on your
                                CATS web server, and press the button below to have CATS scan for uploaded documents.  If you need
                                assistance in uploading files to your web server, contact your system administrator.<br />
                                <br />
                                <input type="button" class="button" value="Scan /upload/ folder for resumes" onclick="document.location.href='?m=import&a=showMassImport';" />
                            <?php else: ?>
                                <br />
                                The automated bulk resume import feature has been temporarily disabled.<br /><br />
                                To import resumes into the bulk resume pool, please contact <a href="mailto:support@catsone.com">support@catsone.com</a>
                                for assistance from the CATS team.
                                <br />
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
    <div id="bottomShadow"></div>

    <script type="text/javascript">
        initPopUp();
    </script>

<?php TemplateUtility::printFooter(); ?>
