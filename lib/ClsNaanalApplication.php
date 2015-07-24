<?php
define("APP_PATH",  realpath("."));
define("NAANAL_MODULES_PATH",  APP_PATH."/modules/");
define("NAANAL_PAGES_PATH",  APP_PATH."/pages/");
define("NAANAL_APP_PATH",  realpath(".")."/");
define("WEB_PATH", "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']);
define("NAANAL_WEB_PATH", "");
$naanalPath=dirname(dirname(__FILE__))."/";
//define("NAANAL_PATH",$naanalPath);
$libpath=AUIEO_FRAMEWORK_PATH."lib/";
define("NAANAL_PATH_LIB",$libpath);
$jslibspath=dirname(__FILE__)."/js/";
$csslibpath=dirname(__FILE__)."/css/";

include_once(NAANAL_PATH_LIB."ClsNaanalPage.php");
include_once(NAANAL_PATH_LIB."ClsNaanalValidate.php");
include_once NAANAL_PATH_LIB."ClsNaanalModule.php";
include_once NAANAL_PATH_LIB."ClsAuieoModule.php"; 
include_once NAANAL_PATH_LIB."Config.php"; 
define("IS_NAANAL_ADMIN",false);

class ClsNaanalApplication
{
    private static $arrConfigVar=array();
    public static $isClsConfigExist=true;
    public static $isClsConfigFileExist=true;
    public static $isErrINIExist=true;
    public static $isLangVarExist=true;
    public static $arrDirectoryNotExist=array();
    public static $isLangDirectoryExist=true;
    public static $isLangDefaultDirectoryExist=true;
    public static $isLangDefaultCommoneFileExist=true;
    public static $isLangWorkplaceDirectoryExist=true;
    public static $isLangWorkplaceDefaultDirectoryExist=true;
    public static $isLangWorkplaceDefaultCommoneFileExist=true;
    public static $isWorkPlaceDirectoryExist=true;
    public static $isClientDirectoryExist=true;
    public static $isClientDefaultDirectoryExist=true;
    public static $isModulesDirectoryExist=true;
    public static $isThemesDirectoryExist=true;
    public static $isLogDirectoryExist=true;
    public static $isClsPageExist=true;
    
    public static $isDatabaseExist=true;
    public static $isDatabaseValid=true;
    
    public static $isLogExist=true;
    public static $isDisableTrackingExist=true;
    public static $isDeveloperIPExist=true;
    public static $isDeveloperModeExist=true;
    public static $isThemeExist=true;
    public static $isHomeExist=true;
    public static $isHookExist=true;
    
    public static $isControlPanelExist=true;
    public static $controlPanelItemNotExist=array();
    
    public static $isApplicationHasError=false;
    public static $isDatabaseConnectivityError=false;
    
    private static $arrError=array();
    private static $arrLog=array();
    
    private static $databaseStable=false;
    
    private static $isWebservice=false;
    
    public function __construct()
    {
        
    }
    public static function getDefaultDatabase()
    {
        return getAppConfig("DATABASE_NAME");
    }
 
    public static function &getConfigVars($module)
    {
        if(isset(self::$arrConfigVar[$module])) return self::$arrConfigVar[$module];
        $modulePath=ClsNaanalApplication::getModulePath($module);
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
                self::$arrConfigVar[$__naanal_tmp_module]=array();
                return self::$arrConfigVar[$__naanal_tmp_module];
            }
            unset($modulePath);
            self::$arrConfigVar[$__naanal_tmp_module]=get_defined_vars();
            $tmp=self::$arrConfigVar[$__naanal_tmp_module];
            unset(self::$arrConfigVar[$__naanal_tmp_module]["__naanal_tmp_module"]);
            return $tmp;
        }
        $tmp=array();
        return $tmp;
    }
    public static function loadWrapper($module)
    {
        static $arrNaanalWrapperLoaded=array();
        if(!isset($arrNaanalWrapperLoaded[$module]))
        {
            $naanal_wrapper=self::getWrapperName($module);
            $libpath=NAANAL_PATH_LIB;
            $naanalPath=AUIEO_FRAMEWORK_PATH;
            $path=ClsNaanalApplication::getAbsModulePath($module);
            if($path)
            {
                if(file_exists($path.$naanal_wrapper.".php"))
                {
                    $arrNaanalWrapperLoaded[$module] = $path.$naanal_wrapper.".php";
                }
                else if(file_exists("wrapper/".$naanal_wrapper.".php"))
                {
                    $arrNaanalWrapperLoaded[$module] = "wrapper/".$naanal_wrapper.".php";
                }
                else if(file_exists($naanal_wrapper.".php"))
                {
                    $arrNaanalWrapperLoaded[$module] = $naanal_wrapper.".php";
                }
                else
                {
                    $arrWrapperLoaded[$module]=false;
                }
            }
            else
            {
                $arrNaanalWrapperLoaded[$module]=false;
            }
            if(isset($arrNaanalWrapperLoaded[$module]))
            {
                include_once $arrNaanalWrapperLoaded[$module];
            }
        }
    }
    public static function getModuleNameByController($controller)
    {
        static $arrNaanalModule=array();
        if(!isset($arrNaanalModule[$controller]))
        {
            $naanal_controller_module=_AuieoHook("controller_module_name");
            if($naanal_controller_module)
            {
                $arrNaanalModule[$controller]=$naanal_controller_module($controller);
            }
            else
            {
                $umodule=substr($controller, 3);
                $arrNaanalModule[$controller]=strtolower($umodule);
            }
        }
        return $arrNaanalModule[$controller];
    }
    public static function getModuleNameByWrapper($wrapper)
    {
        static $arrNaanalModule=array();
        if(!isset($arrNaanalModule[$wrapper]))
        {
            $naanal_wrapper_module=_AuieoHook("wrapper_module_name");
            if($naanal_wrapper_module)
            {
                $arrNaanalModule[$wrapper]=$naanal_wrapper_module($wrapper);
            }
            else
            {
                $umodule=substr($wrapper, 3);
                $arrNaanalModule[$wrapper]=strtolower($umodule);
            }
        }
        return $arrNaanalModule[$wrapper];
    }
    public static function getControllerName($module)
    {
        static $arrNaanalController=array();
        if(!isset($arrNaanalController[$module]))
        {
            $naanal_module_controller=_AuieoHook("module_controller_name");
            if($naanal_module_controller)
            {
                $arrNaanalController[$module]=$naanal_module_controller($module);
            }
            else
            {
                $arrNaanalController[$module]="Cls".ucfirst($module);
            }
        }
        return $arrNaanalController[$module];
    }
    public static function getWrapperName($module)
    {
        return getModuleWrapperName($module);
    }
    public static function loadController($module)
    {
        static $arrControllerLoaded=array();
        if(!isset($arrControllerLoaded[$module]))
        {
            $controller=self::getControllerName($module);
            $libpath=NAANAL_PATH_LIB;
            $naanalPath=AUIEO_FRAMEWORK_PATH;
            $path=ClsNaanalApplication::getAbsModulePath($module);
            if($path)
            {
                if(file_exists($path.$controller.".php"))
                {
                    $arrControllerLoaded[$module] = $path.$controller.".php";
                }
                else if(file_exists("controller/".$controller.".php"))
                {
                    $arrControllerLoaded[$module] = "wrapper/".$controller.".php";
                }
                else if(file_exists($controller.".php"))
                {
                    $arrControllerLoaded[$module] = $controller.".php";
                }
                else
                {
                    $arrControllerLoaded[$module]=false;
                }
            }
            else
            {
                $arrControllerLoaded[$module]=false;
            }
            if($arrControllerLoaded[$module])
            {
                include_once $arrControllerLoaded[$module];
            }
        }
        if(isset($arrControllerLoaded[$module]))
        {
            return new $controller();
        }
    }
    public static function loadClass($class)
    {
        static $arrClassPath=array();
        if(!isset($arrClassPath[$class]))
        {
            $libpath=NAANAL_PATH_LIB;
            $naanalPath=AUIEO_FRAMEWORK_PATH;
            $module=false;
            $classPrefix=substr($class,0,3);
            if($classPrefix=="Cls" && $class[3]=="W" && ctype_upper($class[4]))
            {
                $module=substr($class,4);
                $module=strtolower($module);
            }
            else if($classPrefix=="Cls" && $class[3]=="U" && ctype_upper($class[4]))
            {
                $module=substr($class,4);
                $module=strtolower($module);
            }
            else if($classPrefix=="Cls")
            {
                $module=substr($class,3);
                $module=strtolower($module);
            }
            else
            {
                
            }
            if($module)
            {
                $path=ClsNaanalApplication::getAbsModulePath($module);
                if($path)
                {
                    if(file_exists($path.$class.".php"))
                    {
                        $arrClassPath[$class] = $path.$class.".php";
                    }
                    else if(file_exists("wrapper/".$class.".php"))
                    {
                        $arrClassPath[$class] = "wrapper/".$class.".php";
                    }
                    else if(file_exists($class.".php"))
                    {
                        $arrClassPath[$class] = $class.".php";
                    }
                }
                else
                {
                }
            }
        }
        if(empty($arrClassPath[$class]) || !file_exists($arrClassPath[$class])) 
        {
            //self::$arrError[]=array("error"=>"class ({$class}) does not exist");
            return false;
        }
        include_once($arrClassPath[$class]);
        return true;
    }
    
    public static function getError()
    {
        return $this->arrError;
    }
    
    public static function getNaanalModule()
    {
        
    }
    
    public static function loadNaanalModule()
    {
        
    }
    
    public static function loadNaanalApp()
    {
        $success=ClsNaanalApplication::validate();
        if(!file_exists("ClsConfig.php"))
        {
            //$arrParam=ClsNaanalRequest::getInstance()->getAll();
            //unset($arrParam[ClsNaanalRequest::getInstance()->getUrlModuleParam()]);
            //unset($arrParam[ClsNaanalRequest::getInstance()->getUrlActionParam()]);
            //$installContent=get_module_content("install","create",0,$arrParam);
            //echo($installContent);
            //exit;
        }
        else
        {
            include_once("ClsConfig.php");
            if(!class_exists("ClsConfig"))
            {
                print_r("class ClsConfig not defined in ClsConfig.php.</br>");
                exit;
            }
            $arrStaticVar=array("CONTROL_PANEL",  "isDeveloperMode");
            $arrError=array();
            foreach($arrStaticVar as $staticVar)
            {
                if(!isset(ClsConfig::$$staticVar))
                {
                    $arrError[]="static variable <b>{$staticVar}</b> not defined in ClsConfig class.</br>";
                }
            }
        }
        if(!empty($arrError))
        {
            $strError=implode("<br />",$arrError);
            echo $strError;exit;
        }
        else
        {
            if(ClsConfig::$isDeveloperMode)
            {
                include_once(__DIR__."/testing.php");
                ClsAuieoTestGen::render(AUIEO_WEB_PATH);
            }
           $cpanel="guest";
           $page=new ClsNaanalPage(AUIEO_CONTROL_PANEL,true,null,URL_BASE_FILE_NAME);
           if($err=$page->getError())
           {
               trace($err);
           }
           /**
           *Caching the rendered data 
           */
           ob_start();
           $page->render();
           /**
           *store the rendered data in variable 
           */
           echo ob_get_clean();//trace("====");
        }
    }
    
    /**
    * temporary simple template used only in page.php. In future, this function has to be removed
    * @param type string
    * @param type array
    * @return string 
    */
    public static function processTpl($templateContent,$arrParam)
    {
            $template=html_entity_decode($templateContent);
            $template=stripslashes($template);
            $matches=array();
            preg_match_all("/{[^{]*}/", $template, $matches);
            $arrTplVar=array();
            foreach($matches[0] as $ind=>$tplvar)
            {
                    $arrTplVar[trim($tplvar,'{}')]=$tplvar;
            }
            foreach($arrTplVar as $var=>$tplvar)
            {
                    $template=str_replace($tplvar, $arrParam[$var], $template);				
            }
            return $template;
    }
    /**
    * wrap the generated report with report template
    * @param type $template_id
    * @param type $content
    * @param type $reportname
    * @return type 
    */
    public static function wrapTemplate($template_id,$content,$reportname)
    {
            $sql="select * from pagetemplate where id=".$template_id;
            $clientpdo=ClsNaanalPDO::getNamedInstance();
            $clientpdo->setQuery($sql);
            $arr=$clientpdo->getAllAssoc();
            $template=$arr[0]["template"];

            $arrParam=null;

            $arrClient=getCurrentClient();
            $companyname=$arrClient["companyname"];
            $logo=$arrClient["logo"];
            $path="admin/".$logo;
            if(!file_exists($path))
            {
            $path="upload/noimage.jpeg";
    }	
    else if(is_dir($path))
    {
        $path="upload/noimage.jpeg";
    }
            $arrParam["LOGO"]="<img src='".$path."' width='100' height='100' />";
            $arrParam["COMPANY_NAME"]=$companyname;
            $arrParam["BODY"]=$content;
            $arrParam["REPORT_NAME"]=$reportname;

            $content=processTpl($template, $arrParam);
            return $content;
    } 
    /**
    * returns current user object
    * @return object
    */
    public static function getCurrentUser()
    {
        return getUserObject();
    }
    /**
    * returns current client info as array
    * @return type array
    */
    public static function getCurrentClient()
    {
            $objUser=getCurrentUser();
            $pdo=ClsNaanalPDO::getNamedInstance();
            $pdo->setQuery("select * from client where id=".$objUser->get_client_id());
            $arr=$pdo->getAllAssoc();
            return $arr[0];	
    }
    /**
    * returns current database ID
    * @return int 
    */
    public static function getCurrentDatabaseID()
    {
        $objNaanalSession=  ClsNaanalSession::getInstance();
        return $objNaanalSession->isPanelDataSet("dynamicdatabase")?$objNaanalSession->getPanelData("dynamicdatabase"):0;
    }
    /**
    * returns current database
    * @return string 
    */
    public static function getCurrentDatabase()
    {
        $dbid=self::getCurrentDatabaseID();
        if($dbid>0)
        {
            $sql="select * from `database` where `id`=".$dbid;
            $pdo=ClsNaanalPDO::getNamedInstance();
            $pdo->setQuery($sql);
            $arrAssoc=$pdo->getAllAssoc();
            $database=$arrAssoc[0]["databasename"];
            $pdo->statement=null;
            return $database;
        }
        else 
        {
            return ClsNaanalApplication::getDefaultDatabase();
        }
    }
    public static function isFrameworkModule($module)
    {
        $path=self::getModulePath($module);
        if(strpos($path, AUIEO_FRAMEWORK_PATH)===0)
        {
            return true;
        }
        return false;
    }
    public static function __getWorkplacePath()
    {
        $path=getConfigVar("WORKPLACE_PATH");
        $path=!is_null($path)?$path:"workplace/";
        return $path;
    }
    public static function __renderActionPath()
    {
        
    }
    public static function __renderThemePath($theme)
    {
        static $arrThemePath=array();
        if(isset($arrThemePath[$theme])) return $arrThemePath[$theme];
        $themePath=getConfigVar("THEME_PATH");
        if(!is_null($themePath))
        {
            if(file_exists("{$themePath}{$theme}"))
            {
                $arrThemePath[$theme]="{$themePath}";
                return $arrThemePath[$theme];
            }
        }
        if(file_exists("themes/{$theme}"))
        {
            $arrThemePath[$theme]="themes/";
            return $arrThemePath[$theme];
        }
        if(file_exists(AUIEO_FRAMEWORK_PATH."themes/{$theme}"))
        {
            $arrThemePath[$theme]=AUIEO_FRAMEWORK_PATH."themes/";
            return $arrThemePath[$theme];
        }
        return null;
    }
    public static function isFrameworkInstalled()
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
    public static function __renderModulePath($module)
    {
        static $arrModulePath=array();
        
        if(!ClsNaanalApplication::isFrameworkInstalled())
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
    public static function __getModulePath()
    {
        $modulePath=getConfigVar("MODULE_PATH");
        $modulePath=!is_null($modulePath)?$modulePath:"modules/";
        return $modulePath;
    }
    public static function __getTemplatePath()
    {
        $templatePath=getConfigVar("TEMPLATE_PATH");
        $templatePath=!is_null($templatePath)?$templatePath:"templates/";
        return $templatePath;
    }
    public static function getAbsModulePath($module,$action="create")
    {
        $modulePath=self::__renderModulePath($module);
        $controller=self::getControllerName($module);
        $wrapper=self::getWrapperName($module);
        if($module=="install")
        {
            if(!ClsNaanalApplication::isFrameworkInstalled())
            {
                return "{$modulePath}install/";
            }
            if(!isApplicationInstalled())
            {
                return "{$modulePath}install/";
            }
        }
        $workplacePath=ClsNaanalApplication::__getWorkplacePath();
        if(file_exists("{$modulePath}{$module}"))
        {
            $modulePath="{$modulePath}{$module}/";
        }
        else if(file_exists(AUIEO_FRAMEWORK_PATH."{$modulePath}{$module}"))
        { 
            $modulePath=AUIEO_FRAMEWORK_PATH."{$modulePath}{$module}/";
        }
        else if(file_exists("{$workplacePath}{$module}.php") || file_exists("{$workplacePath}{$module}_{$action}.php") || file_exists("{$workplacePath}{$controller}.php") || file_exists("{$workplacePath}{$wrapper}.php"))
        {
            $modulePath=$workplacePath;
        }
        else
        {
            $modulePath=false;
        }
        return $modulePath;
    }
    public static function getThemePath($theme=false)
    {
        if($theme===false)
        {
            return "themes/";
        }
        else
        {
            return self::__renderThemePath($theme);
        }
    }
    public static function getModulePath($module)
    {
        return self::__renderModulePath($module);
        /*$modulePath=self::__getModulePath();
        if($module=="install")
        {
            if(!ClsNaanalApplication::isFrameworkInstalled())
            {
                return NAANAL_PATH.$modulePath;
            }
            if(!isApplicationInstalled())
            {
                return $modulePath;
            }
        }
        if(file_exists("{$modulePath}{$module}"))
        {
            return $modulePath;
        }
        else if(file_exists(NAANAL_PATH."modules/{$module}"))
        {
            return NAANAL_PATH."modules/";
        }
        return false;*/
    }
    public static function getBusinessModulePath($module)
    {
        if(file_exists("businessmodules/{$module}"))
        {
            return "businessmodules/{$module}/";
        }
        else if(file_exists(AUIEO_FRAMEWORK_PATH."businessmodules/{$module}"))
        {
            return AUIEO_FRAMEWORK_PATH."businessmodules/{$module}/";
        }
        return false;
    }
    public static function getLibraryModulePath($module)
    {
        if(file_exists("librarymodules/{$module}"))
        {
            return "librarymodules/{$module}/";
        }
        else if(file_exists(AUIEO_FRAMEWORK_PATH."librarymodules/{$module}"))
        {
            return AUIEO_FRAMEWORK_PATH."librarymodules/{$module}/";
        }
        return false;
    }
    public static function getBusinessObject($module)
    {
        $class="ClsB".ucfirst($module);
        if(!class_exists($class))
        {
            $path=self::getBusinessModulePath($module);
            include_once $path.$class.".php";
        }
        $bObj=new $class();
        return $bObj;
    }
    public static function getLibraryObject($module)
    {
        $class="ClsL".ucfirst($module);
        if(!class_exists($class))
        {
            $path=self::getLibraryModulePath($module);
            include_once $path.$class.".php";
        }
        $bBObj=new $class();
        return $bObj;
    }
}
?>