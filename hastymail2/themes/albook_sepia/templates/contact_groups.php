<?php
/*  contacts.php: Contacts page template 
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

/* don't let this file be loaded by the browser directly */
if (!isset($pd) || !is_object($pd)) {
    exit;
}?>

<!-- Main contacts page div and heading -->
<div id="contacts_page"><?php echo do_display_hook('contacts_page_top') ?>
    <h2 id="mailbox_title2">
        <?php echo $pd->user->str[8] ?>
    </h2>
        <div id="contact_links">
            <a href="?page=contacts&amp;mailbox=<?php echo urlencode($pd->pd['mailbox']) ?>"><?php echo 'Back to contacts' ?></a>
            <?php echo do_display_hook('contacts_quick_links') ?>
        </div>

    <!-- Existing contact groups -->
    <?php echo $pd->print_existing_contact_groups() ?>

    <!-- Manage groups -->
    <?php echo $pd->print_manage_contact_groups() ?>
</div>
