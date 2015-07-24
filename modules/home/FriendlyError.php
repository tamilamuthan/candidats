<?php

pageTitle('Support');
ob_start();
 ?>
<style type="text/css">
div.friendlyErrorTitle {
    font-size: 16pt;
    font-weight: bold;
    color: #444444;
    line-height: 14pt;
    font-family: Arial, Verdana, sans-serif;
}

div.friendlyErrorMessage {
    font-size: 9pt;
    color: #444444;
    line-height: 14pt;
    font-family: Arial, Verdana, sans-serif;
}
</style>
            <table style="padding: 25px;">
                <tr>
                    <?php if (!$this->modal): ?>
                    <td align="left" valign="top" style="padding-right: 20px;">
                        <img src="images/friendly_error.jpg" border="0" />
                    </td>
                    <?php endif; ?>
                    <td align="left" valign="top">
                        <div class="friendlyErrorTitle"><?php echo $this->errorTitle; ?></div>
                        <p />
                        <div class="friendlyErrorMessage">
                            <?php echo $this->errorMessage; ?>
                            <?php if ($this->isDemo): ?>
                            <br /><br />
                            You are logged in as a <b>demo account.</b> Demo accounts
                            have several restrictions in place because of their inherent anonymity.
                            You may wish to sign up for a CATS Hosted account -- it's free,
                            and none of the demo restrictions are in place. To sign up, <a href="?a=getcats">click here</a>!
                            <?php endif; ?>
                            <?php
                            eval(Hooks::get('FRIENDLYERRORS_CONTACTCATS'));
                            ?>
                        </div>
                    </td>
                </tr>
            </table>
<?php $AUIEO_CONTENT=ob_get_clean(); ?>