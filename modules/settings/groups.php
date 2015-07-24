<?php
$db=  DatabaseConnection::getInstance();
$site_id=$_SESSION["CATS"]->getSiteID();
$query = "SELECT id,parentid,rolename as title FROM auieo_roles where site_id={$site_id} and parentid!=0";
$arrRowRoles = $db->getAllAssoc($query);
include_once("modules/settings/ClsFieldsView.php");
$AUIEO_JSON_RENDER="";
$objFieldsView=new ClsFieldsView();
$arrRender=$objFieldsView->render();//trace($arrRender);

/**
 * build roles and profile array
 */
$query = "select * from auieo_profiles where site_id={$site_id}";
$arrRowProfile = $db->getAllAssoc($query);
$arrProfile=array();
foreach($arrRowProfile as $rw)
{
    $arrProfile[$rw["id"]]=$rw["profilename"];
}
$arrProfileRole=array();
$arrProfileRoleSelected=array();
$arrProfileRoleAvailable=array();
foreach($arrRowRoles as $i=>$arrRle)
{
    foreach($arrRowProfile as $j=>$rw)
    {
        $arrProfileRole[$arrRle["id"]][$j]=array("profileid"=>$rw["id"],"email"=>$rw["profilename"]);
        $sql="select * from auieo_roles2profiles where roleid={$arrRle["id"]} and profileid={$rw["id"]}";
        $arrAssoc=$db->getAllAssoc($sql);
        if(empty($arrAssoc))
        {
            $arrProfileRoleSelected[$arrRle["id"]][$j]="";
            $arrProfileRoleAvailable[$arrRle["id"]][$j]=array("profileid"=>$rw["id"],"email"=>$rw["profilename"]);
        }
        else
        {
            $arrProfileRoleSelected[$arrRle["id"]][$j]=array("profileid"=>$rw["id"],"email"=>$rw["profilename"]);
            $arrProfileRoleAvailable[$arrRle["id"]][$j]="";
        }
    }
}
$AUIEO_JSON_PROFILE_ROLE_SELECTED = json_encode($arrProfileRoleSelected[$arrRowRoles[0]["id"]]);
$AUIEO_JSON_PROFILE_ROLE_AVAILABLE = json_encode($arrProfileRoleAvailable[$arrRowRoles[0]["id"]]);
$AUIEO_JSON_PROFILE_ROLE = json_encode($arrProfileRole[$arrRowRoles[0]["id"]]);
$AUIEO_JSON_PROFILE_SELECT_INDEX = $arrRowRoles[0]["id"];

//$arrRender=$objCandidateView->render();
$AUIEO_JSON_RENDER=  json_encode($arrRender);
$AUIEO_FIELD_MODULE = '<select id="module" onchange="loadFieldsModule();">
    <option value="100" '.(isset($_REQUEST["fieldmodule"]) && $_REQUEST["fieldmodule"]=="candidates"?"selected":"").'>Candidate</option>
    <option value="200" '.(isset($_REQUEST["fieldmodule"]) && $_REQUEST["fieldmodule"]=="companies"?"selected":"").'>Company</option>
    <option value="300" '.(isset($_REQUEST["fieldmodule"]) && $_REQUEST["fieldmodule"]=="contacts"?"selected":"").'>Contact</option>
    <option value="400" '.(isset($_REQUEST["fieldmodule"]) && $_REQUEST["fieldmodule"]=="joborders"?"selected":"").'>Joborder</option>
</select>';

?>