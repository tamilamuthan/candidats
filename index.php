<?php
/*
 * CATS
 * Index (Delegation Module)
 *
 * CATS Version: 0.8.0 (Jhelum)
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/. Software distributed under the License is
 * distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * rights and limitations under the License.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * A properly formatted query string will look like this:
 *
 *    /index.php?m=candidates&a=edit&candidateID=55
 *
 *
 * $Id: index.php 3807 2007-12-05 01:47:41Z will $
 */

/* 
 * CandidATS
 * Document to Text Conversion Library
 *
 * Copyright (C) 2014 - 2015 Auieo Software Private Limited, Parent Company of Unicomtech.
 * 
 * This Modified Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

define("DEVELOPER_IP","");
if(!isset($_GET['m']) && isset($_GET['module']))
{
    $_GET['m']=$_GET['module'];
    $_REQUEST['m']=$_REQUEST['module'];
}
if(!isset($_GET['a']) && isset($_GET['action']))
{
    $_GET['a']=$_GET['action'];
    $_REQUEST['a']=$_REQUEST['action'];
}
if(isset($_GET['m']) && $_GET['m']=="MailManager")
{
    $_GET['module']="mailclient";
    $_GET['m']="mailclient";
    $_REQUEST['module']="mailclient";
    $_REQUEST['m']="mailclient";
}
//function trace($message){die($message);}
include_once("debug.php");
spl_autoload_register(function ($class) { 


    $pathClass = 'lib/' . $class . '.php';

    if (file_exists($pathClass)) 
    {
        require_once $pathClass;
    } 
    else
    {
        return null;
    }
});
define("AUIEO_FRAMEWORK_PATH", "");
define("AUIEO_APP_PATH", "");
//require_once("E:\\auieo\\project\\naanal\\testing.php");
include_once("lib/ClsNaanalController.php");
include_once("lib/ClsNaanalApplication.php");
include_once("lib/ClsLInputValidator.php");
include_once("lib/ClsAuieoModule.php");
include_once("lib/ModuleEmailTemplate.php");
include_once("lib/SearchDataStructure.php");
include_once("lib/Pagination.php");
include_once("lib/PaginationUI.php");
include_once("lib/ClsNaanalLibrary.php");
include_once("lib/ClsAuieoDataStructure.php");
include_once("lib/ClsAuieoData.php");
include_once("lib/ClsAuieoFluidArray.php");
include_once("lib/ClsAuieoArray.php");
include_once("lib/ClsAuieoSQL.php");
include_once("lib/ClsAuieoModuleViewer.php");
include_once("lib/ClsAuieoViewerBase.php");
include_once("lib/PRGManagement.php");
include_once("lib/PRGGroup.php");
include_once("mvc/viewers/ClsAuieoView.php");
//ClsAuieoTestGen::render("http://127.0.0.1/candidats/");
/* Do we need to run the installer? */
if (!file_exists('INSTALL_BLOCK') && !isset($_POST['performMaintenence']))
{
    include('modules/install/notinstalled.php');
    die();
}

// FIXME: Config file setting.
@ini_set('memory_limit', '64M');

/* Hack to make CATS work with E_STRICT. */
if (function_exists('date_default_timezone_set'))
{
    @date_default_timezone_set(date_default_timezone_get());
}

if(!class_exists("ClsNaanalPDO"))include_once("./lib/ClsNaanalPDO.php");
include_once('./config.php');
include_once('./constants.php');
include_once('./lib/vendor/autoload.php');
Logger::configure('logger.xml');
Logger::getLogger("AuieoATS")->info("Start....");
include_once("utils.php");
include_once("./lib/Modules.php");
include_once('./lib/CommonErrors.php');
include_once('./lib/CATSUtility.php');
include_once('./lib/DatabaseConnection.php');
include_once('./lib/Template.php');
include_once('./lib/Users.php');
include_once('./lib/MRU.php');
include_once('./lib/Hooks.php');
include_once('./lib/Session.php'); /* Depends: MRU, Users, DatabaseConnection. */
include_once('./lib/UserInterface.php'); /* Depends: Template, Session. */
include_once('./lib/ModuleUtility.php'); /* Depends: UserInterface */
include_once('./lib/TemplateUtility.php'); /* Depends: ModuleUtility, Hooks */
include_once("./lib/ClsNaanalFilter.php");
include_once("./modules/install/extra.php");
/* Give the session a unique name to avoid conflicts and start the session. */
@session_name(CATS_SESSION_NAME);
session_start();
//include_once("migrateextrafield.php");
/* Try to prevent caching. */
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

