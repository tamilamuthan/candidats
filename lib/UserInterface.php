<?php
/**
 * CATS
 * User Interface Class
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
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
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: UserInterface.php 3593 2007-11-13 17:36:57Z andrew $
 */

/* 
 * CandidATS
 * Base class for controller
 *
 * Copyright (C) 2014 - 2015 Auieo Software Private Limited, Parent Company of Unicomtech.
 * 
 * This Modified Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

/**
 *	User Interface Library
 *	@package    CATS
 *	@subpackage Library
 */
class UserInterface extends ClsNaanalController
{
    protected $_moduleName = '';
    protected $_moduleTabText = '';
    protected $_subTabs = array();
    protected $_subTabsExternal = array();
    protected $_settingsEntries = array();
    protected $_settingsUserCategories = array();
    protected $_template;
    protected $_moduleDirectory = '';
    protected $_userID = -1;
    protected $_siteID = -1;
    protected $_accessLevel = -1;
    protected $_authenticationRequired = true;
    protected $_hooks = array();
    protected $_schema = array();
    private $objView=null;
    protected $isViewCalled=false;
    protected $isViewSet=false;

    public function __construct()
    {
        $this->_db = DatabaseConnection::getInstance();
        $this->_template = new Template();

        if (isset($_SESSION['CATS']) && !empty($_SESSION['CATS']))
        {
            /* Get the current user's user ID. */
            $this->_userID = $_SESSION['CATS']->getUserID();

            /* Get the current user's site ID. */
            $this->_siteID = $_SESSION['CATS']->getSiteID();

            /* Get the current user's access level. */
            $this->_accessLevel = $_SESSION['CATS']->getAccessLevel();

            /* All templates have an access level if we have a session. */
            $this->_template->assign('accessLevel', $this->_accessLevel);
        }
    }
    
    public function setView(&$objView)
    {
        $this->isViewSet=true;
        $this->objView=$objView;
    }
    
    public function &getView($id=false)
    {
        if($this->isViewSet)
        {
            $this->isViewCalled=true;
            if($id!==false)
            {
                $this->objView->setID($id);
            }
            return $this->objView;
        }
        return false;
    }
    
    public function isViewSet()
    {
        return $this->isViewSet;
    }
    public function isViewCalled()
    {
        return $this->isViewCalled;
    }
    
    public function updateField()
    {
        $moduleInfo=getTableInfoByModule($this->_moduleName);
        $_siteID = $_SESSION['CATS']->getSiteID();
        $sql="update auieo_fields set displaytype={$_REQUEST["checked"]} where data_item_type={$moduleInfo["data_item_type"]} and fieldname='{$_REQUEST["field_name"]}' and site_id={$_siteID}";
        $db=DatabaseConnection::getInstance();
        $db->query($sql);
        exit;
    }
    
    public function updateFieldReadonly()
    {
        $moduleInfo=getTableInfoByModule($this->_moduleName);
        $_siteID = $_SESSION['CATS']->getSiteID();
        $sql="update auieo_fields set readonly={$_REQUEST["checked"]} where data_item_type={$moduleInfo["data_item_type"]} and fieldname='{$_REQUEST["field_name"]}' and site_id={$_siteID}";
        $db=DatabaseConnection::getInstance();
        $db->query($sql);
        exit;
    }
    
    /**
     * Returns this module's name.
     *
     * @return string name of the module
     */
    public function getModuleName()
    {
        return $this->_moduleName;
    }
    
    public function &getTemplateObject()
    {
        return $this->_template;
    }
    
    public function isRendered()
    {
        return $this->_template->isRendered();
    }

    /**
     * Returns this module's tab text.
     *
     * @return string tab text of the module
     */
    public function getModuleTabText()
    {
        return $this->_moduleTabText;
    }
    
    public function getIcon()
    {
        if(isset($this->_moduleName))
        {
            if(file_exists("modules/{$this->_moduleName}/images/icon.gif"))
            {
                return "<img style='padding:0px;margin:0px;' src='modules/{$this->_moduleName}/images/icon.gif' width='24' height='24' alt='{$this->_moduleTabText}' style='border: none; margin-top: 3px;' />";
            }
            else if(file_exists("images/{$this->_moduleName}.gif"))
            {
                return "<img style='padding:0px;margin:0px;' src='images/{$this->_moduleName}.gif' width='24' height='24' alt='{$this->_moduleTabText}' style='border: none; margin-top: 3px;' />";
            }
        }
        return "";
    }
    
    /**
     * Returns this module's tab text.
     *
     * @return string tab text of the module
     */
    public function getModuleIcon()
    {
        return $this->_icon;
    }

    /**
     * Returns hooks defined by this module.
     *
     * @return array hooks
     */
    public function getHooks()
    {
        return $this->_hooks;
    }

    /**
     * Returns schema revisions defined by this module.
     *
     * @return array hooks
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Returns subtabs for this module as an array of strings.
     *
     * @return array subtab items for this module
     */
    public function getSubTabs($modules = array())
    {
        if (empty($modules))
        {
            return $this->_subTabs;
        }

        $subTabsExternal = $this->getThisSubTabsExternal($modules);
        return array_merge($this->_subTabs, $subTabsExternal);
    }

