<?php
$module_controller_name=function ($module)
{
    return ucfirst($module)."UI";
};
$joborders_add_before=function ($record)
{
    return true;
};
$joborders_add_after=function ($record)
{
    AUIEO_SEND_HOOK_EMAIL($record);
};
$joborders_update_after=function ($record)
{
    AUIEO_SEND_HOOK_EMAIL($record);
};
if(!function_exists("AUIEO_SEND_HOOK_EMAIL"))
{
    function AUIEO_SEND_HOOK_EMAIL ($record)
    {
        $recruiter=$record["recruiter"];
        $sql="select * from user where user_id='{$recruiter}'";
        $db=DatabaseConnection::getInstance();
        $arrAssoc=$db->getAllAssoc($sql);
        $emailAddress=$arrAssoc[0]["email"];
        $site_id=$arrAssoc[0]["site_id"];
        if($emailAddress)
        {
            $obj=new JobOrders($site_id);
            $obj->load($record["id"]);
            $obj->sendTemplateEMail("My Joborder 2", $emailAddress);
        }

        $owner=$record["owner"];
        $sql="select * from user where user_id='{$owner}'";
        $db=DatabaseConnection::getInstance();
        $arrAssoc=$db->getAllAssoc($sql);
        $emailAddress=$arrAssoc[0]["email"];
        $site_id=$arrAssoc[0]["site_id"];
        if($emailAddress)
        {
            $obj=new JobOrders($site_id);
            $obj->load($record["id"]);
            $obj->sendTemplateEMail("My Joborder 2", $emailAddress);
        }
    }
}
?>