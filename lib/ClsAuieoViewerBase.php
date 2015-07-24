<?php
class ClsAuieoViewerBase
{
    protected $loadVarAsAttribute=false;
    public $theme="default";
    public $loadScript="";
    public $loadStyle="";
    public $includeScript="";
    public $includeStyle="";
    protected $isThemeRender=false;
    
    public function __construct()
    {
        
    }
    public function &get_select($arrData, $name="default")
    {
        $arg[0]=$arrData;
        $func=explode("::",__METHOD__);
        $func=$func[1];
        $resourceName=substr($func, 4, strlen($func)-4);
        $arrTplVar=$arg[0];
        if(!isset($arrTplVar))$arrTplVar=array();
        if(empty($this->theme)) die("Theme not set for getting button");
        $obj=ClsResource::getInstance($this->theme);
        $ret=$obj->validateInputParam($resourceName,$name,$arrTplVar);
        if($ret!==true)
        {
            addLog("{$resourceName} parameters(".implode(", ",$ret).") expected");
            die("{$resourceName} parameters(".implode(", ",$ret).") expected");
        }
        $getResourceClass="get".ucfirst($resourceName);
        $button=$obj->$getResourceClass($arrTplVar,$name);
        return $button;
    }
    public function setTheme($theme)
    {
        $this->theme=$theme;
    }
    public function setLoadScript($loadScript)
    {
        $this->loadScript=$loadScript;
    }
    public function setLoadStyle($loadStyle)
    {
        $this->loadStyle=$loadStyle;
    }
    public function setIncludeScript($includeScript)
    {
        $this->includeScript=$includeScript;
    }
    public function setIncludeStyle($includeStyle)
    {
        $this->includeStyle=$includeStyle;
    }
    public function loadVarAsAttribute()
    {
        $this->loadVarAsAttribute=true;
    }
    public function setPath($templatePath)
    {
        $this->templatePath=$templatePath;
    }
    public function getThemeImage($imageName)
    {
        if(file_exists("themes/{$this->theme}/images/{$imageName}")) return "themes/{$this->theme}/images/{$imageName}";
        if(file_exists("themes/{$this->theme}/image/{$imageName}")) return "themes/{$this->theme}/image/{$imageName}";
        if(file_exists("themes/images/{$imageName}")) return "themes/images/{$imageName}";
        if(file_exists("themes/image/{$imageName}")) return "themes/image/{$imageName}";
        return null;
    }
    public function assign($name,$data)
    {
        $this->arrVar[$name]=$data;
    }
    public function assign_by_ref($name,&$data)
    {
        $this->arrVar[$name]=$data;
    }
    public function addVar($data,$name)
    {
        $this->arrVar[$name]=$data;
    }
    