    /**
     * Returns subtabs for this module as an array of strings.
     *
     * @return array subtab items for this module
     */
    public function getSubTabsExternal()
    {
        if (isset($this->_subTabsExternal))
        {
            return $this->_subTabsExternal;
        }

        return false;
    }

    /**
     * Get a list of settings and their values pertaining to the
     * user interface.
     *
     * @return mixed Array or false on failure
     */
    public function getSettingsEntries()
    {
        if (isset($this->_settingsEntries))
        {
            return $this->_settingsEntries;
        }

        return false;
    }

    /**
     * Get a list of settings pertaining to user categories
     * for the user interface.
     *
     * @return mixed Array or false on failure
     */
    public function getSettingsUserCategories()
    {
        if (isset($this->_settingsUserCategories))
        {
            return $this->_settingsUserCategories;
        }

        return false;
    }

    /**
     * Returns whether or not the module requires authentication.
     *
     * @return boolean requires authentication
     */
    public function requiresAuthentication()
    {
        if (isset($this->_authenticationRequired))
        {
            return $this->_authenticationRequired;
        }

        return true;
    }

    /**
     * Returns the action name that a module was called with (the a=blah part
     * of the request URI).
     *
     * @return string action name
     */
    protected function getAction()
    {
        if (isset($_GET['a']) && !empty($_GET['a']))
        {
            return $_GET['a'];
        }

        return '';
    }

