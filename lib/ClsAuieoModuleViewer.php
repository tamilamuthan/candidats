<?php
include_once(dirname(__FILE__)."/ClsAuieoViewerBase.php");
class ClsAuieoModuleViewer extends ClsAuieoViewerBase
{
    protected $arrVar=array();
        protected $MODULE="";
        protected $ACTION="";
        protected $SWITCH="";
        protected $modulePath="";
        protected $isPage=false;
        protected $arrConfigVar=array();
        private $arrHeaderData=array();
        private $templateHTMLFile=false;
        private $themePath=false;
        private $templatePath=false;
        
    public function __construct($module,$action=false,$switch=false)
    {
        $this->MODULE=$module;
        $this->ACTION=$action;
        $this->SWITCH=$switch;
        $this->assign('MODULE', $this->MODULE);
        $this->modulePath="modules/";
        $this->arrConfigVar=ClsNaanalApplication::getConfigVars($module);
        parent::__construct();
    }
    
    protected function getConfigVar($name)
    {
        if(array_key_exists($name,$this->arrConfigVar))
        {
            return $this->arrConfigVar[$name];
        }
        else
        {
            return null;
        }
    }
    public function renderAuieoTheme(&$_AUIEO_FUNCTION_CONTENT, $_AUIEO_THEME,$_AUIEO_THEME_DIRECTORY=false,$file=false)
    {
        $this->isThemeRender=true;
        if($_AUIEO_THEME_DIRECTORY===false)
        {
            $_AUIEO_TEMPLATE_DIR_PATH="themes/{$_AUIEO_THEME}/";
        }
        else
        {
            $_AUIEO_TEMPLATE_DIR_PATH="{$_AUIEO_THEME_DIRECTORY}{$_AUIEO_THEME}/";
        }
        if(method_exists($this, "loadCommonThemeTemplateVars"))
            $this->loadCommonThemeTemplateVars($file);
        $this->loadCurrentURL();
        /**
         * loading the assigned variables
         */
        if($this->loadVarAsAttribute)
        {
            foreach($this->arrVar as $__var=>$__data)
            {
                if($__var=="arrVar") continue;
                $this->$__var=$__data;
            }
            unset($__var);
            unset($__data);
        }

        $renderBase = parent::renderThemeBase($_AUIEO_FUNCTION_CONTENT,$_AUIEO_TEMPLATE_DIR_PATH."{$file}.php",$this->arrVar);
        return $renderBase;
    }
    public function renderTheme($_AUIEO_THEME,$_AUIEO_THEME_DIRECTORY=false,$file=false,&$_AUIEO_HTML_DOM=null)
    {
        $this->isThemeRender=true;
        if($_AUIEO_THEME_DIRECTORY===false)
        {
            $_AUIEO_TEMPLATE_DIR_PATH="themes/{$_AUIEO_THEME}/";
        }
        else
        {
            $_AUIEO_TEMPLATE_DIR_PATH="{$_AUIEO_THEME_DIRECTORY}{$_AUIEO_THEME}/";
        }
        if($file===false) 
        {
            $file="inner";
            if($this->MODULE=="home" && (file_exists($_AUIEO_TEMPLATE_DIR_PATH."home.php") || file_exists($_AUIEO_TEMPLATE_DIR_PATH."home.html") || file_exists($_AUIEO_TEMPLATE_DIR_PATH."home.htm")))
            {
                $file="home";
            }
        }
        if($file!==false)
        {
            $_AUIEO_HTML_DOM=new DOMDocument();
            $_AUIEO_HTML_DOM->loadHTMLFile("themes/{$_AUIEO_THEME}/{$file}.html");
        }
        if(method_exists($this, "loadCommonThemeTemplateVars"))
            $this->loadCommonThemeTemplateVars($file);
        $this->loadCurrentURL();
        /**
         * loading the assigned variables
         */
        if($this->loadVarAsAttribute)
        {
            foreach($this->arrVar as $__var=>$__data)
            {
                if($__var=="arrVar") continue;
                $this->$__var=$__data;
            }
            unset($__var);
            unset($__data);
        }
        //else
        //{
        //    extract($this->arrVar);
        //}
        $_AUIEO_FUNCTION_CONTENT=$_AUIEO_HTML_DOM->saveHTML();
        $_AUIEO_FUNCTION_CONTENT=html_entity_decode($_AUIEO_FUNCTION_CONTENT);

        //$arrVar=  get_defined_vars();
        $renderBase = parent::renderThemeBase($_AUIEO_FUNCTION_CONTENT,$_AUIEO_TEMPLATE_DIR_PATH."{$file}.php",$this->arrVar);
        return $renderBase;
        
        //echo $_AUIEO_FUNCTION_CONTENT;exit;
        //$render = $this->__render($_AUIEO_TEMPLATE_DIR_PATH."{$file}.php",$this->templateHTMLFile,$_AUIEO_HTML_DOM);
        //return $render;
        //$renderTemplate = $this->renderTemplate($_AUIEO_TEMPLATE_DIR_PATH, $file, $_AUIEO_HTML_DOM);
        //return $renderTemplate;
    }
    public function loadCurrentURL()
    {
        $arrUrl=array();
        foreach(ClsNaanalRequest::getInstance()->getAll() as $k=>$v)
        {
            $arrUrl[$k]=$v;
        }
        $current_url=  json_encode($arrUrl);
        $this->assign("current_url",$current_url);
    }
    public function __render($_AUIEO_TEMPLATE_PHP_FILE,$_AUIEO_TEMPLATE_HTML_FILE=false,&$_AUIEO_HTML_DOM=null)
    {
        $this->loadCurrentURL();
        $_AUIEO_FUNCTION_CONTENT=false;
        if(!is_null($_AUIEO_HTML_DOM))
        {
            $_AUIEO_FUNCTION_CONTENT=$_AUIEO_HTML_DOM->saveHTML();
            $_AUIEO_FUNCTION_CONTENT=html_entity_decode($_AUIEO_FUNCTION_CONTENT);
        }
        else
        {
            if(!empty($_AUIEO_TEMPLATE_HTML_FILE))
            {
                $_AUIEO_FUNCTION_CONTENT=file_get_contents($_AUIEO_TEMPLATE_HTML_FILE);
                $_AUIEO_FUNCTION_CONTENT=html_entity_decode($_AUIEO_FUNCTION_CONTENT);
            }
        }
        /**
         * loading the assigned variables
         */
        if($this->loadVarAsAttribute)
        {
            foreach($this->arrVar as $__var=>$__data)
            {
                if($__var=="arrVar") continue;
                $this->$__var=$__data;
            }
            unset($__var);
            unset($__data);
        }
        else
        {
            extract($this->arrVar);
        }
        if($_AUIEO_FUNCTION_CONTENT===false)
        {
            ob_start();
            include($_AUIEO_TEMPLATE_PHP_FILE);
            return ob_get_clean();
        }
        else
        {
            $arrVar=  get_defined_vars();
            $renderBase = parent::renderBase($_AUIEO_FUNCTION_CONTENT,$_AUIEO_TEMPLATE_PHP_FILE,$arrVar);
            return $renderBase;
        }
    }
	public function display($tplFile)
    {
        echo $this->fetch($tplFile);
    }
    public function fetch($tplFile)
    {
        $arr=explode(".tpl",$tplFile);
        //echo $this->renderTemplate($this->templatePath, $arr[0]);
        $this->loadCurrentURL();
        $templateHTMLFile=false;
        if(file_exists($this->templatePath."{$arr[0]}.html"))
        {
            $templateHTMLFile=$this->templatePath."{$arr[0]}.html";
        }
        else if(file_exists($this->templatePath."{$arr[0]}.htm"))
        {
            $templateHTMLFile=$this->templatePath."{$arr[0]}.htm";
        }
        else if(file_exists($this->templatePath."{$arr[0]}.tpl.html"))
        {
            $templateHTMLFile=$this->templatePath."{$arr[0]}.tpl.html";
        }
        if(file_exists($this->templatePath."{$arr[0]}.php"))
        {
            return $this->__render($this->templatePath."{$arr[0]}.php",$templateHTMLFile);
        }
        else
        {
            return $this->__render($this->templatePath."{$arr[0]}.tpl.php",$templateHTMLFile);
        }
    }
    public function setPath($templatePath)
    {
        $this->templatePath=$templatePath;
    }
    ///try to skip this method. use __render directly
    public function renderTemplate($_AUIEO_TEMPLATE_DIR_PATH,$_AUIEO_FUNCTION_FILE,&$_AUIEO_HTML_DOM=null)
    {
        $this->loadCurrentURL();
        if(!is_null($_AUIEO_HTML_DOM))
        {
            $render = $this->__render($_AUIEO_TEMPLATE_DIR_PATH."{$_AUIEO_FUNCTION_FILE}.php",$this->templateHTMLFile,$_AUIEO_HTML_DOM);
            return $render;
        }
        else
        {
            $_AUIEO_FUNCTION_CONTENT=false;
            if($this->templateHTMLFile!==false)
            {
                //$_AUIEO_FUNCTION_CONTENT=file_get_contents($this->templateHTMLFile);
            }
            else if(file_exists($_AUIEO_TEMPLATE_DIR_PATH."{$_AUIEO_FUNCTION_FILE}.html"))
            {
                $this->templateHTMLFile=$_AUIEO_TEMPLATE_DIR_PATH."{$_AUIEO_FUNCTION_FILE}.html";
            }
            else if(file_exists($_AUIEO_TEMPLATE_DIR_PATH."{$_AUIEO_FUNCTION_FILE}.htm"))
            {
                $this->templateHTMLFile=$_AUIEO_TEMPLATE_DIR_PATH."{$_AUIEO_FUNCTION_FILE}.htm";
            }
            return $this->__render($_AUIEO_TEMPLATE_DIR_PATH."{$_AUIEO_FUNCTION_FILE}.php",$this->templateHTMLFile);
        }
    }
    public function renderMainTemplate($_AUIEO_PATH_INFO=false,$isWorkplace=false,$tplHTMLFile=null)
    {
        if(method_exists($this, "render"))
        {
            $this->render();
        }
        $_AUIEO_FUNCTION_FILE=$_AUIEO_PATH_INFO["filename"];
        if(!is_null($tplHTMLFile))
        {
            $this->templateHTMLFile=$tplHTMLFile;
            $_AUIEO_TEMPLATE_DIR_PATH=$_AUIEO_PATH_INFO["dirname"]."/";
        }
        else
        {
            $_AUIEO_TEMPLATE_DIR_PATH=$_AUIEO_PATH_INFO["dirname"]."/";
        }
        if($isWorkplace!==false)
        {
            $_AUIEO_TEMPLATE_DIR_PATH=$workplacePath=ClsNaanalApplication::__getWorkplacePath();
        }
        if(!file_exists($_AUIEO_TEMPLATE_DIR_PATH . $_AUIEO_FUNCTION_FILE . ".php") && !file_exists($_AUIEO_TEMPLATE_DIR_PATH . $_AUIEO_FUNCTION_FILE . ".html") && !file_exists($_AUIEO_TEMPLATE_DIR_PATH . $_AUIEO_FUNCTION_FILE . ".htm"))
        {
            $_AUIEO_FUNCTION_FILE="default";
        }
        return $this->renderTemplate($_AUIEO_TEMPLATE_DIR_PATH, $_AUIEO_FUNCTION_FILE);
    }
    public function renderSubTemplate($_AUIEO_FUNCTION_FILE)
    {
        $_AUIEO_TEMPLATE_DIR_PATH=$this->renderTemplateHTMLPath($this->MODULE, $this->ACTION, $this->SWITCH,$_AUIEO_FUNCTION_FILE);
        $templateHTMLFile=$_AUIEO_TEMPLATE_DIR_PATH.$_AUIEO_FUNCTION_FILE.".html";
        return $this->__render($_AUIEO_TEMPLATE_DIR_PATH."{$_AUIEO_FUNCTION_FILE}.php",$templateHTMLFile);
    }
    public function renderBlock($_AUIEO_BLOCK)
    {
        $_AUIEO_TEMPLATE_DIR_PATH=$this->modulePath."template/";
        return $this->renderTemplate($_AUIEO_TEMPLATE_DIR_PATH, $_AUIEO_FUNCTION_FILE);
    }
    public function getAppTitle()
    {
        $appTitle=loadAppTitle();
        if(!is_null($appTitle))
        {
            return $this->getLangVar($appTitle);
        }
    }
    public function __call($name,$arg)
    {
        if(strpos($name, "AUIEO_")===0)
        {
            $arrName=explode("_",$name);
            if($arrName[1]=="THEME" && $arrName[2]=="IMAGE")
            {
                return $this->getThemeImage($arg[0]);
            }
            else if($arrName[1]=="LANG")
            {
                return $this->getLangVar($arg[0]);
            }
        }
    }
    public function __get($name) 
    {
        if(strpos($name, "AUIEO_")===0)
        {
            $arrName=explode("_",$name);
            if($arrName[1]=="APP")
            {
                if($arrName[2]=="TITLE")
                {
                    return $this->getAppTitle();
                }
                else
                {
                    return "";
                }
            }
            else if($arrName[1]=="THEME")
            {
                return $this->getThemeVar($name);
            }
            else if($arrName[1]=="BLOCK")
            {
                return $this->getBlock($arrName[2]);
            }
            else if($arrName[1]=="CUSTOM")
            {
                return $this->getCustomVar($name);
            }
            else if($arrName[1]=="LANG")
            {
                return $this->getLangVar($name);
            }
            else if($arrName[1]=="MODULE")
            {
                return $this->getModuleVar($name);
            }
            else if($arrName[1]=="SUBMODULE")
            {
                $module=$arrName[2];
                $action=isset($arrName[3])?$arrName[3]:"create";
                return get_module_content($module, $action);
            }
            else if($arrName[1]=="TEMPLATE")
            {
                return $this->getTemplate($arrName[2]);
            }
            else if($arrName[1]=="CONFIG")
            {
                unset($arrName[0]);unset($arrName[1]);
                $newName=implode("_",$arrName);
                return $this->getConfigVar($newName);
            }
        }
        else
        {
            if(isset($this->$name)) return $this->$name;
            else if(isset($this->arrVar[$name])) $this->arrVar[$name];
            else return $name;
        }
    }
    ///verify the hook, whether to allow the include or not, then render the template
    public function getTemplate($_AUIEO_INPUT_NAME)
    {
        include($this->modulePath."hook/template.php");
        $_AUIEO_HOOK_RETURN=true;
        if(isset($$_AUIEO_INPUT_NAME))
        {
            $_AUIEO_HOOK_RETURN=$$_AUIEO_INPUT_NAME($this->arrVar);
        }
        if($_AUIEO_HOOK_RETURN)
        {
            return $this->renderSubTemplate($_AUIEO_INPUT_NAME);
        }
        else
        {
            return "";
        }
    }
    private function getLangVar($name)
    {
        static $arrData=array();
        //$name=  substr($name, strpos($name, "AUIEO_LANG"));
        if(isset($arrData[$name])) return $arrData[$name];
        $language=getAppConfig("LANG");
        if(!$language) $language ="default";
        include "lang/{$language}/common.php"; 
        if(file_exists($this->modulePath."lang/{$language}/common.php")) include $this->modulePath."lang/{$language}/common.php";
        if(isset($$name))
        {
            $arrData[$name]=$$name;
        }
        else
        {
            $arrData[$name]=$name;
        }
        return $arrData[$name];
    }
    private function getBlock($name)
    {
        include($this->modulePath."block.php");
        if(isset($$name))
        {
            return $$name();
        }
        else
        {
            return "";
        }
    }
    private function getCustomVar($name)
    {
        static $arrData=array();
        if(isset($arrData[$name])) return $arrData[$name];
        if(file_exists($this->modulePath."theme_hooks.php")) include $this->modulePath."theme_hooks.php";
        if(isset($$name))
        {
            $arrData[$name]=$$name();
        }
        else
        {
            $arrData[$name]="";
        }
        return $arrData[$name];
    }
    private function getThemeVar($name)
    {
        static $arrData=array();
        if(isset($arrData[$name])) return $arrData[$name];
        else
        {
            switch($name)
            {
                case "AUIEO_THEME_NAME": $arrData[$name] = $this->getTheme(); break;
                case "AUIEO_THEME_PATH": $arrData[$name] =  $this->getThemePath(); break;
                case "AUIEO_THEME_CSS_PATH": $arrData[$name] =  $this->getThemeCSSPath(); break;
                case "AUIEO_THEME_JS_PATH": $arrData[$name] =  $this->getThemeJSPath(); break;
                case "AUIEO_THEME_IMAGE_PATH": $arrData[$name] =  $this->getThemeImagePath(); break;
                default: $arrData[$name]=$name; break;
            }
        }
        return $arrData[$name];
    }
    private function getModuleVar($name)
    {
        static $arrData=array();
        if(isset($arrData[$name])) return $arrData[$name];
        else
        {
            switch($name)
            {
                case "AUIEO_MODULE_PATH": $arrData[$name] =  $this->getModuleCSSPath(); break;
                case "AUIEO_MODULE_CSS_PATH": $arrData[$name] =  $this->getModuleCSSPath(); break;
                case "AUIEO_MODULE_JS_PATH": $arrData[$name] =  $this->getModuleJSPath(); break;
                case "AUIEO_MODULE_IMAGE_PATH": $arrData[$name] =  $this->getModuleImagePath(); break;
                default: $arrData[$name]=$name; break;
            }
        }
        return $arrData[$name];
    }
    public function getModuleJSPath()
    {
        return $this->modulePath."js/";
    }
    public function getModuleCSSPath()
    {
        return $this->modulePath."css/";
    }
    public function getModuleImagePath()
    {
        return $this->modulePath."image/";
    }
    public function getTheme()
    {
        static $theme=null;
        if(is_null($theme))
        {
            $theme=$this->getConfigVar("theme");
            if($theme===true)
            {
                $theme=getAppConfig("theme");
            }
        }
        return $theme;
    }
    public function getThemePath()
    {
        $theme=$this->getTheme();
        $themePath=ClsNaanalApplication::getThemePath($theme).$theme."/";trace($themePath);
        return $themePath;
    }
    public function getThemeCSSPath()
    {
        $theme=$this->getTheme();
        $themePath=$this->getThemePath($theme);
        $css_path="";
        if(file_exists($themePath."config.php"))
        {
            include $themePath."config.php";
        }
        return $themePath.$css_path;
    }
    public function getThemeJSPath()
    {
        $theme=$this->getTheme();
        $themePath=$this->getThemePath($theme);
        $js_path="";
        if(file_exists($themePath."config.php"))
        {
            include $themePath."config.php";
        }
        return $themePath.$js_path;
    }
    public function getThemeImagePath()
    {
        $theme=$this->getTheme();
        $themePath=$this->getThemePath($theme);
        $js_path="";
        if(file_exists($themePath."config.php"))
        {
            include $themePath."config.php";
            return $themePath.$image_path;
        }
        else
        {
            return $themePath;
        }
    }
    public function setHeaderData($title,$arrJS=false,$arrCSS=false)
    {
        $this->arrHeaderData=array
        (
            "title"=>$title,
            "js_include"=>$arrJS,
            "css_include"=>$arrCSS
        );
        if($arrJS!==false)
        {
            foreach($arrJS as $js)
            {
                loadAppJS($js, $js);
            }
        }
        if($arrCSS!==false)
        {
            foreach($arrCSS as $css)
            {
                loadAppJS($css, $css);
            }
        }
        loadAppTitle($title);
    }
    public function getHeaderData()
    {
        return $this->arrHeaderData;
    }
    protected function renderTemplateHTMLPath($module,$action="create",$switch="default",$subTemplate=false)
    {
        $modulePath = ClsNaanalApplication::getAbsModulePath($module);
        $htmlPath=null;
        if($subTemplate===false)
        {
            if(file_exists($modulePath."{$action}/{$switch}.html"))
            {
                $htmlPath=$modulePath."{$action}/";
            }
            else if(file_exists($modulePath."{$action}/{$switch}.htm"))
            {
                $htmlPath=$modulePath."{$action}/";
            }
            else if(file_exists($modulePath."{$action}.html"))
            {
                $htmlPath=$modulePath;
            }
            else if(file_exists($modulePath."{$action}.htm"))
            {
                $htmlPath=$modulePath;
            }
            if(isset(ClsConfig::$TEMPLATE_HTML_PATH) && !empty(ClsConfig::$TEMPLATE_HTML_PATH))
            {
                if(file_exists(ClsConfig::$TEMPLATE_HTML_PATH."{$module}/{$action}/{$switch}.html"))
                {
                    $htmlPath=ClsConfig::$TEMPLATE_HTML_PATH."{$module}/{$action}/";
                }
                else if(file_exists(ClsConfig::$TEMPLATE_HTML_PATH."{$module}/{$action}/{$switch}.htm"))
                {
                    $htmlPath=ClsConfig::$TEMPLATE_HTML_PATH."{$module}/{$action}/";
                }
                else if(file_exists(ClsConfig::$TEMPLATE_HTML_PATH."{$module}/{$action}.html"))
                {
                    $htmlPath=ClsConfig::$TEMPLATE_HTML_PATH."{$module}/";
                }
                else if(file_exists(ClsConfig::$TEMPLATE_HTML_PATH."{$module}/{$action}.htm"))
                {
                    $htmlPath=ClsConfig::$TEMPLATE_HTML_PATH."{$module}/";
                }
            }
        }
        else
        {
            if(!empty($action) && file_exists($modulePath."{$action}/template/{$subTemplate}.html"))
            {
                $htmlPath=$modulePath."{$action}/template/";
            }
            else if(!empty($action) && file_exists($modulePath."{$action}/template/{$subTemplate}.htm"))
            {
                $htmlPath=$modulePath."{$action}/template/";
            }
            else if(file_exists($modulePath."template/{$subTemplate}.html"))
            {
                $htmlPath=$modulePath."template/";
            }
            else if(file_exists($modulePath."template/{$subTemplate}.htm"))
            {
                $htmlPath=$modulePath."template/";
            }
            if(isset(ClsConfig::$TEMPLATE_HTML_PATH) && !empty(ClsConfig::$TEMPLATE_HTML_PATH))
            {
                if(file_exists(ClsConfig::$TEMPLATE_HTML_PATH."{$module}/{$action}/template/{$subTemplate}.html"))
                {
                    $htmlPath=ClsConfig::$TEMPLATE_HTML_PATH."{$module}/{$action}/template/";
                }
                else if(file_exists(ClsConfig::$TEMPLATE_HTML_PATH."{$module}/{$action}/template/{$subTemplate}.htm"))
                {
                    $htmlPath=ClsConfig::$TEMPLATE_HTML_PATH."{$module}/{$action}/template/";
                }
                else if(file_exists(ClsConfig::$TEMPLATE_HTML_PATH."{$module}/template/{$subTemplate}.html"))
                {
                    $htmlPath=ClsConfig::$TEMPLATE_HTML_PATH."{$module}/template/";
                }
                else if(file_exists(ClsConfig::$TEMPLATE_HTML_PATH."{$module}/template/{$subTemplate}.htm"))
                {
                    $htmlPath=ClsConfig::$TEMPLATE_HTML_PATH."{$module}/template/";
                }
            }
        }
        return $htmlPath;
    }
    protected function renderTemplatePHPPath($module,$action="create",$switch="default",$subTemplate=false)
    {
        $modulePath = ClsNaanalApplication::getAbsModulePath($module);
        $path=null;
        if(file_exists($modulePath."{$action}/{$switch}.php"))
        {
            $path=$modulePath."{$action}/";
        }
        else if(file_exists($modulePath."{$action}.php"))
        {
            $path=$modulePath;
        }
        if(isset(ClsConfig::$TEMPLATE_PHP_PATH) && !empty(ClsConfig::$TEMPLATE_PHP_PATH))
        {
            if(file_exists(ClsConfig::$TEMPLATE_PHP_PATH."{$module}/{$action}/{$switch}.php"))
            {
                $path=ClsConfig::$TEMPLATE_PHP_PATH."{$module}/{$action}/";
            }
            else if(file_exists(ClsConfig::$TEMPLATE_PHP_PATH."{$module}/{$action}.php"))
            {
                $path=ClsConfig::$TEMPLATE_PHP_PATH."{$module}/";
            }
        }
        return $path;
    }
}
?>