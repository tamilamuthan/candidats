<?php
pageHeaderInclude('js/highlightrows.js');
pageHeaderInclude('js/sweetTitles.js');
pageHeaderInclude('js/dataGrid.js');
pageTitle('Activities');
ob_start();

            ?>
<div style="text-align: right;"><?php $this->dataGrid->printNavigation(false); ?>&nbsp;&nbsp;<?php echo($this->quickLinks); ?></div>
            <p class="note">
                <span style="float:left;">Activities - Page <?php echo($this->dataGrid->getCurrentPageHTML()); ?></span>
                <span style="float:right;">
                    <?php $this->dataGrid->drawRowsPerPageSelector(); ?>
                    <?php $this->dataGrid->drawShowFilterControl(); ?>
                </span>&nbsp;
            </p>

            <?php $this->dataGrid->drawFilterArea(); ?>
            <?php $this->dataGrid->draw();  ?>

            <div style="display:block;">
                <span style="float:left;">
                    <?php $this->dataGrid->printActionArea(); ?>
                </span>
                <span style="float:right;">
                    <?php $this->dataGrid->printNavigation(true); ?>
                </span>&nbsp;
            </div>

            

            <?php  
        
$AUIEO_CONTENT=ob_get_clean();

?>