    public function getVar($name)
    {
        if($this->loadVarAsAttribute && isset($this->$name)) return $this->$name;
        if(isset($this->arrVar[$name])) return $this->arrVar[$name];
        return null;
    }
    public function isVarSet($var)
    {
        if($this->loadVarAsAttribute && isset($this->$var)) return true;
        if(isset($this->arrVar[$var])) return true;
        return false;
    }
    public function getThemeVariables()
    {
        $arrScript=  loadThemeScript();
            $loadScript="";
            if(!empty($arrScript))
            {
                $loadScript=implode("
    ",$arrScript);            
            }

            $arrIncludeScript=includeThemeScript();
            $includeScript="";
            if(!empty($arrIncludeScript))
            {
                $includeScript='<script type="text/javascript" src="'.implode('"></script><script type="text/javascript" src="
    ',$arrIncludeScript).'"></script>';            
            }

            $arrStyle=  loadThemeStyle();
            $loadStyle="";
            if(!empty($arrStyle))
            {
                $loadStyle=implode("
    ",$arrStyle);            
            }

            $arrIncludeStyle=includeThemeStyle();
            $includeStyle="";
            if(!empty($arrIncludeStyle))
            {
                $includeStyle='<link rel="stylesheet" type="text/css" href="'.implode('" /><link rel="stylesheet" type="text/css" href="
    ',$arrIncludeStyle).'" />';            
            }
            return array("load_style"=>$loadStyle,"load_script"=>$loadScript,"include_style"=>$includeStyle,"include_script"=>$includeScript);
    }
    public function &renderBase(&$_AUIEO_FUNCTION_CONTENT, $_AUIEO_TEMPLATE_PHP_FILE=false,&$arrVar=false)
    {
        Logger::getLogger("APF")->info("inside the renderBase");
        if(!empty($arrVar))
        {
            extract($arrVar);
        }
        
        if($_AUIEO_TEMPLATE_PHP_FILE!==false)
            include($_AUIEO_TEMPLATE_PHP_FILE);
        unset($_AUIEO_TEMPLATE_PHP_FILE);
        /**
        * loading the assigned variables from php layer of the template file
        */
        $load_script="";
        if($this->loadScript) $load_script=$this->loadScript;
        $load_style="";
        if($this->loadStyle) $load_style=$this->loadStyle;
        
        $include_script="";
        if($this->includeScript) $include_script=$this->includeScript;
        $include_style="";
        if($this->includeStyle) $include_style=$this->includeStyle;
        
       if($this->loadVarAsAttribute)
       {
           $arrVar=  get_defined_vars();
           foreach($arrVar as $__var=>$__data)
           {
               if($__var=="arrVar") continue;
               $this->$__var=$__data;
           }
       }
       if($this->isThemeRender)
    {
        extract($this->getThemeVariables());
    }
        /**
         * for handing comment in html template. usage is {$_("This is comment")}
         */
        $_=function($comment)
        {
            return "";
        };
        /**
         * load variable from database if page is loaded from database
         */
        if(ClsConfig::$DATABASE && isset(ClsConfig::$DATABASE_MODULE_TABLE))
        {
            if(isset($module))
            {
                include_once(__DIR__."/ClsAuieoDatabasePage.php");
                $pageID=ClsAuieoDatabasePage::getPageID($module);
                if(!empty($pageID))
                {
                    $objDatabasePage=new ClsAuieoDatabasePage($pageID);
                    $arrTplVar=$objDatabasePage->getAll();
                    extract($arrTplVar); 
                }
            }
        }
        
        try
        {
            if(!isset($content)) $content="";
        ob_start();
        $_NAANAL_TPL_CONTENT="";
        eval('echo <<< EOT
'.$_AUIEO_FUNCTION_CONTENT.'
EOT;
');
        $_NAANAL_TPL_CONTENT = ob_get_clean();
    }
        catch(Exception $e)
        {
            trace($e);
        }
        $_NAANAL_TPL_CONTENT = isset($_NAANAL_TPL_CONTENT)?$_NAANAL_TPL_CONTENT:"";
        return $_NAANAL_TPL_CONTENT;
    }
    
    public function &renderThemeBase(&$_AUIEO_FUNCTION_CONTENT, $_AUIEO_TEMPLATE_PHP_FILE=false,&$arrVar=false)
    {
        Logger::getLogger("APF")->info("inside the renderBase");
        if(!empty($arrVar))
        {
            extract($arrVar);
        }
        
        if($_AUIEO_TEMPLATE_PHP_FILE!==false)
        {
            include($_AUIEO_TEMPLATE_PHP_FILE);
        }
        unset($_AUIEO_TEMPLATE_PHP_FILE);
        /**
        * loading the assigned variables from php layer of the template file
        */
        $load_script="";
        if($this->loadScript) $load_script=$this->loadScript;
        $load_style="";
        if($this->loadStyle) $load_style=$this->loadStyle;
        
        $include_script="";
        if($this->includeScript) $include_script=$this->includeScript;
        $include_style="";
        if($this->includeStyle) $include_style=$this->includeStyle;
        
       if($this->loadVarAsAttribute)
       {
           $arrVar=  get_defined_vars();
           foreach($arrVar as $__var=>$__data)
           {
               if($__var=="arrVar") continue;
               $this->$__var=$__data;
           }
       }
       if($this->isThemeRender)
    {
        extract($this->getThemeVariables());
    }
        /**
         * for handing comment in html template. usage is {$_("This is comment")}
         */
        $_=function($comment)
        {
            return "";
        };//trace($_AUIEO_FUNCTION_CONTENT,3);
        try
        {
        ob_start();
        $_NAANAL_TPL_CONTENT="";
        eval('echo <<< EOT
'.$_AUIEO_FUNCTION_CONTENT.'
EOT;
');
        $_NAANAL_TPL_CONTENT = ob_get_clean();
    }
        catch(Exception $e)
        {
            trace($e);
        }
        $_NAANAL_TPL_CONTENT = isset($_NAANAL_TPL_CONTENT)?$_NAANAL_TPL_CONTENT:"";
        return $_NAANAL_TPL_CONTENT;
    }
    
    function &get_button($arrTplVar,$name="default")
    {
        if(empty($this->theme)) die("Theme not set for getting button");
        if(empty($arrTplVar) || !isset($arrTplVar)) die("Button parameters expected");
        $obj=ClsResource::getInstance($this->theme);
        $ret=$obj->validateInputParam("button",$name,$arrTplVar);
        if($ret!==true)
        {
            addLog("Button parameters(".implode(", ",$ret).") expected");
            die("Button parameters(".implode(", ",$ret).") expected");
        }
        $button=$obj->getButton($arrTplVar,$name);
        return $button;
    }
    function &get_login($arrTplVar,$name="default")
    {
        if(!isset($arrTplVar))$arrTplVar=array();
        if(empty($this->theme)) die("Theme not set for getting button");
        $obj=ClsResource::getInstance($this->theme);
        $ret=$obj->validateInputParam("login",$name,$arrTplVar);
        if($ret!==true)
        {
            addLog("Login parameters(".implode(", ",$ret).") expected");
            die("Login parameters(".implode(", ",$ret).") expected");
        }
        $button=$obj->getLogin($arrTplVar,$name);
        return $button;
    }
    function &get_form($arrTplVar,$name="default")
    {
        if(!isset($arrTplVar))$arrTplVar=array();
        if(empty($this->theme)) die("Theme not set for getting button");
        $obj=ClsResource::getInstance($this->theme);
        $ret=$obj->validateInputParam("forms",$name,$arrTplVar);
        if($ret!==true)
        {
            addLog("Forms parameters(".implode(", ",$ret).") expected");
            die("Form parameters(".implode(", ",$ret).") expected");
        }
        $button=$obj->getForm($arrTplVar,$name);
        return $button;
    }
    function &get_menu($arrTplVar,$name="default")
    {
        if(empty($this->theme)) die("Theme not set for getting button");
        if(empty($arrTplVar) || !isset($arrTplVar)) die("Menu parameters expected");
        $obj=ClsResource::getInstance($this->theme);
        $ret=$obj->validateInputParam("menu",$name,$arrTplVar);
        if($ret!==true)
        {
            addLog("Menu parameters(".implode(", ",$ret).") expected");
            die("Menu parameters(".implode(", ",$ret).") expected");
        }
        $button=$obj->getMenu($arrTplVar,$name);
        return $button;
    }
    function &get_menucontainer($arrTplVar,$name="default")
    {
        if(empty($this->theme)) die("Theme not set for getting button");
        if(empty($arrTplVar) || !isset($arrTplVar)) die("Menu Container parameters expected");
        $obj=ClsResource::getInstance($this->theme);
        $ret=$obj->validateInputParam("menucontainer",$name,$arrTplVar);
        if($ret!==true)
        {
            addLog("Menucontainer parameters(".implode(", ",$ret).") expected");
            die("Menucontainer parameters(".implode(", ",$ret).") expected");
        }
        
        $button=$obj->getMenucontainer($arrTplVar,$name);
        return $button;
    }
    function &get_list($arrTplVar,$name="default")
    {
        if(empty($this->theme)) die("Theme not set for getting button");
        if(empty($arrTplVar) || !isset($arrTplVar)) die("List parameters expected");
        $obj=ClsResource::getInstance($this->theme);
        $ret=$obj->validateInputParam("list",$name,$arrTplVar);
        if($ret!==true)
        {
            addLog("List parameters(".implode(", ",$ret).") expected");
            die("List parameters(".implode(", ",$ret).") expected");
        }
        $button=$obj->getList($arrTplVar,$name);
        return $button;
    }
    function &get_listcontainer($arrTplVar,$name="default")
    {
        if(empty($this->theme)) die("Theme not set for getting button");
        if(empty($arrTplVar) || !isset($arrTplVar)) die("List Container parameters expected");
        $obj=ClsResource::getInstance($this->theme);
        $ret=$obj->validateInputParam("listcontainer",$name,$arrTplVar);
        if($ret!==true)
        {
            addLog("Listcontainer parameters(".implode(", ",$ret).") expected");
            die("Listcontainer parameters(".implode(", ",$ret).") expected");
        }
        
        $button=$obj->getListcontainer($arrTplVar,$name);
        return $button;
    }
    function &get_checkbox($arrTplVar,$name="default")
    {
        if(empty($this->theme)) die("Theme not set for getting button");
        if(empty($arrTplVar) || !isset($arrTplVar)) die("Checkbox parameters expected");
        $obj=ClsResource::getInstance($this->theme);
        $ret=$obj->validateInputParam("checkbox",$name,$arrTplVar);
        if($ret!==true)
        {
            addLog("Checkbox parameters(".implode(", ",$ret).") expected");
            die("Checkbox parameters(".implode(", ",$ret).") expected");
        }
        $button=$obj->getCheckbox($arrTplVar,$name);
        return $button;
    }
    function &get_textbox($arrTplVar,$name="default")
    {
        if(empty($this->theme)) die("Theme not set for getting button");
        if(empty($arrTplVar) || !isset($arrTplVar)) 
        {
            addLog("Textbox parameters expected");
            die("Textbox parameters expected");
        }
        $obj=ClsResource::getInstance($this->theme);
        $ret=$obj->validateInputParam("textbox",$arrTplVar);
        if($ret!==true)
        {
            addLog("Textbox parameters(".implode(", ",$ret).") expected");
            die("Textbox parameters(".implode(", ",$ret).") expected");
        }
        $button=$obj->getTextbox($arrTplVar,$name);
        return $button;
    }
    function &get_textbox_object($arrTplVar,$name="default")
    {
        if(empty($this->theme)) die("Theme not set for getting button");
        if(empty($arrTplVar) || !isset($arrTplVar)) die("Textbox parameters expected");
        $obj=ClsResource::getInstance($this->theme);
        $ret=$obj->validateInputParam("textbox",$name,$arrTplVar);
        if($ret!==true)
        {
            addLog("Textbox parameters(".implode(", ",$ret).") expected");
            die("Textbox parameters(".implode(", ",$ret).") expected");
        }
        $button=$obj->getTextboxObject($arrTplVar,$name);
        return $button;
    }
    function &get_textarea($arrTplVar,$name="default")
    {
        if(empty($this->theme)) die("Theme not set for getting button");
        if(empty($arrTplVar) || !isset($arrTplVar)) die("Textarea parameters expected");
        $obj=ClsResource::getInstance($this->theme);
        $ret=$obj->validateInputParam("textarea",$name,$arrTplVar);
        if($ret!==true)
        {
            addLog("Textarea parameters(".implode(", ",$ret).") expected");
            die("Textarea parameters(".implode(", ",$ret).") expected");
        }
        $button=$obj->getTextarea($arrTplVar,$name);
        return $button;
    }
    function &get_table($arrTplVar,$name="default")
    {
        if(empty($this->theme)) die("Theme not set for getting button");
        if(!isset($arrTplVar)) die("Table parameters not set");
        $obj=ClsResource::getInstance($this->theme);
        $ret=$obj->validateInputParam("table",$name,$arrTplVar);
        if($ret!==true)
        {
            addLog("Table parameters(".implode(", ",$ret).") expected");
            die("Table parameters(".implode(", ",$ret).") expected");
        }       
        $button=$obj->getTable($arrTplVar,$name);     
        return $button;
    }
    function &get_table_object($arrTplVar,$name="default")
    {
        if(empty($this->theme)) die("Theme not set for getting button");
        if(empty($arrTplVar) || !isset($arrTplVar)) die("Table parameters not set");
        $obj=ClsResource::getInstance($this->theme);
        $ret=$obj->validateInputParam("table",$name,$arrTplVar);
        if($ret!==true)
        {
            addLog("Table parameters(".implode(", ",$ret).") expected");
            die("Table parameters(".implode(", ",$ret).") expected");
        }       
        $button=$obj->getTableObject($arrTplVar,$name);     
        return $button;
    }
    function &get_file($arrTplVar,$name="default")
    {
        if(empty($this->theme)) die("Theme not set for getting button");
        if(empty($arrTplVar) || !isset($arrTplVar)) die("File parameters not set");
        $obj=ClsResource::getInstance($this->theme);
        $ret=$obj->validateInputParam("file",$name,$arrTplVar);
        if($ret!==true)
        {
            addLog("File parameters(".implode(", ",$ret).") expected");
            die("File parameters(".implode(", ",$ret).") expected");
        }
        $button=$obj->getFile($arrTplVar,$name);
        return $button;
    }
    function &get_radio($arrTplVar,$name="default")
    {
        if(empty($this->theme)) die("Theme not set for getting button");
        if(empty($arrTplVar) || !isset($arrTplVar)) die("Radio parameters not set");
        $obj=ClsResource::getInstance($this->theme);
        $ret=$obj->validateInputParam("radio",$name,$arrTplVar);
        if($ret!==true)
        {
            addLog("Radio parameters(".implode(", ",$ret).") expected");
            die("Radio parameters(".implode(", ",$ret).") expected");
        }
        $button=$obj->getRadio($arrTplVar,$name);
        return $button;
    }
    function &get_widget($arrTplVar,$name="default")
    {
        if(empty($this->theme)) die("Theme not set for getting button");
        if(empty($arrTplVar) || !isset($arrTplVar)) die("Widget parameters not set");
        $obj=ClsResource::getInstance($this->theme);
        $ret=$obj->validateInputParam("widget",$name,$arrTplVar);
        if($ret!==true)
        {
            addLog("Widger parameters(".implode(", ",$ret).") expected");
            die("Widget parameters(".implode(", ",$ret).") expected");
        }
        $button=$obj->getWidget($arrTplVar,$name);
        return $button;
    }
    function __get($name)
    {
        return "";
    }
}
?>