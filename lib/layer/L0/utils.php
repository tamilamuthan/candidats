<?php
function connectPDO($host,$user,$pass,$dbname=null,$sqlserver="mysql") 
{
    $pdo=null;
    try 
    {
        if(strtolower($sqlserver)=="mysql")
        {
            $dsn="mysql:host=$host";
            if($dbname)
            {
                $dsn="mysql:host=$host;dbname=$dbname";
            }
            $pdo = new PDO($dsn, $user, $pass);
        }
        else if(strtolower($sqlserver)=="sqlite")
        {
            $dsn="sqlite:{$dbname}";
            $pdo = new PDO($dsn);
        }
        else
        {
            die ("Unknown DSN. Please set config.php");
        }
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->fetchmode = PDO::FETCH_ASSOC;
    }   
    catch(PDOException $e)
    {
        die($e); 
    }
    return $pdo;
}
function getConfigVar($variable)
{
    static $arrConfigVar=array();
    if(!isset($arrConfigVar[$variable]))
    {
        if(!file_exists("ClsConfig.php")) return null;
        if(!class_exists("ClsConfig")) return null;
        if(!isset(ClsConfig::$$variable)) return null;
        $arrConfigVar[$variable]=ClsConfig::$$variable;
    }
    return $arrConfigVar[$variable];
}
function getModulePath($module)
{
    static $arrModulePath=array();

    if(!isFrameworkInstalled())
    {
        return AUIEO_FRAMEWORK_PATH."modules/";
    }
    if(!isApplicationInstalled())
    {
        if(file_exists("modules/{$module}"))
            return "modules/";
        else return AUIEO_FRAMEWORK_PATH."modules/";
    }
    if(isset($arrModulePath[$module])) return $arrModulePath[$module];
    $modulePath=getConfigVar("MODULE_PATH");
    if(!is_null($modulePath))
    {
        if(file_exists("{$modulePath}{$module}"))
        {
            $arrModulePath[$module]="{$modulePath}";
            return $arrModulePath[$module];
        }
    }
    if(file_exists("modules/{$module}"))
    {
        $arrModulePath[$module]="modules/";
        return $arrModulePath[$module];
    }
    if(file_exists(AUIEO_FRAMEWORK_PATH."modules/{$module}"))
    {
        $arrModulePath[$module]=AUIEO_FRAMEWORK_PATH."modules/";
        return $arrModulePath[$module];
    }
    return null;
}
function getModuleConfigVars($module)
{
    static $arrConfigVar=array();
    if(isset($arrConfigVar[$module])) return $arrConfigVar[$module];
    $modulePath=getModulePath($module);
    if($modulePath)
    {
        $__naanal_tmp_module=$module;
        unset($module);
        if(file_exists($modulePath."{$__naanal_tmp_module}/config.php"))
        {
            include $modulePath."{$__naanal_tmp_module}/config.php";
        }
        else if(file_exists(AUIEO_APP_PATH."module_config/{$__naanal_tmp_module}.php"))
        {
            include AUIEO_APP_PATH."module_config/{$__naanal_tmp_module}.php";
        }
        else
        {
            $arrConfigVar[$__naanal_tmp_module]=array();
            return $arrConfigVar[$__naanal_tmp_module];
        }
        unset($modulePath);
        $arrConfigVar[$__naanal_tmp_module]=get_defined_vars();
        $tmp=$arrConfigVar[$__naanal_tmp_module];
        unset($arrConfigVar[$__naanal_tmp_module]["__naanal_tmp_module"]);
        return $tmp;
    }
    $tmp=array();
    return $tmp;
}
function isFrameworkInstalled()
{
    if(!file_exists("ClsConfig.php")) return false;
    require_once("ClsConfig.php");
    if(!class_exists("ClsConfig")) return false;
    $arrConfigVar=array("CONTROL_PANEL","LOG","LANG","isDeveloperMode","DATABASE","DISABLE_TRACKING","theme");
    foreach($arrConfigVar as $var)
    {
        if(!isset(ClsConfig::$$var)) return false;
    }
    return true;
}
function isApplicationInstalled()
{
    if(!file_exists("config.php")) 
    {
        return false;
    }
    if(!file_exists("ClsConfig.php")) return false;
    require_once("ClsConfig.php");
    if(!class_exists("ClsConfig")) return false;
    foreach(ClsConfig::$CONFIG_VAR as $variable=>$arrInfo)
    {
        if(ClsConfig::$DATABASE_CONFIG_TYPE=="variable")
        {
            if(ClsNaanalConfigGen::getVarIndex($variable)==-1)
            {
                return false;
            }
        }
        else
        {
            if(ClsNaanalConfigGen::getDefineKeyIndex($variable)==-1)
            {
                return false;
            }
        }
    }
    /**
     * The application var must have some value other than default value
     */
    if(ClsConfig::$CONFIG_MANDATORY_VAR)
    foreach(ClsConfig::$CONFIG_MANDATORY_VAR as $applicationVar)
    {
        if(ClsConfig::$DATABASE_CONFIG_TYPE=="variable")
        {
            $dataIndex=ClsNaanalConfigGen::getVarIndex($applicationVar);
        }
        else
        {
            $dataIndex=ClsNaanalConfigGen::getDefineValueIndex($applicationVar);
        }
        if(is_null($dataIndex) || $dataIndex==-1) 
        {
            return false;
        }
        $data=ClsNaanalConfigGen::extractValue($dataIndex);
        if(ClsConfig::$CONFIG_VAR[$applicationVar]["data"]===$data)
        {
            return false;
        }
        $configdata=ClsConfig::$CONFIG_VAR[$applicationVar]["data"];
    }
    $type=getAppConfig("DATABASE_CONFIG_TYPE");
    if($type=="constant")
    {
        include_once("config.php");
        $arrVar=get_defined_constants();
    }
    else
    {
        $arrVar=getFileVars("config.php");
    }
    /*$arrConfigVar=getAppConfig("CONFIG_VAR");
    if($arrConfigVar)
    foreach($arrConfigVar as $var)
    {
        $tmpvar=ClsConfig::$CONFIG_VAR_MAP[$var];
        if(!isset($arrVar[$tmpvar]))
        {
            return false;
        }
    }*/
    return true;
}
function &getFileVars($__AUIEO_TEMP_FILE)
{
    include($__AUIEO_TEMP_FILE);
    unset($__AUIEO_TEMP_FILE);
    $arrVar=get_defined_vars();
    return $arrVar;
}
/**
 * gets application's config value.
 * @param type $name
 * @return if config exist, return data. else returns null
 */
