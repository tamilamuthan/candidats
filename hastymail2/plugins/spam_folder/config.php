<?php

/*  config.php: Plugin file responsible for defining how the plugin interacts with Hastymail 
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

$spam_folder_hooks = array(
    'work_hooks'        => array('init', 'update_settings', 'before_logout'),
    'display_hooks'     => array('folder_options_table', 'mailbox_controls_1'),
);
$spam_folder_langs = array(
    'en_US' => array(
        0 => 'Spam Folder',
        1 => 'Automatically delete spam on logout after this many days',
        2 => 'Empty Spam',
        3 => 'Are you sure you want to empty the SPAM folder?',
    ),
);
?>
