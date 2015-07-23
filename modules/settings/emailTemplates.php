
<?php 
ob_start();
TemplateUtility::printHeader('Settings', 'js/sorttable.js');
$AUIEO_HEADER=  ob_get_clean();
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
$AUIEO_OPTION_EMAIL_TEMPLATE=$optionEmailTemplate;
$AUIEO_JSON_EMAIL_TEMPLATE=$json_email_template;
?>