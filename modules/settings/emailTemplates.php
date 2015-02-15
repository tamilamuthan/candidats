
<?php 
ob_start();
TemplateUtility::printHeader('Settings', 'js/sorttable.js');
$AUIEO_HEADER=  ob_get_clean();
ob_start();
?>


    
        

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/candidate.gif" width="24" height="24" border="0" alt="Candidates" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: EMail Template</h2></td>
                </tr>
            </table>

            <p class="note">EMail Template</p>


                <?php
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
                        $data["text"]=str_replace($match, $replace, $data["text"]);
                    }
                    $emailTemplateArray[$data['emailTemplateID']]=array("title"=>$data['emailTemplateTitle'],"text"=>$data['text']);
                    $optionEmailTemplate=$optionEmailTemplate."
                    <option value='{$data['emailTemplateID']}'>{$data['emailTemplateTitle']}</option>";
                }
                $json_email_template=  json_encode($emailTemplateArray);
                ?>
<script type="text/javascript">
    var jsonmodule={"candidates":"Candidates","joborders":"Joborders","contacts":"Contacts"};
    function loadModule()
    {
        var stroption="<option value=''>- Select -</option>";
        for(var i in jsonmodule)
        {
            stroption=stroption+"<option value='"+i+"'>"+jsonmodule[i]+"</option>";
        }
        jQuery("#templatemodule").html(stroption);
    }
        jQuery(document).ready(function ()
        {
            ckedit=CKEDITOR.replace( 'emailBody' );
            loadModule();
            jQuery("#templatemodule").change(function ()
            {
                var templatemodule=jQuery("#templatemodule").val();
                if(templatemodule=="")
                {
                    jQuery("#templatevars").html("<option value=''>- Select -</option>");
                }
                else
                {
                    jQuery.ajax({
                        url:"index.php?m=settings&a=templateVariables&templatemodule="+jQuery("#templatemodule").val()+"&templateid="+jQuery("#titleSelect").val(),
                        beforeSend: function( xhr ) 
                        {
                            jQuery("#templatevars").html("<option value=''>loading...</option>");
                        },
                        success:function (response)
                        {
                            var objTplVar=JSON.parse(response);
                            var stroption="";
                            for(var module in objTplVar)
                            {
                                var objMain=objTplVar[module]["main"];
                                var objExtra=objTplVar[module]["extra"];
                                stroption=stroption+"<option value=''>- Select -</option><optgroup label='"+module+":Main Columns'>";
                                for(var i in objMain)
                                {
                                    stroption=stroption+"<option rel='" + module + "' value='"+i+"'>"+objMain[i]+"</option>";
                                }
                                stroption=stroption+"</optgroup>";
                                stroption=stroption+"<optgroup label='"+module+":Extra Columns'>";
                                for(var i in objExtra)
                                {
                                    stroption=stroption+"<option rel='" + module + "' value='"+i+"'>"+objExtra[i]+"</option>";
                                }
                                stroption=stroption+"</optgroup>";
                            }
                            jQuery("#templatevars").html(stroption);
                        }
                    });
                }
            });
            jQuery("#insertvar").click(function ()
            {
                var tplvar=jQuery("#templatevars").val();
                var tplmodule=jQuery("#templatemodule").val();
                var tplRelmodule=jQuery("#templatevars").find("option:selected").attr("rel");
                if(tplvar!="")
                {
                    ckedit.insertText("{$" + tplmodule + "->" + tplRelmodule + "->" + tplvar + "}");
                }
            });
            jQuery("#titleSelect").change(function ()
            {
                $templateid=jQuery("#titleSelect").val();
                if($templateid=="")
                {
                    loadModule();
                    jQuery("#templatevars").html("<option value=''>- Select -</option>");
                    ckedit.setData("");
                }
                else
                {
                    jQuery.ajax({
                        url:"index.php?m=settings&a=templateVariables&templateID="+$templateid,
                        beforeSend: function( xhr ) 
                        {
                            ckedit.setData("...loading. Please wait.");
                        },
                        success:function (response)
                        {
                            var objTplVar=JSON.parse(response);
                            ckedit.setData(objTplVar.text);
                            jQuery("#emailSubject").val(objTplVar.emailTemplateTitle);
                            if(objTplVar.disabled==1)
                            {
                                jQuery("#useThisTemplate").prop("checked",false)
                            }
                            else
                            {
                                jQuery("#useThisTemplate").prop("checked",true)
                            }
                            /*var stroption="<option value=''>- Select -</option>";
                            for(var i in objTplVar)
                            {
                                stroption=stroption+"<option value='"+i+"'>"+objTplVar[i]+"</option>";
                            }
                            jQuery("#templatemodule").html(stroption);*/
                        }
                    });
                }
            });
        });
            function load_template(obj)
            {
                /*var templateid=jQuery(obj).val();
                
                if(jQuery("#titleSelect").val())
                {
                    var title=json_email_template[jQuery("#titleSelect").val()]["title"];
                    var text=json_email_template[jQuery("#titleSelect").val()]["text"];
                }
                else
                {
                    var text="";
                }
                ckedit.setData(text);*/
            }
            /*function insertTemplateVar()
            {
                var tmp='<input type="button" class="button" style="width:235px;" value="'.$description.'" onclick="insertAtCursor(document.getElementById(\'messageText'.$data['emailTemplateID'].'\'),  \''.$value.'\');"><br />';
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
            }*/
                </script>
            <script type="text/javascript">
            var json_email_template=<?php echo $json_email_template; ?>;
                       
                </script>
                <script src="js/ckeditor/ckeditor.js"></script>
