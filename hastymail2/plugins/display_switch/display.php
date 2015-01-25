<?php
    
function display_switch_simple_menu($tools) {
    
    $mailbox = $tools->get_mailbox();
    
    // Creates a link at the end of the menu for the Full Display
    return '<a href="?page=display_switch&amp;mailbox='.urlencode($mailbox).'">'.$tools->str[2].'</a>&#160; ';
    
}
function display_switch_menu($tools, $args) {
    
    if (isset($args['display_switch_menu'])) {
        return $args['display_switch_menu'];
    }
    $mailbox = $tools->get_mailbox();
    
    // Creates a link at the end of the menu for Simple Display
    return '<a href="?page=display_switch&amp;mailbox='.urlencode($mailbox).'">'.$tools->str[1].'</a>&#160; ';
    
}

?>
