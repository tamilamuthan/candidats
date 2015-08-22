<?php
$AUIEO_ERROR_MSG=errMsg();
$module=getCurrentModuleController();
if($module)
{
    $AUIEO_MODULE_CAPTION=$module->getModuleTabText();
    $AUIEO_MODULE_ICON=$module->getIcon();
}
else
{
    $AUIEO_MODULE_CAPTION="";
    $AUIEO_MODULE_ICON="";
}
$AUIEO_URL=http_build_query($_REQUEST);
ob_start();
TemplateUtility::printQuickSearch();
$AUIEO_QUICK_SEARCH = ob_get_clean();
?>