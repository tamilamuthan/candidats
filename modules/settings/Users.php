<?php
pageHeaderInclude('js/sorttable.js');
pageTitle('Settings');
ob_start();
?>

            <p class="note">User Management</p>

            <table class="sortable" width="100%">
                <thead>
                    <tr>
                        <th align="left" nowrap="nowrap">First Name</th>
                        <th align="left" nowrap="nowrap">Last Name</th>
                        <th align="left">Username</th>
                        <th align="left" nowrap="nowrap">Access Level</th>
                        <th align="left" nowrap="nowrap">Last Success</th>
                        <th align="left" nowrap="nowrap">Last Fail</th>
                    </tr>
                </thead>

                <?php if (!empty($this->rs)): ?>
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
                            <td valign="top" align="left"><?php $this->_($data['username']); ?></td>
                            <td valign="top" align="left"><?php $this->_($data['accessLevelDescription']); ?></td>
                            <td valign="top" align="left"><?php $this->_($data['successfulDate']); ?></td>
                            <td valign="top" align="left"><?php $this->_($data['unsuccessfulDate']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
            <a id="add_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=addUser" title="You have <?php $this->_($this->license['diff']); ?> user accounts remaining.">
                <img src="images/candidate_inline.gif" width="16" height="16" class="absmiddle" alt="add" style="border: none;" />&nbsp;Add User
            </a>
<?php $AUIEO_CONTENT=ob_get_clean(); ?>