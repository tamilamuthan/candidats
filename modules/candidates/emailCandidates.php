<?php
/* 
 * CandidATS
 * Sites Management
 *
 * Copyright (C) 2014 - 2015 Auieo Software Private Limited, Parent Company of Unicomtech.
 * 
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

ob_start();
TemplateUtility::printHeader('Candidates', array('modules/candidates/validator.js', 'lib/ckeditor/ckeditor.js', 'js/searchSaved.js', 'js/sweetTitles.js', 'js/searchAdvanced.js', 'js/highlightrows.js', 'js/export.js'));
$AUIEO_HEADER =  ob_get_clean();

$AUIEO_CONTENT="";
ob_start();
$emailTo = '';
$arrIDList=array();
foreach($this->recipients as $recipient)
{
        if(strlen($recipient['email1']) > 0)
        {
            $arrIDList[$recipient["candidate_id"]]["email"][]=array("email"=>$recipient["email1"],"name"=>$recipient["last_name"]." ".$recipient["first_name"]);
            $eml = $recipient['email1'];
        }
        else if(strlen($recipient['email2']) > 0)
        {
            $arrIDList[$recipient["candidate_id"]]["email"][]=array("email"=>$recipient["email2"],"name"=>$recipient["last_name"]." ".$recipient["first_name"]);
            $eml = $recipient['email2'];
        }
        else
        {
            $arrIDList[$recipient["candidate_id"]]["email"][]=false;
            $eml = '';
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
foreach ($this->emailTemplatesRS as $data)
{
    $optionEmailTemplate=$optionEmailTemplate."
    <option value='{$data['emailTemplateID']}'>{$data['emailTemplateTitle']}</option>";
}
$tabIndex = 1;
$idlist= urlencode(json_encode($arrIDList));
?>

<table class="editTable" width="100%">
    <tr>
        <td>
            <form name="emailForm" id="emailForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=emailCandidates" method="post" onsubmit="return checkEmailForm(document.emailForm);" autocomplete="off" enctype="multipart/form-data">
            <input type="hidden" name="postback" id="postback" value="postback" />
            <input type="hidden" name="idlist" id="idlist" value="<?php echo $idlist; ?>" />
            <table>
                <tr>
                    <td>
                        Email Template
                        </td>
                        <td>
                            <select id="titleSelect" name="titleSelect" style="width:550px;">
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
$AUIEO_CONTENT=  ob_get_clean();
?>
