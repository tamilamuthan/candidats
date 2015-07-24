<?php
pageHeaderInclude('modules/settings/validator.js');
pageHeaderInclude('js/sorttable.js');
pageTitle('Settings');
ob_start();
?>

            <p class="note">Recent Login Activity</p>

            <form name="loginActivityViewSelectorForm" id="loginActivityViewSelectorForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get">
                <input type="hidden" name="m" value="settings" />
                <input type="hidden" name="a" value="loginActivity" />

                <table class="viewSelector">
                    <tr>
                        <td>
                            <select name="view" id="view" onchange="document.loginActivityViewSelectorForm.submit();">
                                <?php if ($this->view == 'successful'): ?>
                                    <option value="successful" selected="selected">Successful Logins</option>
                                    <option value="unsuccessful">Unsuccessful Logins</option>
                                <?php elseif ($this->view == 'unsuccessful'): ?>
                                    <option value="successful">Successful Logins</option>
                                    <option value="unsuccessful" selected="selected">Unsuccessful Logins</option>
                                <?php else: ?>
                                    <option value="successful">Successful Logins</option>
                                    <option value="unsuccessful">Unsuccessful Logins</option>
                                <?php endif; ?>
                            </select>
                            <!--&nbsp;&nbsp;&nbsp;&nbsp;
                            Login activity older than 1 month plus 100 entries in the past is automatically cleared from the system.-->
                        </td>
                    </tr>
                </table>
            </form>

            <?php if (!empty($this->rs)): ?>
                <table class="sortable" width="100%">
                    <thead>
                        <tr>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('firstName', 'First Name'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('lastName', 'Last Name'); ?>
                            </th>
                            <th align="left">
                                <?php $this->pager->printSortLink('ip', 'IP'); ?>
                            </th>
                            <th align="left">
                                <?php $this->pager->printSortLink('hostname', 'Hostname'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('shortUserAgent', 'User Agent'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('dateSort', 'Date / Time'); ?>
                            </th>
                        </tr>
                    </thead>

                    <?php foreach ($this->rs as $rowNumber => $data): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td valign="top" align="left">
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=showUser&userID=<?php $this->_($data['userID']); ?>">
                                    <?php $this->_($data['firstName']); ?>
                                </a>
                            </td>
                            <td valign="top" align="left">
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=showUser&userID=<?php $this->_($data['userID']); ?>">
                                    <?php $this->_($data['lastName']); ?>
                                </a>
                            </td>
                            <td valign="top" align="left"><?php $this->_($data['ip']); ?></td>
                            <td valign="top" align="left"><?php $this->_($data['hostname']); ?></td>
                            <td valign="top" align="left"><?php $this->_($data['shortUserAgent']); ?></td>
                            <td valign="top" align="left" nowrap="nowrap"><?php $this->_($data['date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <?php $this->pager->printNavigation('', true, 20); ?>
            <?php endif;
            $AUIEO_CONTENT=ob_get_clean(); ?>