    /**
     * Returns true if the module/action was called with postback=postback
     * in the POST data.
     *
     * @return boolean is postback
     */
    protected function isPostBack()
    {
        if (isset($_POST['postback']))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the module/action was called with getback=getback
     * in the GET string.
     *
     * @return boolean is getback
     */
    protected function isGetBack()
    {
        if (isset($_GET['getback']))
        {
            return true;
        }

        return false;
    }

    /**
     * Print a fatal error and die.
     *
     * @param string error message
     * @param string module directory from which to load templates (optional)
     * @return void
     */
    protected function fatal($error, $directoryOverride = '')
    {
        if ($directoryOverride != '')
        {
            $moduleDirectory = $directoryOverride;
        }
        else
        {
            $moduleDirectory = $this->_moduleDirectory;
        }

        $this->_template->assign('active', $this);
        $this->_template->assign('errorMessage', $error);
        $this->_template->display(
            './modules/' . $moduleDirectory . '/Error.tpl'
        );

        $getArray = array();
        foreach ($_REQUEST as $index => $data)
        {
            $getArray[] = urlencode($index) . '=' . urlencode($data);
        }

        echo '<!--';
         trigger_error(
             str_replace("\n", " ", 'Fatal Error raised: ' . $error)
         );
        echo ' REQUEST: '.implode('&', $getArray).'-->';

        die();
    }

    /**
     * Print a fatal error and die (used in a modal dialog).
     *
     * @param string error message
     * @param string module directory from which to load templates (optional)
     * @return void
     */
    protected function fatalModal($error, $directoryOverride = '')
    {
        if ($directoryOverride != '')
        {
            $moduleDirectory = $directoryOverride;
        }
        else
        {
            $moduleDirectory = $this->_moduleDirectory;
        }

        $this->_template->assign('errorMessage', $error);
        $this->_template->display(
            './modules/' . $moduleDirectory . '/ErrorModal.tpl'
        );

        /*
        echo '<!--';
         trigger_error(
             str_replace("\n", " ", 'Fatal Modal Error raised: ' . $error)
         );
        echo '-->';
        */

        die();
    }

    /**
     * Returns true if a required numeric ID ($key) is a) present in $request,
     * b) not empty, and c) a digit / whole number. ID cannot be '0' unless
     * $allowZero is true.
     *
     * @param string request key name of ID
     * @param array $_GET, $_POST, or $_REQUEST
     * @param boolean allow ID to be 0
     * @return void
     */
    protected function isRequiredIDValid($key, $request, $allowZero = false)
    {
        if (isset($request[$key]) && (!empty($request[$key]) ||
            ($allowZero && $request[$key] == '0')))
        {
            if(ctype_digit((string) $request[$key]))
                return true;
            ///walk through the array and check whether it is valid id
            else if(is_array($request[$key]))
            {
                foreach($request[$key] as $val)
                {
                    if(!ctype_digit((string) $val))
                        return false;
                }
                return true;
            }
            ///if it is json, convert it and check for valid id
            else if($arrCandidateID=json_decode($request[$key]))
            {
                if(isset($arrCandidateID) && !empty($arrCandidateID))
                {
                    foreach($request[$key] as $val)
                    {
                        if(!ctype_digit((string) $val))
                            return false;
                    }
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns true if an optional numeric ID ($key) is a) present in $request,
     * b) not empty, and c) a digit / whole number, or -1.
     *
     * @param string request key name of ID
     * @param array $_GET, $_POST, or $_REQUEST
     * @return void
     */
    protected function isOptionalIDValid($key, $request)
    {
        if (isset($request[$key]) && !empty($request[$key]) &&
            ($request[$key] == '-1' || ctype_digit((string) $request[$key])))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns true if a checkbox by the name of $key is checked in $request.
     *
     * @param string Request variable name / key.
     * @param array $_GET, $_POST, or $_REQUEST
     * @return boolean Is checkbox checked?
     */
    protected function isChecked($key, $request)
    {
        if (isset($request[$key]) && !empty($request[$key]) &&
            $request[$key] != 'false' && $request[$key] != 'off')
        {
            return true;
        }

        return false;
    }

    /**
     * Returns trim()'d form input if $key is in $request; otherwise ''.
     *
     * @param string Request variable name / key.
     * @param array $_GET, $_POST, or $_REQUEST
     * @return string Trimmed value or ''.
     */
    protected function getTrimmedInput($key, $request)
    {
        if (isset($request[$key]))
        {
            return trim($request[$key]);
        }

        return '';
    }

    /**
     * Returns valid subtabs for this module.
     *
     * @return array subtab items for this module
     */
    public function getThisSubTabsExternal($modules)
    {
        $ret = array();

        foreach ($modules as $moduleName => $parameters)
        {
            $subTabsExternal = $parameters[2];

            if ($subTabsExternal != false)
            {
                foreach ($subTabsExternal as $moduleNameTab => $theSubTab)
                {
                    if ($moduleNameTab === $this->_moduleName)
                    {
                        $ret = array_merge($ret, $theSubTab);
                    }
                }
            }
        }

        return $ret;
    }
    
    public function editemail ()
    {
        $db = DatabaseConnection::getInstance();	
        $email_history_id=trim($_REQUEST["email_history_id"]);
        $rs = $db->getAllAssoc(sprintf(
    'SELECT recipients, from_address, text '
    . 'FROM email_history '
    . 'WHERE  email_history_id=%s' ,
$email_history_id
        ));

        if($rs)
        {
           /* $emailTemplates = new EmailTemplates($this->_siteID);
            $emailTemplatesRS = $emailTemplates->getAll();
            $arrTpl["emailTemplatesRS"]=$emailTemplatesRS;*/
            $arrTmp=explode("Message:",$rs[0]["text"]);
            $subject= $arrTmp[0];
            $message=$arrTmp[1];
            $arrSubj=explode("Subject:",$subject);
            $this->_template->assign('active', $this);
            $this->_template->assign('message', $message);
            $this->_template->assign('subject', $arrSubj[1]);
            $this->_template->assign('recipient', $rs[0]["recipients"]);
            $this->_template->assign('from', $rs[0]["from_address"]);

            //$this->_template->display("./modules/{$this->_moduleName}/editemail.php");
            $this->_template->display($this->getModuleTemplate("editemail"));
        }
        else
        {
            die("Unknown EMail details requested");
        }	
    }
    
    public function getModuleTemplate($template)
    {
        $filename="./modules/{$this->_moduleName}/{$template}.php";
        if(!file_exists($filename))
        {
            $filename="./auieo/common/template/{$template}.php";
        }
        return $filename;
    }
    
    public function onEditemail()
    {

    }
    
    public function transferto()
    {
        if ($this->_accessLevel < ACCESS_LEVEL_EDIT)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }
        if($this->_moduleName=="candidates")
        {
            $module = new Candidates($this->_siteID);
        }
        else if($this->_moduleName=="companies")
        {
            $module = new Companies($this->_siteID);
        }
        else if($this->_moduleName=="joborders")
        {
            $module = new JobOrders($this->_siteID);
        }
        /* Bail out if we don't have a valid candidate ID. */
        if (!isset($module))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, "Invalid Module: {$this->_moduleName}.");
            return;
        }
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('dataItemID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, "Invalid {$this->_moduleName} ID.");
            return;
        }
        /* Update the module record. */
        $updateSuccess = $module->updateSite(
            $_GET['dataItemID'],
            $_GET["siteID"]
        );
        if (!$updateSuccess)
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, "Failed to update {$this->_moduleName}.");
        }
        $this->_template->assign('active', $this);
        $this->_template->display('./modules/Settings/transferstatus.php');
    }
    
    public function copyto()
    {
        if ($this->_accessLevel < ACCESS_LEVEL_EDIT)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }
        if($this->_moduleName=="candidates")
        {
            $module = new Candidates($this->_siteID);
        }
        else if($this->_moduleName=="companies")
        {
            $module = new Companies($this->_siteID);
        }
        else if($this->_moduleName=="joborders")
        {
            $module = new JobOrders($this->_siteID);
        }
        /* Bail out if we don't have a valid candidate ID. */
        if (!isset($module))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, "Invalid Module: {$this->_moduleName}.");
            return;
        }
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('dataItemID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, "Invalid {$this->_moduleName} ID.");
            return;
        }
        /* Update the candidate record. */
        $updateSuccess = $module->copyRecord(
            $_GET['dataItemID'],
            $_GET["siteID"]
        );
        if (!$updateSuccess)
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, "Failed to update {$this->_moduleName}.");
        }
        $this->_template->assign('active', $this);
        $this->_template->display('./modules/Settings/copystatus.php');
    }
}

?>
