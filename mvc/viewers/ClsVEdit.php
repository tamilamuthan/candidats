<?php
class ClsVEdit extends ClsAuieoModuleViewer
{
    public function __construct($module) {
        loadScriptFiles("js/export.js","export.js","js");
        loadScriptFiles("js/searchSaved.js","searchSaved.js","js");
        loadScriptFiles("js/suggest.js","suggest.js","js");
        parent::__construct($module);
    }
}
?>