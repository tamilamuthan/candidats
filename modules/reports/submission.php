<?php

ob_start();
 ?>

  
<center><h2><?php $this->_($this->reportTitle); ?></h2></center>
    <p class="note">Submissions</p>

<?php foreach ($this->submissionJobOrdersRS as $rowNumber => $submissionJobOrdersData)
{
    $submissionsRS=$submissionJobOrdersData['submissionsRS'];
    unset($submissionJobOrdersData['submissionsRS']);
    $arrrSummaryRenderData=array();
    foreach($submissionJobOrdersData as $summeryField=>$summeryfieldData)
    {
        $summeryField=  str_replace("joborder_", "",$summeryField);
        $arrrSummaryRenderData[]="<span style='font: normal normal bold 13px/130% Arial, Tahoma, sans-serif;'>{$summeryField}</span>:{$summeryfieldData}";
    }
    echo implode(", ",$arrrSummaryRenderData);
?>
        <br />
        <table class="sortable" width="100%">
           
<?php
echo " <tr>";
foreach($submissionsRS[0] as $fieldname=>$fielddata)
{
    echo "<th align='left' nowrap='nowrap'>{$fieldname}</th>";
}
echo " </tr>";
foreach ($submissionsRS as $rowNumber => $submissionsData)
{
    echo " <tr>";
    foreach($submissionsData as $fieldname=>$fielddata)
    {
        echo "<td valign='top' align='left' nowrap='nowrap'>{$fielddata}</td>";
    }
    echo " </tr>";
}
?>
        </table>
    <?php } ?>
<?php $AUIEO_CONTENT=ob_get_clean(); ?>