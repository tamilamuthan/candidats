<?php
include_once("modules/settings/ClsFieldsView.php");
$AUIEO_JSON_RENDER="";
$objFieldsView=new ClsFieldsView();
$arrRender=$objFieldsView->render();//trace($arrRender);
$arrModule=getModules();
//$arrRender=$objCandidateView->render();
$AUIEO_JSON_RENDER=  json_encode($arrRender);
$arrFieldModule=array();
$AUIEO_FIELD_MODULE = '<select id="module" onchange="loadFieldsModule();">';
$currentDataItemType=100;
foreach($arrModule as $data_item_type=>$moduleInfo)
{
    if(isset($_REQUEST["fieldmodule"]) && $_REQUEST["fieldmodule"]==$moduleInfo["module"])
    {
        $currentDataItemType=$data_item_type;
    }
    $AUIEO_FIELD_MODULE .= '<option value="'.$data_item_type.'" '.(isset($_REQUEST["fieldmodule"]) && $_REQUEST["fieldmodule"]==$moduleInfo["module"]?"selected":"").'>'.$moduleInfo["module"].'</option>';
    $arrFieldModule[$data_item_type]=$moduleInfo["module"];
}
$AUIEO_FIELD_MODULE .= '</select>';
$AUIEO_JSON_FIELD_MODULE = json_encode($arrFieldModule);
$arrModuleFields=getModuleFields($currentDataItemType);
/**
 * check owner field exist
 */
$isOwnerExist=false;
foreach ($arrModuleFields as $fieldDataModule)
{
    if($fieldDataModule["uitype"]==5)
    {
        $isOwnerExist=true;
        break;
    }
}
$AUIEO_NEW_FIELD="";
$sql="select * from auieo_uitype";
$objDB=DatabaseConnection::getInstance();
$arrUIType=$objDB->getAllAssoc($sql);
$arr=array();
foreach($arrUIType as $uitype)
{
    /**
     * restrict to use owner field one time only
     */
    if($isOwnerExist && $uitype["id"]==5) continue;
    $arr[]="<option value='{$uitype["id"]}'>{$uitype["caption"]}</option>";
}
$AUIEO_NEW_FIELD=implode("",$arr);
?>