function getAppConfig($name)
{
    include_once("config.php");
    if(defined($name)) return constant($name);
    return null;
}
function &_AuieoHook($hook)
{
    static $arrHook=array();
    if(empty($arrHook))
    {
        if(file_exists(AUIEO_APP_PATH."hooks.php"))
        {
            $arrHook=exractFileVars(AUIEO_APP_PATH."hooks.php");
        }
        else if(AUIEO_APP_PATH."auieo_hooks.php")
        {
            $arrHook=exractFileVars(AUIEO_APP_PATH."auieo_hooks.php");
        }
    }
    if(array_key_exists($hook, $arrHook))
    {
        extract($arrHook);
        return $$hook;
    }
    else
    {
        $tmp=null;
        return $tmp;
    }
}
/**
 * if input is file name, variable declared in the file returns,
 * if input is array of file name, all the variable in the fils merged and returns
 * the next file variable will override previous file variable
 * @param type string or array
 * @return type array
 */
function exractFileVars($_auieoFilePath)
{
    if(!is_array($_auieoFilePath))
    {
        $_auieoFilePath=array($_auieoFilePath);
    }
    foreach($_auieoFilePath as $_auieoFilePathTmp)
    {
        if(file_exists($_auieoFilePathTmp))
        {
            include($_auieoFilePathTmp);
        }
    }
    unset($_auieoFilePathTmp);
    unset($_auieoFilePath);
    return get_defined_vars();
}
function pageTitle($title=false)
{
    static $auieoPageTitle=null;
    if($title===false)
    {
        return $auieoPageTitle;
    }
    $auieoPageTitle=$title;
}
function getCurrentModuleController()
{
    $module=isset($_REQUEST["m"])?$_REQUEST["m"]:"home";
    return ClsNaanalApplication::loadController($module);
}
function getModuleWrapperName($module)
{
    static $arrModuleWrapperName=array();
    //if(class_exists("ClsConfig") && isset(ClsConfig::$DATABASE_WRAPPER_CLASS) && ClsConfig::$DATABASE_WRAPPER_CLASS===false) return false;
    if(!isset($arrModuleWrapperName[$module]))
    {
        $module_wrapper=_AuieoHook("module_wrapper_name");
        if($module_wrapper)
        {
            $arrModuleWrapperName[$module]=$module_wrapper($module);
        }
        else
        {
            $arrModuleWrapperName[$module]="ClsW".ucfirst($module);
        }
    }
    return $arrModuleWrapperName[$module];
}
function pageHeaderInclude($js=false)
{
    static $arrJS=array();
    if($js===false)
    {
        return $arrJS;
    }
    if(!in_array($js, $arrJS))
    {
        $arrJS[]=$js;
    }
}
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else
        $ip = $_SERVER['REMOTE_ADDR'];
    return $ip;
}
function errMsg($error=false)
{
    static $_error=false;
    if($error===false) return $_error;
    $_error=$error;
}
function loadDoubleLayerTemplate($php_path,$html_path,$arrTplVar=array())
{
    if(!empty($arrTplVar))
    {
        extract($arrTplVar);
    }
        include($php_path);
        /**
        * for handing comment in html template. usage is {$_("This is comment")}
        */
       $_=function($comment)
       {
           return "";
       };
        ob_start();
        eval('echo <<< EOT
        '.file_get_contents($html_path).'
EOT;
');
        $html = ob_get_clean();
        return $html;
}
function multi_dimension_array($singleDimensionArray, $col_count=2,$columnPrefix=false){
	$result = false;
        $array=array_values($singleDimensionArray);
        $arrayKeys=array_keys($singleDimensionArray);
	if(!empty($array) && is_array($array)){
		$row_count = ceil( count($array) / $col_count);
		$pointer = 0;
		for($row=0; $row < $row_count; $row++) {
			for($col=0; $col < $col_count; ++$col){
				//if($pointer<count($singleDimensionArray)) {
                                    if($columnPrefix)
                                    {
                                        $result[$row][$columnPrefix.$col] = isset($array[$pointer])?$array[$pointer]:"";
                                    }
                                    else
                                    {
					$result[$row][$arrayKeys[$pointer]] = isset($array[$pointer])?$array[$pointer]:"";
                                    }
				//}
                                /*else
                                {
                                    trace($pointer);
                                }*/
                                $pointer++;
			}
		}
	}
	return $result;
}
?>