/* Make sure we aren't getting screwed over by magic quotes. */
if (get_magic_quotes_runtime())
{
    set_magic_quotes_runtime(0);
}
if (get_magic_quotes_gpc())
{
    include_once('./lib/ArrayUtility.php');

    //$_GET     = array_map('stripslashes', $_GET);
    //$_POST    = array_map('stripslashes', $_POST);
    //$_REQUEST = array_map('stripslashes', $_REQUEST);
    $_GET     = ArrayUtility::arrayMapKeys('stripslashes', $_GET);
    $_POST    = ArrayUtility::arrayMapKeys('stripslashes', $_POST);
    $_REQUEST = ArrayUtility::arrayMapKeys('stripslashes', $_REQUEST);
}

/* Objects can't be stored in the session if session.auto_start is enabled. */
if (ini_get('session.auto_start') !== '0' &&
    ini_get('session.auto_start') !== 'Off')
{
    die('CATS Error: session.auto_start must be set to 0 in php.ini.');
}
define("URL_BASE_FILE_NAME",  CATSUtility::getIndexName());
/* Proper extensions loaded?! */
if (!function_exists('mysql_connect') || !function_exists('session_start'))
{
    die('CATS Error: All required PHP extensions are not loaded.');
}

/* Make sure we have a Session object stored in the user's session. */
if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
{
    $_SESSION['CATS'] = new CATSSession();
}

/* Start timer for measuring server response time. Displayed in footer. */
$_SESSION['CATS']->startTimer();

/* Check to see if the server went through a SVN update while the session
 * was active.
 */
$_SESSION['CATS']->checkForcedUpdate();


/* Check to see if the user level suddenly changed. If the user was changed to disabled,
 * also log the user out.
 */
// FIXME: This is slow!
if ($_SESSION['CATS']->isLoggedIn())
{
    $users = new Users($_SESSION['CATS']->getSiteID());
    $forceLogoutData = $users->getForceLogoutData($_SESSION['CATS']->getUserID());

    if (!empty($forceLogoutData) && ($forceLogoutData['forceLogout'] == 1 ||
        $_SESSION['CATS']->getRealAccessLevel() != $forceLogoutData['accessLevel']))
    {
        $_SESSION['CATS']->setRealAccessLevel($forceLogoutData['accessLevel']);

        if ($forceLogoutData['accessLevel'] == ACCESS_LEVEL_DISABLED ||
            $forceLogoutData['forceLogout'] == 1)
        {
            /* Log the user out. */
            $unixName = $_SESSION['CATS']->getUnixName();

            $_SESSION['CATS']->logout();
            unset($_SESSION['CATS']);
            unset($_SESSION['modules']);

            $URI = 'm=login';

            if (!empty($unixName) && $unixName != 'demo')
            {
                $URI .= '&s=' . $unixName;
            }

            CATSUtility::transferRelativeURI($URI);
            die();
        }
    }
}

/* Check to see if we are supposed to display the career page. */
if (((isset($careerPage) && $careerPage) ||
    (isset($_REQUEST['showCareerPortal']) && $_REQUEST['showCareerPortal'] == '1')))
{
    ModuleUtility::loadModule('careers');
}
else if (((isset($jobboard) && $jobboard) ||
    (isset($_REQUEST['jobboard']) && $_REQUEST['jobboard'] == '1')))
{
    ModuleUtility::loadModule('jobboard');
}
/* Check to see if we are supposed to display an rss page. */
else if (isset($rssPage) && $rssPage)
{
    ModuleUtility::loadModule('rss');
}

