<?php
pageHeaderInclude('../js/careerPortalApply.js');
global $careerPage; 
if (isset($careerPage) && $careerPage == true)
{
    pageHeaderInclude('../js/lib.js');
    pageHeaderInclude('../js/sorttable.js');
    pageHeaderInclude('../js/calendarDateInput.js');
}
else
{
    pageHeaderInclude('js/lib.js');
    pageHeaderInclude('js/sorttable.js');
    pageHeaderInclude('js/calendarDateInput.js');
    pageHeaderInclude('js/careersPage.js');
}
pageTitle('Careers');
ob_start();
?>

        <style type="text/css" media="all">
            <?php echo($this->template['CSS']); ?>
			#poweredCATS { clear: both; margin: 30px auto; clear: both; width: 140px; height: 40px; border: none;}
			#poweredCATS img { border: none; }
        </style>
    <!-- TOP -->
    <?php echo($this->template['Header']); ?>

    <!-- CONTENT -->
    <?php echo($this->template['Content']); ?>

    <!-- FOOTER -->
    <?php echo($this->template['Footer']); ?>
    <div style="font-size:9px;">
        <br /><br /><br /><br />
    </div>
    <div style="text-align:center;">

        <?php /* WARNING: It is against the terms of the CPL to remove or alter the following line.  The 'Powered by CATS' line must stay visible on every page. */ ?>
        <div id="poweredCATS">
		<a href="http://www.catsone.com" target="_blank"><img src="http://www.catsone.com/images/CATS-powered.gif" alt="Powered by: CATS - Applicant Tracking System" title="Powered by: CATS - Applicant Tracking System" /></a>
		</div>
    </div>
    <script type="text/javascript">st_init();</script>
   <?php
   $AUIEO_CONTENT=  ob_get_clean();
   ?>