<?php
/*  index.php: PHP web script to build a hastymail2.rc file from the hastymail.conf file
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

if (isset($_POST['create'])) {
    $site_config_file = false;
    if (isset($_FILES['conf_file'])) {
        $atts = $_FILES['conf_file'];
        if (isset($atts['tmp_name'])) {
            $site_config_file = $atts['tmp_name'];
        }
    }
    $conf = array();
    if (is_readable($site_config_file)) {
        $lines = file($site_config_file);
        foreach ($lines as $line) {
            if (!trim($line)) {
                continue;
            }
            elseif (substr(trim($line), 0, 1) == '#') {
                continue;
            }
            else {
                $name = false;
                $value = false;
                $parts = explode('=', $line, 2);
                if (isset($parts[0]) && trim($parts[0])) {
                    $name = trim(strtolower($parts[0]));
                }
                if (isset($parts[1]) && trim($parts[1])) {
                    $value = trim($parts[1]);
                }
                if ($name == 'theme') {
                    $val_bits = explode(',', $value);
                    $theme = false;
                    $css = false;
                    $icons = false;
                    $templates = false;
                    if (isset($val_bits[0])) {
                        $theme = $val_bits[0];
                    }
                    if (isset($val_bits[1]) && $val_bits[1] == 'true') {
                        $css = true;
                    }
                    if (isset($val_bits[2]) && $val_bits[2] == 'true') {
                        $icons = true;
                    }
                    elseif (isset($val_bits[2]) && $val_bits[2] == 'default') {
                        $icons = 'default';
                    }
                    if (isset($val_bits[3]) && $val_bits[3] == 'true') {
                        $templates = true;
                    }
                    if ($theme) {
                        $conf['site_themes'][$theme] = array('icons' => $icons, 'templates' => $templates, 'css' => $css);
                    }
                }
                elseif ($name == 'plugin') {
                    $conf['plugins'][] = $value;
                }
                elseif (substr($name, 0, 7) == 'default') {
                    if ($name == 'default_folder_check') {
                        $conf['user_defaults']['folder_check'][] = $value;
                    }
                    else {
                        if (strtolower($value) == 'true') {
                            $value = true;
                        }
                        elseif (strtolower($value) == 'false') {
                            $value = false;
                        }
                        $conf['user_defaults'][substr($name, 8)] = $value;
                    }
                }
                elseif ($name) {
                    if (strtolower($value) == 'true') {
                        $value = true;
                    }
                    elseif (strtolower($value) == 'false') {
                        $value = false;
                    }
                    $conf[$name] = $value;
                }
            }
        }
    }
    else {
        echo "input file was Unreadable\n\n";
    }
    if (!empty($conf)) {
        $data = serialize($conf);
        if (isset($_POST['download'])) { 
            header("Content-Type: text/plain");
            header('Content-Disposition: attachment; filename="hastymail2.rc"');
            header("Content-Length: ".strlen($data));
            echo $data;
        }
        else {
            echo '<textarea style="width: 600px; height: 400px; cols="80" rows="30">'.$data.'</textarea>';
        }
        exit; 
    }
}
echo '
<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head><title>Hastymail configuraton generator</title>
</head>
<body>
<div style="padding: 50px;">
Upload your hastymail.conf file and this script will produce a hastymail2.rc file.<br />
Uncheck the "Download file" to have the resulting hastymail2.rc file show in your browser.<br /><br />
<form method="post" action="" enctype="multipart/form-data">
<input type="file" name="conf_file" />
<input type="submit" name="create" value="create" />
<br />Download file <input type="checkbox" name="download" value="1" checked="checked" />
</form>
</div>
</body>
</html>';
