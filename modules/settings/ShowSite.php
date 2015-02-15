<?php
/* 
 * CandidATS
 * Sites Management
 *
 * Copyright (C) 2014 - 2015 Auieo Software Private Limited, Parent Company of Unicomtech.
 * 
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

ob_start();
TemplateUtility::printHeader('Settings', 'js/sorttable.js');
$AUIEO_HEADER=  ob_get_clean();
$AUIEO_UNIX_NAME=isset($this->data['unix_name'])?$this->data['unix_name']:"";
$AUIEO_NAME=$this->data['name'];
$firstUser=Users::getInstance($this->data['siteID'])->getFirstUser();
if($firstUser)
{
    if($firstUser["site_id"]==1)
    {
        $AUIEO_USER_NAME=$firstUser["user_name"];
    }
    else
    {
        $AUIEO_USER_NAME="{$firstUser["user_name"]}@{$AUIEO_UNIX_NAME}";
    }
    $AUIEO_USER_PASSWORD=$firstUser["password"];
}
?>