<script src="js/ckeditor/adapters/jquery.js"></script>
<script>
    $( document ).ready( function() {
	//$( 'textarea#emailBody' ).ckeditor();
    } );
</script>
            <table class="editTable" width="100%">
                <tr>
                    <td>
                        <form name="emailForm" id="emailForm" action="index.php?m=settings&a=emailTemplates" method="post" onsubmit="return checkEmailForm(document.emailForm);" autocomplete="off" enctype="multipart/form-data">
                        <input type="hidden" name="m" value="settings" />
                        <input type="hidden" name="a" value="emailTemplates" />
                        <input type="hidden" name="postback" id="postback" value="postback" />
                        <table>
                            <tr>
                                <td>
                                    Email Template
                                    </td>
                                    <td>
                                        <select id="titleSelect" name="templateID" style="width:100%" onchange="load_template(this)">
                            <option value="">- New -</option>
                                        <?php echo $optionEmailTemplate; ?>
                                        </select>
                                        </td>
                                </tr>
                            <tr>
                                <td>
                                    Template Variables
                                    </td>
                                    <td style="text-align: left;">
                                        <table cellpadding="0" cellspacing="0"><tr><td style="text-align: left;">
                                       Module: <select id="templatemodule">
                            <option value="">- Select -</option>
                                        </select></td><td>
                                       Variable: <select id="templatevars">
                            <option value="">- Select -</option>
                                       </select></td><td><input type="button" id="insertvar" value="Insert" /></td><td> </td></tr></table>
                                        </td>
                                </tr>
                            <tr>
                                <td class="tdVertical" style="text-align: right;">
                                    <label id="emailSubjectLabel" for="emailSubject">Subject</label>
                                </td>
                                <td class="tdData">
                                    <table><tr><td>
                                    <input id="emailSubject" type="text" name="emailSubject" class="inputbox" style="width: 600px;" />
                                            </td><td>Enabled: </td><td><input name="useThisTemplate" id="useThisTemplate" type="checkbox" value="1" /></td></tr></table>
                                </td>
                            </tr>
                            <tr>
                                <td class="tdVertical" style="text-align: right;">
                                    <label id="emailBodyLabel" for="emailBody">Body</label>
                                </td>
                                <td class="tdData">
                                    <textarea id="emailBody" name="emailBody" rows="10" cols="90" style="width: 600px;" class="inputbox"></textarea />
                                </td>
                            </tr>
                            <tr>
                                <td align="right" valign="top" colspan="2">
                                    <input type="submit" class="button" value="Save" />&nbsp;
                                </td>
                            </tr>
                        </table>

                        </form>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
<?php
$AUIEO_CONTENT=ob_get_clean();
?>