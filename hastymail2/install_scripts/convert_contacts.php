<?php
/*  convert_contacts.php: CLI PHP script to convert contacts from hastymail 1 to hastymail 2
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

/*
Usage:

php ./install_scripts/convert_contacts.php /path/to/old/contacts /path/to/new/contacts

This script will write out new contacts files from the old hastymail1 format to the new
format used by hastymail2. You must supply a source and destination directory. The script
assumes a file delimiter of "/" which you can change if need be below.

Files created by this script must be made writable by the webserver otherwise
users will NOT be able to edit there contacts
*/

/* Filesystem delimiter */
$delim = '/';


if (!isset($argv[1])) {
    $source_dir = false;
}
else {
    $source_dir = $argv[1];
}
if (!isset($argv[2])) {
    $output_dir = false;
}
else {
    $output_dir = $argv[2];
}
if ($source_dir && $output_dir) {
    if (substr($source_dir, -1) != $delim) {
        $source_dir = $source_dir.$delim;
    }
    if ($dh = opendir($source_dir)) {
        while (false !== ($file = readdir($dh))) {
            if (preg_match("/(.+)\.contacts$/", $file, $matches)) {
                if (isset($matches[1])) {
                    $username = $matches[1];
                    print "Found contact file for $username ... ";
                    $lines = file($source_dir.$file);
                    $contacts = build_contact_data($lines);
                    $of = fopen($output_dir.$delim.$username.'.contacts', "wb");
                    fwrite($of, serialize($contacts));
                    fclose($of);
                } 
            }
        }
    }
}
else {
    echo "Both a source directory and a destination directory are required\n";
}
function build_contact_data($lines) {
    $contacts = array();
    foreach ($lines as $line) {
        $flds = explode('\|', $line, 4);
        if (isset($flds[1]) && isset($flds[2])) {
            $name = array(
                'group'      => '',
                'value'      => $flds[2],
                'name'       => 'FN',
                'properties' => array(),
            );
            $email = array(
                'group'      => 'A',
                'value'      => $flds[1],
                'name'       => 'EMAIL',
                'properties' => array(),
            );
            $contacts[] = array($name, $email);
        }
    }
    echo "converted ".count($contacts)." contacts\n";
    return $contacts;
}
?>
