<?php
ob_start();
TemplateUtility::printHeader('Settings', 'js/sorttable.js');
$AUIEO_HEADER=  ob_get_clean();
$AUIEO_EEO="";
$AUIEO_SITES="";
$AUIEO_SITES=$AUIEO_SITES."<option vlaue=''>- New Site -</option>";
foreach($this->sites as $site)
{
    $AUIEO_SITES=$AUIEO_SITES."<option value='{$site['siteID']}'>{$site['name']}</option>";
}
ob_start();
if($this->EEOSettingsRS['enabled'] == 1)
{
?>
<tr>
   <td class="tdVertical">Allowed to view EEO Information:</td>
   <td class="tdData">
       <span id="eeoIsVisibleCheckSpan">
           <input type="checkbox" name="eeoIsVisible" id="eeoIsVisible" onclick="if (this.checked) document.getElementById('eeoVisibleSpan').style.display='none'; else document.getElementById('eeoVisibleSpan').style.display='';">
           &nbsp;This user is <span id="eeoVisibleSpan">not </span>allowed to edit and view candidate's EEO information.
       </span>
   </td>
</tr>
<?php
}
$AUIEO_EEO=  ob_get_clean();
?>