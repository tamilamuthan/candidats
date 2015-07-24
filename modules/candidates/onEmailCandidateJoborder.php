<?php 
ob_start();
TemplateUtility::printHeader('Candidates', array('modules/candidates/validator.js', 'lib/ckeditor/ckeditor.js', 'js/searchSaved.js', 'js/sweetTitles.js', 'js/searchAdvanced.js', 'js/highlightrows.js', 'js/export.js'));
$AUIEO_HEADER =  ob_get_clean();
?>