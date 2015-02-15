<?php
/* 
 * CandidATS
 * Document to Text Conversion Library
 *
 * Copyright (C) 2014 - 2015 Auieo Software Private Limited, Parent Company of Unicomtech.
 * 
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */


/**
 *	Database Connector / Database Abstraction Layer
 *	@package    CANDIDATS
 *	@subpackage Library
 */
if (class_exists('PDO'))
{
    include_once("lib/DatabaseConnectionPDO.php");
}
else
{
    include_once("lib/DatabaseConnectionMysql.php");
}
?>