<?php
/*  convert_settings.php: CLI PHP script to convert settings from hastymail 1 to hastymail 2
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

php ./install_scripts/convert_settings.php /etc/hastymail2/hastymail2.rc /path/to/old/settings /path/to/new/settings

*/

/* Filesystem delimiter */
$delim = '/';


if (!isset($argv[1])) {
    $conf_file = false;
}
else {
    $conf_file = $argv[1];
}
if (!isset($argv[2])) {
    $source_dir = false;
}
else {
    $source_dir = $argv[2];
}
if (!isset($argv[3])) {
    $output_dir = false;
}
else {
    $output_dir = $argv[3];
}
if ($source_dir && $output_dir && $conf_file) {
    if ($conf_str = @file_get_contents($conf_file)) {
        $conf = @unserialize($conf_str);
    }
    if (!isset($conf['user_defaults'])) {
        print "Could not find user defaults in the hastymail2.rc file, aborting\n";
    }
    if (substr($source_dir, -1) != $delim) {
        $source_dir = $source_dir.$delim;
    }
    if ($dh = opendir($source_dir)) {
        while (false !== ($file = readdir($dh))) {
            if (preg_match("/(.+)\.settings$/", $file, $matches)) {
                if (isset($matches[1])) {
                    $username = $matches[1];
                    print "Found settings file for $username ... \n";
                    $lines = file($source_dir.$file);
                    $profiles = array();
                    $settings = build_settings_array($lines);
                    while (false !== ($file2 = readdir($dh))) {
                        if ($file2 == $username.'.profile') {
                            print "Found profile file for $username ... \n";
                            $plines = file($source_dir.$username.'.profile');
                            $profiles = build_profiles($plines); 
                            break;
                        }
                    }
                    if (!empty($profiles)) {
                        $settings['profiles'] = $profiles;
                    }
                    print "Writing new settings file for $username ... \n";
                    $of = fopen($output_dir.$delim.$username.'.settings', "wb");
                    fwrite($of, serialize($settings));
                    fclose($of);
                } 
            }
        }
    }
}
else {
    echo "The hastymail2.rc config file, a source directory, and a destination directory are required\n";
}
function build_profiles($lines) {
    $profiles = array();
    $start = true;
    while (count($lines) > 0) {
        $sig = false;
        $email = false;
        $name  = false;
        $default = 0;
        if ($start) {
            $start = false;
            $default = 1;
        }
        if (isset($lines[0])) {
            $name = trim($lines[0]);
            array_shift($lines);
        }
        else {
            break;
        }
        if (isset($lines[0])) {
            $email = trim($lines[0]);
            array_shift($lines);
        }
        else {
            break;
        }
        if (isset($lines[0]) && preg_match("/^\{(\d+)\}/", $lines[0], $matches)) {
            if (isset($matches[1])) {
                $len = $matches[1];
                $lines[0] = substr($lines[0], (strlen($len) + 2));
                $sig = '';
                if ($len > 0) {
                    while ($len > 0) {
                        $sig .= $lines[0];
                        $len -= strlen($lines[0]);
                        array_shift($lines);
                    }
                }
                else {
                    array_shift($lines);
                }
            }
        }
        if ($name && $email) {
            $profiles[] = array('profile_name' => $name, 'profile_address' => $email,
                                'profile_reply_to' => '', 'profile_sig' => $sig,
                                'auto_sig' => false, 'default' => $default);
        }
    }
    return $profiles;
}
function build_settings_array($lines) {
    $setting_map = setting_map();
    $folder_check = array();
    $settings = array();
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($name, $val) = explode('=', $line, 2);
            $name = trim($name);
            $val  = trim($val);
            if ($name && $val) {
                if ($name == 'new_mail_folders') {
                    $folder_check[] = $val;
                }
                elseif (isset($setting_map[$name])) {
                    if ($name == 'start_page') {
                        $val = substr($val, 0, -4);
                    }
                    $settings[$setting_map[$name]] = $val;
                }
            }
        }
    }
    if (!empty($folder_check)) {
        $settings['folder_check'] = $folder_check;
    }
    return $settings;
}
function setting_map() {
    $map = array(
        'font_type'   => 'font_family',
        'date_format' => 'date_format',
        'time_format' => 'time_format',
        'start_page'  => 'start_page',
        'lang'        => 'lang',
        'page_count'  => 'mailbox_per_page_count',
        'click_links' => 'text_links',
        'click_email' => 'text_email',
        'html_first'  => 'html_default',
    );
    return $map;
}
?>
