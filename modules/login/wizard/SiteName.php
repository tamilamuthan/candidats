<?php
ob_start();
?>
<b>Please name your CATS site.</b>
<br />
The name you choose will be used to describe your CATS site. By default, it will be seen in
e-mail notifications.
<p />
<div id="siteBeacon" style="display: none;">&nbsp;</div>
<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td style="padding-right: 15px; font-size: 14px;">Site Name:</td>
        <td><input type="text" name="siteName" id="siteName" value="" size="30" maxlength="75" style="border: 1px solid #0C519D; padding: 5px;" /></td>
    </tr>
</table>

<?php
$AUIEO_CONTENT=  ob_get_clean();
?>