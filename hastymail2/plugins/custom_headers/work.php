<?php

/*  work.php: Plugin file responsible for the backend processing
    Copyright (C) 2002-2010  Hastymail Development group

    This file is part of Hastymail.

    Hastymail is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Hastymail is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Hastymail; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

* $Id:$
*/
function custom_headers_init($tools) {
    $opts = $tools->str;
    $tools->save_to_global_store('help_strings', $opts);
}
function custom_headers_message_send($tools) {
    if (isset($_POST['custom_header_type']) && isset($_POST['custom_header_value'])) {
        $tools->add_outgoing_header($_POST['custom_header_type'], $_POST['custom_header_value']);
    }
}
function custom_headers_update_settings($tools) {
    if (isset($_POST['custom_header_enabled']) && $_POST['custom_header_enabled']) {
        $tools->save_options_page_setting('custom_header_enabled', 1);
    }
    else {
        $tools->save_options_page_setting('custom_header_enabled', 0);
    }
}
?>
