<?php
/*  install_config.php: CLI PHP script to build a hastymail.rc file from the hastymail.conf file
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

if (!isset($argv[1])) {
    echo "Required argument \"input_file\" missing.\n\n\tUsage\n\tinstall_config input_file <output_file.php>\n\n";
    exit;
}
else {
    $conf = array();
    $site_config_file = $argv[1];
    $output = 'hastymail2.rc';
    if (isset($argv[2])) {
        $output = $argv[2];
    }
    if (is_readable($site_config_file)) {
        $lines = file($site_config_file);
        foreach ($lines as $line) {
            if (!trim($line)) {
                continue;
            }
            elseif (substr(trim($line), 0, 1) == '#') {
                continue;
            }
            elseif (!strstr($line, '=')) {
                continue;
            }
            else {
                list($name, $value) = explode('=', $line, 2);
                $name = trim(strtolower($name));
                $value = trim($value);
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
                else {
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
        echo "input_file was Unreadable\n\n";
        exit;
    }
    if (!empty($conf)) {
        if (is_readable($output)) {
            echo "Output file \"$output\" exists, exiting\n\n";
        }
        else {
            $handle = @fopen($output, "w");
            if (@fwrite($handle, serialize($conf))) {
                echo "Configuration file: ".$output." written successfully\n";
                @fclose($handle);
            }
            else {
                echo "Could not write the output file\n\n";
            }
        }
    }
}
