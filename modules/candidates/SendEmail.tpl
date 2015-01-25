<?php /* $Id: SendEmail.tpl 3078 2007-09-21 20:25:28Z will $ */ ?>
<?php TemplateUtility::printHeader('Candidates', array('modules/candidates/validator.js', 'lib/ckeditor/ckeditor.js', 'js/searchSaved.js', 'js/sweetTitles.js', 'js/searchAdvanced.js', 'js/highlightrows.js', 'js/export.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/candidate.gif" width="24" height="24" border="0" alt="Candidates" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Candidates: Send E-mail</h2></td>
                </tr>
            </table>

            <p class="note">Send Candidates E-mail</p>

            <?php
            if($this->success == true)
            {
                ?>

                <br />
                <span style="font-size: 12pt; font-weight: 900;">
                Your e-mail has been successfully sent to the following recipients:
                <blockquote>
                <?php
                echo $this->success_to;
                ?>
                </blockquote>


                <?php
            }
            else
            {
                $emailTo = '';
                foreach($this->recipients as $recipient)
                {
                        $eml = '';
                        if(strlen($recipient['email1']) > 0)
                        {
                            $eml = $recipient['email1'];
                        }
                        if(strlen($recipient['email2']) > 0)
                        {
                            if($eml!='')
                            {
                                $eml = $eml.", ".$recipient['email2'];
                            }
                            else
                            {
                                $eml = $recipient['email2'];
                            }
                        }
                        if($eml != '')
                        {
                            if($emailTo != '')
                            {
                                $emailTo .= ', ';
                            }
                            $emailTo .= $eml;
                        }
                }
                $optionEmailTemplate="";
                $emailTemplateArray=array();
                foreach ($this->emailTemplatesRS as $data)
                {
                    $arrMatch=array();
                    preg_match_all('/%[a-zA-Z]*%/', $data['text'], $arrMatch);
                    foreach($arrMatch[0] as $match)
                    {
                        $replace=$match;
                        if($match=="%DATETIME%")
                        {
                            $replace=date("d-m-Y");
                        }
                        else if($match=="%CANDFULLNAME%")
                        {
                            $replace=(isset($recipient["first_name"])?$recipient["first_name"]:"")." ".(isset($recipient["last_name"])?$recipient["last_name"]:"");
                        }
                        $data["text"]=str_replace($match, $replace, $data["text"]);
                    }
                    $emailTemplateArray[$data['emailTemplateID']]=array("title"=>$data['emailTemplateTitle'],"text"=>$data['text']);
                    $optionEmailTemplate=$optionEmailTemplate."
                    <option value='{$data['emailTemplateID']}'>{$data['emailTemplateTitle']}</option>";
                }
                $json_email_template=  json_encode($emailTemplateArray);
                $tabIndex = 1;
                ?>
            <script type="text/javascript">
                jQuery(document).ready(function ()
        {
            emailTo.ShortcutsEnabled=false;
            ckedit=CKEDITOR.replace( 'emailBody' );
        });
        $(document).ready(function() 
        {
            jQuery('#emailTo').bind('copy paste cut',function(e) {
            e.preventDefault(); //disable cut,copy,paste
            alert('cut,copy & paste options are disabled !!');
            });
            
            $('#emailTo').bind('contextmenu', function (e) {
                e.preventDefault();
                alert('Right Click is not allowed');
              }); 
});


            function load_template(obj)
            {
                if(jQuery("#titleSelect").val())
                {
                    var title=json_email_template[jQuery("#titleSelect").val()]["title"];
                    var text=json_email_template[jQuery("#titleSelect").val()]["text"];
                }
                else
                {
                    var text="";
                }
                ckedit.insertText(text);
            }
                </script>
            <script type="text/javascript">
            var json_email_template=<?php echo $json_email_template; ?>;
                       
                </script>
            <table class="editTable" width="100%">
                <tr>
                    <td>
                        <form name="emailForm" id="emailForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=emailCandidates" method="post" onsubmit="return checkEmailForm(document.emailForm);" autocomplete="off" enctype="multipart/form-data">
                        <input type="hidden" name="postback" id="postback" value="postback" />
                        <table>
                            <tr>
                                <td>
                                    Email Template
                                    </td>
                                    <td>
                                        <select id="titleSelect" style="width:550px;" onchange="load_template(this)">
                            <option value="">- Select -</option>
                                        <?php echo $optionEmailTemplate; ?>
                                        </select>
                                        </td>
                                </tr>
                            <tr>
                                <td class="tdVertical" style="text-align: right;">
                                    To
                                </td>
                                <td class="tdData">
                                    <textarea class="inputbox" name="emailTo" id="emailTo" rows="2", cols="90" tabindex="99" style="width: 800px;" readonly><?php echo($emailTo); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="tdVertical" style="text-align: right;">
                                    <label id="emailSubjectLabel" for="emailSubject">Subject</label>
                                </td>
                                <td class="tdData">
                                    <input id="emailSubject" tabindex="<?php echo($tabIndex++); ?>" type="text" name="emailSubject" class="inputbox" style="width: 800px;" />
                                </td>
                            </tr>
                            <tr>
                                <td class="tdVertical" style="text-align: right;">
                                    <label id="emailBodyLabel" for="emailBody">Body</label>
                                </td>
                                <td class="tdData">
                                    <textarea id="emailBody" tabindex="<?php echo($tabIndex++); ?>" name="emailBody" rows="10" cols="90" style="width: 800px;" class="inputbox"></textarea />
                                </td>
                            </tr>
                            <tr>
                                <td align="right" valign="top" colspan="2">
                                    <input type="submit" tabindex="<?php echo($tabIndex++); ?>" class="button" value="Send E-Mail" />&nbsp;
                                    <input type="reset"  tabindex="<?php echo($tabIndex++); ?>" class="button" value="Reset" onclick="javascript:ckedit.setData('');" />&nbsp;
                                </td>
                            </tr>
                        </table>

                        </form>

                        <script type="text/javascript">
                        document.emailForm.emailSubject.focus();
                        </script>
                    </td>
                </tr>
            </table>
            <?php
            }
            ?>
            <script type="text/javascript">
            
</script>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>
