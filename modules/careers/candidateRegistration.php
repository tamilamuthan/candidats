<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$AUIEO_HTML_ENCODING=HTML_ENCODING;
ob_start();
if (isset($careerPage) && $careerPage == true)
{
    pageHeaderInclude('../js/careerPortalApply.js');
    pageHeaderInclude('../js/lib.js');
    pageHeaderInclude('../js/sorttable.js');
    pageHeaderInclude('../js/calendarDateInput.js');
}
else
{
    pageHeaderInclude('../js/careerPortalApply.js');
    pageHeaderInclude('../js/lib.js');
    pageHeaderInclude('../js/sorttable.js');
    pageHeaderInclude('../js/calendarDateInput.js');
    pageHeaderInclude('js/careersPage.js');
}
pageTitle('Careers');
$AUIEO_HEADER=  ob_get_clean();
?>