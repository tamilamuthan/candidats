<?php
    
    function url_action_display_switch($tools, $get, $post) {
        
        // Verify that there's a user logged in, direct them to page not found if not
        if (!$tools->logged_in()) {
            $tools->page_not_found();
        }
        
        // Get the current mailbox the user is viewing, so I can redirect them back to it
        $mailbox = $tools->get_mailbox();
        
        // If they're in a mailbox, store it
        if ($mailbox) {
            $tools->set_mailbox($mailbox);
        }
        
        return $mailbox;
        
    }
    
    function print_display_switch($result, $tools) {
        
        // Grab the mailbox the user was in before they clicked the display switch link
        $mailbox = $tools->get_mailbox();
        
        // Check what the display mode is presently set to, so we can change it to the opposite
        $current_display_mode = $tools->get_setting('display_mode');
        
        // If default (1), set to simple (2) and vice versa
        if ($current_display_mode == 1) {
            // Changes display mode for the current session only; does not save any settings
            $_SESSION['user_settings']['display_mode'] = 2;
            
            // Use this if you want to change the setting
            //$tools->save_setting('display_mode', 2);
        } else {
            $_SESSION['user_settings']['display_mode'] = 1;
            
            //tools->save_setting('display_mode', 1);
        }
        
        // After changing the setting, take the user back to the mailbox they were in assuming no headers have been sent (should not be)
        if (!headers_sent()) {
            Header('Location:?page=mailbox&mailbox='.urlencode($mailbox));
        }
        
        // This should never get executed, but if the header got sent, we'll send back nothing to display
        $data = '<a href="?page=mailbox&mailbox='.urlencode($mailbox).'">Back to previous mailbox</a>';
        
        return $data;
        
    }
    
?>