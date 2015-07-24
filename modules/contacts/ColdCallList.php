<?php
pageHeaderInclude('js/sorttable.js');
pageHeaderInclude('js/highlightrows.js');

pageTitle('Contacts');
ob_start();
 ?>   
    <p class="note">Cold Call List (Only Contacts with Phone Numbers)</p>

    <?php if (!empty($this->rs)): ?>
        <table class="sortable" width="100%" rules="all" onmouseover="javascript:trackTableHighlight(event)">
            <tr>
                <th align="left">Company</th>
                <th align="left" nowrap="nowrap">First Name</th>
                <th align="left" nowrap="nowrap">Last Name</th>
                <th align="left">Title</th>
                <th align="left" nowrap="nowrap">Work Phone</th>
            </tr>

            <?php foreach ($this->rs as $rowNumber => $data): ?>
                <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                    <td valign="top" align="left"><?php $this->_($data['companyName']); ?></td>
                    <td valign="top" align="left"><?php $this->_($data['firstName']); ?></td>
                    <td valign="top" align="left"><?php $this->_($data['lastName']); ?></td>
                    <td valign="top" align="left"><?php $this->_($data['title']); ?></td>
                    <td valign="top" align="left"><?php $this->_($data['phoneWork']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php $AUIEO_CONTENT=ob_get_clean(); ?>
