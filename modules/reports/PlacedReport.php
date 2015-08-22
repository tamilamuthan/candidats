<?php

ob_start();
 ?>
    <p class="note">Placements</p>

    <?php foreach ($this->placementsJobOrdersRS as $rowNumber => $placementsJobOrdersData): ?>
        <span style="font: normal normal bold 13px/130% Arial, Tahoma, sans-serif;"><?php $this->_($placementsJobOrdersData['title']) ?> at <?php $this->_($placementsJobOrdersData['companyName']) ?> (<?php $this->_($placementsJobOrdersData['ownerFullName']) ?>)</span>
        <br />
        <table class="sortable" width="100%">
            <tr>
                <th align="left" nowrap="nowrap">First Name</th>
                <th align="left" nowrap="nowrap">Last Name</th>
                <th align="left" nowrap="nowrap">Candidate Owner</th>
                <th align="left" nowrap="nowrap">Date Placed</th>
            </tr>

            <?php foreach ($placementsJobOrdersData['placementsRS'] as $rowNumber => $placementsData): ?>
                <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                    <td valign="top" align="left"><?php $this->_($placementsData['firstName']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($placementsData['lastName']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($placementsData['ownerFullName']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($placementsData['dateSubmitted']) ?>&nbsp;</td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endforeach; ?>
<?php $AUIEO_CONTENT=ob_get_clean(); ?>
