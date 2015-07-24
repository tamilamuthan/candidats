<?php
$AUIEO_ERROR_MSG=errMsg();
$module=getCurrentModuleController();
$AUIEO_MODULE_CAPTION=$module->getModuleTabText();
$AUIEO_MODULE_ICON=$module->getIcon();
$AUIEO_URL=http_build_query($_REQUEST);
?>