else if (isset($xmlPage) && $xmlPage)
{
    ModuleUtility::loadModule('xml');
}

/* Check to see if the user was forcibly logged out (logged in from another browser). */
else if ($_SESSION['CATS']->isLoggedIn() &&
    (!isset($_REQUEST['m']) || ModuleUtility::moduleRequiresAuthentication($_REQUEST['m'])) &&
    $_SESSION['CATS']->checkForceLogout())
{
    Logger::getLogger("AuieoATS")->info("index:before session logout");
    // FIXME: Unset session / etc.?
    $_SESSION['CATS']->logout();
    Logger::getLogger("AuieoATS")->info("index:after session logout");
    Logger::getLogger("AuieoATS")->info("index:before loading login module");
    ModuleUtility::loadModule('login');
    Logger::getLogger("AuieoATS")->info("index:after loading login module");
}

/* If user specified a module, load it; otherwise, load the home module. */
else if (!isset($_REQUEST['m']) || empty($_REQUEST['m']))
{
    Logger::getLogger("AuieoATS")->info("index:if module not set or empty start");
    if ($_SESSION['CATS']->isLoggedIn())
    {
        $_SESSION['CATS']->logPageView();

        if (!eval(Hooks::get('INDEX_LOAD_HOME'))) return;

        ModuleUtility::loadModule('home');
    }
    else
    {
        $_SESSION['CATS']->logout();
        ModuleUtility::loadModule('login');
    }
    Logger::getLogger("AuieoATS")->info("index:if module not set or empty end");
}
else
{
    if ($_REQUEST['m'] == 'logout')
    {
        Logger::getLogger("AuieoATS")->info("index:if module is logout start");
        /* There isn't really a logout module. It's just a few lines. */
        $unixName = $_SESSION['CATS']->getUnixName();

        $_SESSION['CATS']->logout();
        unset($_SESSION['CATS']);
        unset($_SESSION['modules']);

        $URI = 'm=login';
                                 /* Local demo account doesn't relogin. */
        if (!empty($unixName) && $unixName != 'demo')
        {
            $URI .= '&s=' . $unixName;
        }

        if (isset($_REQUEST['message']))
        {
            $URI .= '&message=' . urlencode($_REQUEST['message']);
        }

        if (isset($_REQUEST['messageSuccess']))
        {
            $URI .= '&messageSuccess=' . urlencode($_REQUEST['messageSuccess']);
        }

        /* catsone.com demo domain doesn't relogin. */
        if (strpos(CATSUtility::getIndexName(), '://demo.catsone.com') !== false)
        {
            CATSUtility::transferURL('http://www.catsone.com');
        }
        else
        {
            CATSUtility::transferRelativeURI($URI);
        }
        Logger::getLogger("AuieoATS")->info("index:if module is logout end");
    }
    else if (!ModuleUtility::moduleRequiresAuthentication($_REQUEST['m']))
    {
        Logger::getLogger("AuieoATS")->info("index:if module require authentication start");
        /* No authentication required; load the module. */
        ModuleUtility::loadModule($_REQUEST['m']);
        Logger::getLogger("AuieoATS")->info("index:if module require authentication end");
    }
    else if (!$_SESSION['CATS']->isLoggedIn())
    {
        Logger::getLogger("AuieoATS")->info("index:if session logged in start");
        /* User isn't logged in and authentication is required; send the user
         * to the login page.
         */
        ModuleUtility::loadModule('login');
        Logger::getLogger("AuieoATS")->info("index:if session logged in end");
    }
    else
    {
        Logger::getLogger("AuieoATS")->info("index:if before pageview and load module start");
        /* Everything's good; load the requested module. */
        $_SESSION['CATS']->logPageView();
        ModuleUtility::loadModule($_REQUEST['m']);
        Logger::getLogger("AuieoATS")->info("index:if before pageview and load module end");
    }
}

if (isset($errorHandler))
{
    $errorHandler->reportErrors();
}
Logger::getLogger("AuieoATS")->info("End....");
?>
