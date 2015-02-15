<?php /* $Id: ShowUser.tpl 2881 2007-08-14 07:47:26Z brian $ */ ?>
<?php TemplateUtility::printHeader('Settings', 'js/sorttable.js'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" alt="Settings" style="border: none; margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: Site Details</h2></td>
                </tr>
            </table>

            <p class="note">
                    Site Details
            </p>

            <table class="detailsOutside" width="100%">
                <tr>
                    <td width="100%" height="100%">
                        <table class="detailsInside" height="100%">
                            <tr>
                                <td class="vertical" style="width: 135px;">Site Name:</td>
                                <td class="data">
                                    <span class="bold">
                                        <?php $this->_($this->data['name']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="vertical" style="width: 135px;">Unix Name:</td>
                                <td class="data">
                                    <span class="bold">
                                        <?php $this->_($this->data['unix_name']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="vertical">Username:</td>
                                <td class="data">admin@<?php $this->_($this->data['name']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Password</td>
                                <td class="data">candidats</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
 
            <br clear="all" />
            <a href="index.php?m=settings&a=manageSites">Back</a>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>
