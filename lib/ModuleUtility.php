<?php
/**
 * CATS
 * Module Utility Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc and partly by Unicomtech. Tamil Amuthan - info@unicomtech.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/ and Mozilla Public License Version 1.2.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition" and "CandidATS".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 * Portions created by Tamil Amuthan is Copyright to Unicomtech
 *
 *
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: ModuleUtility.php 3774 2007-11-30 18:17:49Z brian $
 */

/**
 *  Module Utility Library
 *  @package    CATS
 *  @subpackage Library
 */
class ModuleUtility
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}


    /**
     * Loads a module.
     *
     * @param string module name
     * @return void
     */
    public static function loadModule($moduleName)
    {
        Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule entry");
        $modules = self::getModules();

        if (!isset($modules[$moduleName]))
        {
            Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if module not set entry");
            if (class_exists('CommonErrors'))
            {
                CommonErrors::fatal(COMMONERROR_INVALIDMODULE, NULL, $moduleName);
            }
            else
            {
                echo ('Invalid module name \'' . htmlspecialchars($moduleName)
                     . '\'.<br />Is the module installed?!');
                die();
            }
            Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if module not set end");
        }

        $moduleClass = $modules[$moduleName][0];
        if($_SESSION["CATS"]->getSiteID()>0)
        {
             if(defined("AUIEO_API") && isset($_REQUEST["operation"])){}
             else
             {
                Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if siteid greater than 0 start");
                $objPRGManagement=PRGManagement::getInstance();
                $permit=$objPRGManagement->isModuleActionPermitted();
                if($permit===false)
                {
                    Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if siteid not permitted");
                    CommonErrors::fatal(COMMONERROR_PERMISSION, NULL, 'You have no permission to access this.');
                }
                Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if siteid greater than 0 end");
             }
        }
        include_once(
            'modules/' . $moduleName . '/'
            . $moduleClass . '.php'
        );

        if (!eval(Hooks::get('LOAD_MODULE'))) return;

        $objModuleController = new $moduleClass();
        $actionExist=false;
        $ret=null;
        //$moduleModelClass="Cls".ucfirst($moduleName)."Model";
        //$moduleViewClass="Cls".ucfirst($moduleName)."View";
        $moduleActionModelClass="";
        $moduleActionViewClass="";
        if (isset($_REQUEST['a']) && !empty($_REQUEST['a']))
        {
            $action=$_REQUEST['a'];
        }
        else if(isset($_REQUEST['p']) && $_REQUEST['p']!="onApplyToJobOrder")
        {
            $action=$_REQUEST['p'];
        }
        else
        {
            if($moduleName=="login" || isset($_REQUEST["sessionName"]))
            {
                $action="attemptLogin";
            }
            else
                $action="listing";
        }
        
        $moduleActionViewClass="Cls".ucfirst($moduleName).ucfirst($action)."View";
        $moduleActionModelClass="Cls".ucfirst($moduleName).ucfirst($action)."Model";
        $actionMethod=$action;
        if (isset($_POST['postback']) || isset($_GET['getback']))
        {
            $actionMethod = "on" . ucfirst($action);
        }
        /**
         * set model object if exist
         */
        if(!class_exists($moduleActionModelClass) && file_exists("modules/{$moduleName}/{$moduleActionModelClass}.php"))
        {
            Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if module controller not exist and module template file exist start");
            include_once "modules/{$moduleName}/{$moduleActionModelClass}.php";
            Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if module controller not exist and module template file exist end");
        }
        $objModel=null;
        if(class_exists($moduleActionModelClass))
        {
            $objModel=new $moduleActionModelClass();
        }
        
        /**
         * set view object if exist
         */
        if(!class_exists($moduleActionViewClass) && file_exists("modules/{$moduleName}/{$moduleActionViewClass}.php"))
        {
            Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if module view not exist and module view template file exist start");
            include_once "modules/{$moduleName}/{$moduleActionViewClass}.php";
            Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if module view not exist and module view template file exist end");
        }
        $objView=null;
        if(class_exists($moduleActionViewClass))
        {
            Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if module view exist start");
            if(is_null($objModel))
                $objView=new $moduleActionViewClass();
            else
                $objView=new $moduleActionViewClass($objModel);
            if(method_exists($objModuleController, "setView"))
            {
                $objModuleController->setView($objView);
            }
            Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if module view exist end");
        }
        if(defined("AUIEO_API"))
        {
            include_once("lib/api.php");
            $api = new API();
            $suceess=$api->processApi();
            /**
             * if request is proper process the request
             */
            if($suceess)
            {
                $webserviceMethod="webservice".  ucfirst($action);
                if(method_exists($objModuleController, $webserviceMethod))
                {
                    $ret=$objModuleController->$webserviceMethod($api);
                    Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if request from API end");
                    exit;
                }
            }
            else
            {
                Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if request from API end");
                exit;
            }
            Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if request from API end");
        }
        ob_start();
        if(method_exists($objModuleController, $actionMethod))
        {
            Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if action method exist in controller start");
            $ret=$objModuleController->$actionMethod();
            Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if action method exist in controller end");
        }
        else if(method_exists($objModuleController, $action))
        {
            Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if action exist in controller start");
            $ret=$objModuleController->$action();
            Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if action exist in controller end");
        }
        else
        {
            Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if render or handleRequest method exist in controller start");
            if(method_exists($objModuleController, "render"))
                $ret=$objModuleController->render();
            else
                $ret=$objModuleController->handleRequest();
            Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule if handleRequest method exist in controller end");
        }
        /**
         * if it is werservice and if the method exist, the control should not come here.
         * Since it came, it means the webservice method not exist
         */
        if(defined("AUIEO_API"))
        {
            $api->response('',404);
            exit;
        }
        $objTemplate=$objModuleController->getTemplateObject();
        if(!$objTemplate->isRendered())
        {
            if(isset($_REQUEST["file"]))
            {
                include_once("./modules/{$moduleName}/{$_REQUEST["file"]}.php");
            }
            if(isset($objTemplate->errMessage) && $objTemplate->errMessage && file_exists("./modules/{$moduleName}/ErrorMessage.php"))
            {
                $tplfile="./modules/{$moduleName}/ErrorMessage.php";
            }
            else if($action=="listing")
            {
                if(!isset($objTemplate->totalRecords) || $objTemplate->totalRecords>0 || !file_exists("./modules/{$moduleName}/{$action}.php"))
                {
                    $tplfile="./modules/{$moduleName}/{$action}.php";
                }
                else
                {
                    $tplfile="./modules/{$moduleName}/NoRecord.php";
                }
            }
            else
            {
                $tplfile="./modules/{$moduleName}/{$action}.php";
            }
            if($objModuleController->isViewSet())
            {
                $objTemplate->display($tplfile,$objModuleController->getView());
            }
            else
            {
                $objTemplate->display($tplfile);
            }
        }
        $AUIEO_TEMPLATE_CONTENT=ob_get_clean();
        if(defined("AUIEO_API"))
        {
            echo $AUIEO_TEMPLATE_CONTENT;
        }
        if(file_exists("./modules/{$moduleName}/module_template.php"))
        {
            ob_start();
            include "./modules/{$moduleName}/module_template.php";
            echo ob_get_clean();
        }
        else
        {
            echo $AUIEO_TEMPLATE_CONTENT;
        }
        Logger::getLogger("AuieoATS")->info("ModuleUtility:loadModule exit");
    }

    /**
     * Check each module for a tasks directory which contains events that need
     * to be registered with the Asychroneous Queue Processor.
     */
    public static function registerModuleTasks()
    {
        Logger::getLogger("AuieoATS")->info("ModuleUtility:registerModuleTasks entry");
        $modules = self::getModules();

        foreach ($modules as $moduleName => $moduleData)
        {
            $moduleClass = $moduleData[0];

            if (file_exists($taskFile =
                sprintf('./modules/%s/tasks/tasks.php',
                    $moduleName)))
            {
                include_once($taskFile);
            }
        }
        Logger::getLogger("AuieoATS")->info("ModuleUtility:registerModuleTasks exit");
    }

    /**
     * Checks whether or not a module requires authentication.
     *
     * @param string module name
     * @return boolean requires authentication
     */
    public static function moduleRequiresAuthentication($moduleName)
    {
        Logger::getLogger("AuieoATS")->info("ModuleUtility:registerModumoduleRequiresAuthenticationleTasks entry");
        $modules = self::getModules();

        if (!isset($modules[$moduleName]))
        {
            /* Module doesn't exist; take them to the login page if not
             * logged in. If they are logged in, self::loadModule will throw
             * an invalid module error.
             */
            return true;
        }

        $moduleClass = $modules[$moduleName][0];

        include_once(
            'modules/' . $moduleName . '/'
            . $moduleClass . '.php'
        );

        $module = new $moduleClass();

        if (!method_exists($module, 'requiresAuthentication'))
        {
            /* If the module doesn't specify, assume it requires
             * authentication.
             */
            return true;
        }

        $ret = $module->requiresAuthentication();
        Logger::getLogger("AuieoATS")->info("ModuleUtility:registerModumoduleRequiresAuthenticationleTasks exit");
        return $ret;
    }

    /**
     * Returns the modules array.
     *
     * @return array modules array (indexed by module name)
     */
    public static function getModules()
    {
        Logger::getLogger("AuieoATS")->info("ModuleUtility:getModules entry");
        /* Should already be in the session, if not rescan modules dir and add to
         * current session.
         */
        if (!isset($_SESSION['modules']) || empty($_SESSION['modules']))
        {
            $modules = self::_refreshModuleList();
            $_SESSION['modules'] = $modules;
        }

        /* This shouldn't happen... sanity check. */
        if (empty($_SESSION['modules']))
        {
            self::_fatal('No modules found.');
        }

        Logger::getLogger("AuieoATS")->info("ModuleUtility:getModules exit");
        return $_SESSION['modules'];
    }

    /**
     * Checks to see if the specified module exists.
     *
     * @param string module name
     * @return boolean module exists
     */
    public static function moduleExists($moduleName)
    {
        Logger::getLogger("AuieoATS")->info("ModuleUtility:moduleExists entry");
        $modules = self::getModules();

        foreach ($modules as $name => $data)
        {
            if ($name == $moduleName)
            {
                return true;
            }
        }
        Logger::getLogger("AuieoATS")->info("ModuleUtility:moduleExists exit");
        return false;
    }

    /*
     * Rescans module directory
     *
     * @return array modules array (indexed by module name)
     */
    private static function _refreshModuleList()
    {
        Logger::getLogger("AuieoATS")->info("ModuleUtility:_refreshModuleList entry");
        /* Modules array looks like this:
         *
         * $modules = array(
         *     'login'    => array('LoginUI',    ''),
         *     'home'     => array('HomeUI',     'Home'),
         *     ...
         *     'calendar' => array('CalendarUI', 'Calendar'),
         *     'settings' => array('SettingsUI', 'Settings'),
         *     'tests'    => array('TestsUI',    '')
         * );
         */

         /* Attempt to load the list of modules from a temporary file. */
        if (file_exists('modules.cache') && !isset($_POST['performMaintenence']) && CACHE_MODULES)
        {
            $modulesCache = unserialize(file_get_contents('modules.cache'));

            $_SESSION['hooks'] = $modulesCache->hooks;

            return $modulesCache->modules;
        }

        $modules = array();
        $moduleDirectories = array();
        $hooks = array();

        $directory = @opendir(MODULES_PATH) or self::_fatal(
            sprintf("Unable to open '%s'.", MODULES_PATH)
        );

        /* Loop through files / directories inside MODULES_PATH. */
        while ($filename = readdir($directory))
        {
            $fullModulePath = MODULES_PATH . $filename;

            /* Ignore files / directories that begin with '.', and any
             * non-directories.
             */
            if ($filename[0] !== '.' && is_dir($fullModulePath))
            {
                $moduleDirectories[] = $fullModulePath;
            }
        }

        closedir($directory);

        /* Get a blocking advisory lock on the database. */
        $db = DatabaseConnection::getInstance();
        $db->getAdvisoryLock('CATSUpdateLock', 120);

        /* FIXME: There has to be a better way to locate the UI filename. */
        foreach ($moduleDirectories as $directoryName)
        {
            $directory = @opendir($directoryName) or self::_fatal(
                sprintf("Unable to open '%s'.", $directoryName)
            );

            while ($filename = readdir($directory))
            {
                $fullFilePath = $directoryName . '/' . $filename;

                /* Search for UI file. */
                if (substr($filename, -6) !== 'UI.php')
                {
                    continue;
                }

                include_once($fullFilePath);

                $moduleName = basename($directoryName);
                $moduleClass = basename(substr($fullFilePath, 0, -4));

                $module = new $moduleClass();
                $modules[$moduleName] = array(
                    $moduleClass,
                    $module->getModuleTabText(),
                    $module->getSubTabsExternal(),
                    $module->getSettingsEntries(),
                    $module->getSettingsUserCategories()
                );

                $moduleHooks = $module->getHooks();
                foreach ($moduleHooks as $name => $data)
                {
                    $hooks[$name][] = $data;
                }

                self::processModuleSchema($moduleName, $module->getSchema());
            }

            closedir($directory);
        }

        $db->releaseAdvisoryLock('CATSUpdateLock');

        /* Is called by installer? */
        if (isset($_POST['performMaintenence']))
        {
            die();
        }

        $_SESSION['hooks'] = $hooks;

        /* Sort the modules. */
        uksort($modules , array('self', '_sortModules'));

        /* Verify that core modules are present. */
        self::_checkCoreModules($modules);

        /* Try to store the modules for future use. */
        if (CACHE_MODULES)
        {
            $modulesCache->modules = $modules;
            $modulesCache->hooks = $hooks;
            @file_put_contents('modules.cache', serialize($modulesCache));
        }
        Logger::getLogger("AuieoATS")->info("ModuleUtility:_refreshModuleList exit");
        return $modules;
    }

    /**
     * Verifies that core modules are installed and fatal()s out if not.
     *
     * @param array detected modules
     * @return void
     */
    private static function _checkCoreModules($modules)
    {
        Logger::getLogger("AuieoATS")->info("ModuleUtility:_checkCoreModules entry");
        $missing = array();

        foreach ($GLOBALS['coreModules'] as $key => $value)
        {
            if (!isset($modules[$key]))
            {
                $missing[] = $key;
            }
        }

        if (count($missing) > 0)
        {
            $error = 'One or more of CandidATS\' core modules is missing.<br />';

            foreach ($missing as $module)
            {
                $error .= 'Module "' . $module . '" not found.<br />';
            }

            self::_fatal($error);
        }
        Logger::getLogger("AuieoATS")->info("ModuleUtility:_checkCoreModules exit");
    }

    /**
     * Print a fatal error and die.
     *
     * @param string error message
     * @return void
     */
    private static function _fatal($error)
    {
        Logger::getLogger("AuieoATS")->info("ModuleUtility:_fatal entry");
        $template = new Template();

        $template->assign('errorMessage', $error);
        $template->display('./Error.tpl');

        echo '<!--';
         trigger_error(
             str_replace("\n", " ", 'Fatal Error raised: ' . $error)
         );
        echo '-->';
        Logger::getLogger("AuieoATS")->info("ModuleUtility:_fatal exit");
        die();
    }

    /**
     * Sorts modules based on the order specified in constants.php.
     *
     * If both modules are part of the core module list, do a comparison
     * based on order defined in constants file. If only one of them is
     * a custom module and it is A then always return -1, pushing it
     * down the list. If A is a core module then always push it up the
     * list. This way, core modules get displayed first followed by custom
     * modules.
     *
     * @param module A name
     * @param module B name
     * @return sort order for uksort
     */
    private static function _sortModules($a, $b)
    {
        Logger::getLogger("AuieoATS")->info("ModuleUtility:_sortModules entry");
        if (!eval(Hooks::get('SORT_MODULES_RETURN_POS'))) return 1;
        if (!eval(Hooks::get('SORT_MODULES_RETURN_NEG'))) return -1;

        if (isset($GLOBALS['coreModules'][$a]))
        {
            if (isset($GLOBALS['coreModules'][$b]))
            {
                if ($GLOBALS['coreModules'][$a] > $GLOBALS['coreModules'][$b])
                {
                    return 1;
                }

                if ($GLOBALS['coreModules'][$a] == $GLOBALS['coreModules'][$b])
                {
                    return 0;
                }

                return -1;
            }

            return -1;
        }
        Logger::getLogger("AuieoATS")->info("ModuleUtility:_sortModules exit");
        return 1;
    }

    /**
     * Returns the schema version numbers (in a result set format) of all
     * installed modules.
     *
     * @return array Multi-dimensional associative result set array of
     *               schema versions data.
     */
    public static function getModuleSchemaVersions()
    {
        Logger::getLogger("AuieoATS")->info("ModuleUtility:getModuleSchemaVersions entry");
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "SELECT
                name AS name,
                version AS version
            FROM
                module_schema
            ORDER BY
                name ASC"
        );
        Logger::getLogger("AuieoATS")->info("ModuleUtility:getModuleSchemaVersions exit");
        return $db->getAllAssoc($sql);
    }

    /**
     * Checks the module's database schema version and makes sure that the
     * schema includes all updates from the module.  If not, it updates the
     * schema as the module has indicated.
     *
     * @param string Module name for which to process schema changes.
     * @param array Module schema updates array.
     * @return void
     */
    private static function processModuleSchema($moduleName, $schema)
    {
        Logger::getLogger("AuieoATS")->info("ModuleUtility:processModuleSchema entry");
        set_time_limit(0);

		$executedQuery = false;

        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "SELECT
                version AS version
            FROM
                module_schema
            WHERE
                name = %s",
            $db->makeQueryString($moduleName)
        );
        $rs = $db->getAssoc($sql);

        if (!empty($rs))
        {
            $currentVersion = $rs['version'];
        }
        else
        {
            $sql = sprintf(
                "INSERT INTO module_schema (
                    name,
                    version
                )
                VALUES (
                    %s,
                    0
                )",
                $db->makeQueryString($moduleName)
            );
            $db->query($sql);

            $currentVersion = 0;
        }

        /* Get the latest schema revision. */
        $schemaVersions = array_keys($schema);
        if (!empty($schemaVersions))
        {
            $newestVersion = max($schemaVersions);
        }
        else
        {
            $newestVersion = 0;
        }

        /* Do we have any updates to process? */
        if ($newestVersion <= $currentVersion)
        {
            return;
        }

        ksort($schema, SORT_NUMERIC);
        foreach ($schema as $version => $sql)
        {
            if ($version <= $currentVersion)
            {
                continue;
            }

			/* if maintPage, execute 1 query, output the next query and progress, and terminate. */
			global $maintPage;
			if ((isset($maintPage) && $maintPage === true))
			{
				if ($executedQuery == false)
				{
					$executedQuery = true;
				}
				else
				{
					$keys = array_keys($schema);
					rsort($keys, SORT_NUMERIC);
					$maxVersion = $keys[0];
					echo '<script>';
					echo 'setProgressUpdating(decode64("'.base64_encode($sql).'"), '.$version.', '.$maxVersion.', "'.$moduleName.'");';
					echo 'setTimeout("Installpage_maint();", 50);';
					echo '</script>';
					die();
				}
			}

            if (strpos($sql, 'PHP:') === 0)
            {
                /* Strip off the 'PHP:' and execute the code. */
                $PHPCode = substr($sql, 4);
                eval($PHPCode);
            }
            else
            {
                $SQLStatments = explode(';', $sql);

                foreach ($SQLStatments as $SQL)
                {
                    $SQL = trim($SQL);

                   	if (!empty($SQL))
                    {
                        $db->query($SQL);
                    }
                }
            }

            $sql = sprintf(
                "UPDATE
                    module_schema
                SET
                    version = %s
                WHERE
                    name = %s",
                $version,
                $db->makeQueryString($moduleName)
            );
            $rs = $db->query($sql);

            $currentVersion = $version;
        }
        Logger::getLogger("AuieoATS")->info("ModuleUtility:processModuleSchema exit");
    }
}

?>
