<?php
/**************************************************************************
 * Naanal PHP Framework, Simple, Efficient and Developer Friendly
 * Ver 3.0, Copyright (C) <2010>  <Tamil Amuthan. R>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ************************************************************************/
include_once(__DIR__."/utils.php");
class ClsLInputValidator
{
    protected $module="home";
    protected $action="create";
    protected $encryptURL=false;
    private $output = array();
    protected $cookie=array();
    protected $objNaanalSession=null;
    protected $objNaanalRequest=null;
    protected $objNaanalPost=null;
    protected $objNaanalGet=null;
    protected $files=array();
    protected $server=array();
    protected $fldUserID="userid";
    protected $fldUser="lastname";
    protected $fldPassword="password";
    function __construct($controlpanel,$encryptURL=false,$ipFilterControlpanel=null,$homepage=false)
    {
        $autoSession=getConfigVar("AUTO_SESSION");
        if(isset($_REQUEST["webservice"]))
        {
            if($_REQUEST["webservice"]=="getchallenge")
            {
                session_start();
            }
            else if(isset($_REQUEST["accessKey"]))
            {
                $sql="select * from webservice where accessKey='{$_REQUEST["accessKey"]}'";
                $pdo = connectPDO(getAppConfig("DATABASE_HOST"), getAppConfig("DATABASE_USER"), getAppConfig("DATABASE_PASSWORD"), getAppConfig("DATABASE_NAME"),  getAppConfig("DATABASE_SERVER"));//ClsNaanalPDO::getNamedInstance();
                try
                {
                    $statement=$pdo->query($sql);
                    $arr=$statement->fetchAll(PDO::FETCH_ASSOC);
                    $_SESSION= unserialize(base64_decode($arr[0]["session_object"]));
                    if($arr[0]["logged"])
                    {
                        $arrData=array();
                        $arrData["error"]="You already logged.";
                        die(json_encode($arrData));
                    }
                }
                catch(Exception $e)
                {
                    die($e);
                }
            }
            else if(isset($_REQUEST["sessionName"]))
            {
                $sql="select * from webservice where session='{$_REQUEST["sessionName"]}'";
                $pdo = connectPDO(getAppConfig("DATABASE_HOST"), getAppConfig("DATABASE_USER"), getAppConfig("DATABASE_PASSWORD"), getAppConfig("DATABASE_NAME"),  getAppConfig("DATABASE_SERVER"));//ClsNaanalPDO::getNamedInstance();
                try
                {
                    $statement=$pdo->query($sql);
                    $arr=$statement->fetchAll(PDO::FETCH_ASSOC);
                    $_SESSION= unserialize(base64_decode($arr[0]["session_object"]));
                }
                catch(Exception $e)
                {
                    die($e);
                }
            }
        }
        else
        {
            if(!($autoSession===false))
            {
                session_start();
            }
        }
        if(isset($_REQUEST["webservice"]))
        {
            if(isset($_POST["element"]))
            {
                $arr=json_decode($_POST["element"]);
                foreach($arr as $pk=>$pv)
                {
                    if($_REQUEST["webservice"]=="getchallenge")
                    {
                        if($pk=="webservice" || $pk=="loginname") continue;
                    }
                    else if($_REQUEST["webservice"]=="getchallenge")
                    {
                        if($pk=="webservice" || $pk=="accessKey" || $pk=="loginname") continue;
                    }
                    else
                    {
                        if($pk=="element" || $pk=="webservice" || $pk=="sessionName" || $pk=="elementType") continue;
                    }
                    $_POST[$pk]=$pv;
                }
            }
            else if(isset($_GET["element"]))
            {
                $arr=json_decode($_POST["element"]);
                foreach($arr as $pk=>$pv)
                {
                    if($_REQUEST["webservice"]=="getchallenge")
                    {
                        if($pk=="webservice" || $pk=="loginname") continue;
                    }
                    else if($_REQUEST["webservice"]=="getchallenge")
                    {
                        if($pk=="webservice" || $pk=="accessKey" || $pk=="loginname") continue;
                    }
                    else
                    {
                        if($pk=="element" || $pk=="webservice" || $pk=="sessionName" || $pk=="elementType") continue;
                    }
                    $_GET[$pk]=$pv;
                }
            }
        }
        if($homepage===false) $homepage=  getIndexPage();
        parent::__construct($controlpanel,$homepage);  
        if(get_magic_quotes_gpc())
        {
            if(isset($_REQUEST)) $_REQUEST = $this->sanitize($_REQUEST,false);
            if(isset($_GET)) $_GET = $this->sanitize($_GET,false);
            if(isset($_POST)) $_POST =$this->sanitize($_POST,false);
            if(isset($_COOKIE))	$_COOKIE = $this->sanitize($_COOKIE,false);
            if(isset(ClsConfig::$AUTO_SESSION) && ClsConfig::$AUTO_SESSION!==false) 
            {
                if(isset($_SESSION)) $_SESSION = $this->sanitize($_SESSION,false);
            }
            if(isset($_FILES)) $_FILES = $this->sanitize($_FILES,false);
            if(isset($_SERVER)) $_SERVER = $this->sanitize($_SERVER,false);
        }
        else
        {
            if(isset($_REQUEST)) $_REQUEST = $this->sanitize($_REQUEST,true);
            if(isset($_GET)) $_GET = $this->sanitize($_GET,true);
            if(isset($_POST)) $_POST =$this->sanitize($_POST,true);
            if(isset($_COOKIE))	$_COOKIE = $this->sanitize($_COOKIE,true);
            if(class_exists("ClsConfig") && isset(ClsConfig::$AUTO_SESSION) && ClsConfig::$AUTO_SESSION!==false) 
            {
                if(isset($_SESSION)) $_SESSION = $this->sanitize($_SESSION,true);
            }
            if(isset($_FILES)) $_FILES = $this->sanitize($_FILES,true);
            if(isset($_SERVER)) $_SERVER = $this->sanitize($_SERVER,false);
        }
        if(isset($_SERVER["PATH_INFO"]) && !empty($_SERVER["PATH_INFO"]))
        {
            $arrPathInfo=explode("/",$_SERVER["PATH_INFO"]);
            array_shift($arrPathInfo);
            if(!empty($arrPathInfo))
            {
                $_REQUEST["module"]=array_shift($arrPathInfo);
                $_GET["module"]=$_REQUEST["module"];
            }
            if(!empty($arrPathInfo))
            {
                $_REQUEST["action"]=array_shift($arrPathInfo);
                $_GET["action"]=$_REQUEST["action"];
            }
            if(!empty($arrPathInfo))
            {
                $_REQUEST["switch"]=array_shift($arrPathInfo);
                $_GET["switch"]=$_REQUEST["switch"];
            }
            while(!empty($arrPathInfo))
            {
                $_REQUEST["AUIEO_ACTION_PARAM_DATA"][]=array_shift($arrPathInfo);
                $_GET["AUIEO_ACTION_PARAM_DATA"][]=$_REQUEST["AUIEO_ACTION_PARAM_DATA"];
            }
        }
        $autoSession=getConfigVar("AUTO_SESSION");
        if(!($autoSession===false))
        {
            $this->objNaanalSession=ClsNaanalSession::getInstance();
        }
        $this->objNaanalRequest=  ClsNaanalRequest::getInstance();
        $this->objNaanalPost=ClsNaanalPost::getInstance();
        $this->objNaanalGet=ClsNaanalGet::getInstance();

        $this->files=$_FILES;
        $this->page=$this->objNaanalRequest->getModule();
        $this->module=$this->objNaanalRequest->getModule();
        $this->action=$this->objNaanalRequest->getAction();
        $this->switch=$this->objNaanalRequest->getSwitch();
        if(class_exists("ClsConfig"))
        {
            $this->fldUserID=isset(ClsConfig::$FLD_USERID)?ClsConfig::$FLD_USERID:"userid";
            $this->fldUser=isset(ClsConfig::$FLD_USER)?ClsConfig::$FLD_USER:"lastname";
            $this->fldPassword=isset(ClsConfig::$FLD_PASSWORD)?ClsConfig::$FLD_PASSWORD:"password";
        }
        
        $this->encryptURL=$encryptURL;
        if($this->encryptURL && isset($_REQUEST["@page"]))
        {
            $shld=new URLShield(true,true);
            $shld->expose();
        }
    }
    
    
	
    private function sanitize($input_data, $sanitize = true)
    {
        if(is_array($input_data))
        {
            $output_data=null;
            foreach($input_data as $input_key=>$input_value)
            {
                if($input_key=="rand")
                {
                    $output_data[$input_key] = $input_value;
                }
                else
                {
                    $output_data[$input_key] = $this->sanitize($input_value,$sanitize);
                }
            }
            return $output_data;
        }
        else if($sanitize)
        {
            if(is_string($input_data)) 
            {
                return $input_data;
            }
            else return $input_data;
        }
        else
        {
            return $input_data;
        }
    }


    /**
     * @param string $from [e-mail do remetente]
     * @return boolean [false = em ataque; true = seguro)
     */
    public function antiMailInjection($from) {

        $from = urldecode($from);
        
        if (eregi("(\r|\n)", $from)) {
            
            return false;
            
        } else {
            
            return true;
            
        }

    }
    
    /**
     * O array $output conterï¿½ as seguintes transformaï¿½ï¿½es para HTML Entities:
     * De 	Para 
     * < 	&lt; 
     * > 	&gt; 
     * ( 	&#40; 
     * ) 	&#41; 
     * # 	&#35; 
     * & 	&#38; 
     *
     * @param string $key
     * @param string $preOutput
     * @param string $charset [default = UTF-8]
     * @return array [vetor com a saï¿½da tratada, mapeado pelas chaves fornecidas]
     */
    public function antiXssInjection($key, $preOutput, $charset = 'UTF-8') {
        
        $key = (string) $key;
        
        $this->output[$key] = htmlentities($preOutput, ENT_QUOTES, $charset);
        
    }
    
    /**
     * @return array
     */
    public function getOutput() {
        
        return $this->output;
        
    }
    
    /**
     * @param string $getSection [seï¿½ï¿½o referenciada na URL]
     * @param array $sections [lista branca com as seï¿½ï¿½es permitidas]
     * @param string $defaultCleanSection [seï¿½ï¿½o default = 'home']
     * @return string [seï¿½ï¿½o validada]
     */
    public function antiRemoteCodeInjection($getSection, $sections, $defaultCleanSection = 'home') {
        
        if (in_array($getSection, $sections)) {
            
            $cleanSection = $getSection;
        	
        } else {
            
            $cleanSection = $defaultCleanSection;
            
        }
        
        return $cleanSection;
        
    }
    
    /**
     * @param boolean $tokenGeneration [true = gerar token; false = veerificar token]
     * @return mixed [na geraï¿½ï¿½o retorna o token em si; na verificaï¿½ï¿½o, um booleano]
     */
    public function antiXsrfInjection($tokenGeneration = true) {
        
        if ($tokenGeneration) {
        	
            if (session_id() == "") {
            	
                session_start();
                
            }
            
            $token = md5(uniqid(rand(), true));
            $_SESSION['token'] = $token;
            
            return $token;
            
        } else {
            
            if (isset($_SESSION['token']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {
            	
                return true;
            
            } else {
                
                return false;
                
            }
            
        }
        
    }

}

class ClsNaanalFiles
{
    private $arrNaanalFiles="";
    private $field="";
    public function __construct($field)
    {
        $this->field=$field;
        $this->arrNaanalFiles=isset($_FILES)?$_FILES:array();
        $_FILES=array();
    }
    public static function &getInstance($field)
    {
        static $objNaanalFiles=null;
        if(is_null($objNaanalFiles))
        {
            $objNaanalFiles=new ClsNaanalFiles($field);
        }
        return $objNaanalFiles;
    }
    public function getData($name)
    {
        return $this->arrNaanalFiles[$this->field][$name];
    }
    public function getAll()
    {
        return $this->arrNaanalFiles;
    }
    public function render()
    {
        $_POST=$this->arrNaanalFiles;
    }
}

class ClsNaanalSession
{
    private $name="naanal";
    private $arrNaanalSession=array();
    private $naanalPanel="guest";
    public $previousWebPath="";
    private $currentLoginID=0;
    private $currentLoginName="guest";
    private static $objNaanalSession=null;
    public function __construct()
    {
        if(class_exists("ClsConfig") && isset(ClsConfig::$SESSION_NAME))
        {
            $this->name=ClsConfig::$SESSION_NAME;
        }
        else
        {
            $this->name="naanal";
        }//trace($_SESSION);
        $this->arrNaanalSession[$this->name]=isset($_SESSION[$this->name])?$_SESSION[$this->name]:array();
        $this->arrNaanalSession[$this->name]["loginpanel"]=isset($this->arrNaanalSession[$this->name]["loginpanel"])?$this->arrNaanalSession[$this->name]["loginpanel"]:"guest";
        $_SESSION[$this->name]["loginpanel"]=$this->arrNaanalSession[$this->name]["loginpanel"];
        $this->naanalPanel=$this->arrNaanalSession[$this->name]["loginpanel"];
        if($this->naanalPanel!="guest") 
        {
            if(isset($this->arrNaanalSession[$this->name][$this->naanalPanel]["loginid"]))
            {
                $this->currentLoginID=$this->arrNaanalSession[$this->name][$this->naanalPanel]["loginid"];
            }
            else
            {
                unset($this->arrNaanalSession[$this->name]);
                unset($_SESSION[$this->name]);
                $homepage=  getIndexPage();
                header("Location:{$homepage}");exit;
            }
        }
        if($this->naanalPanel!="guest") $this->currentLoginName=$this->arrNaanalSession[$this->name][$this->naanalPanel]["loginname"];
        /**
        * restore the error message from session
        */
        if(isset($this->arrNaanalSession[$this->name]["err"]))
        {
            errMsg($this->arrNaanalSession[$this->name]["err"]);
            unset($this->arrNaanalSession[$this->name]["err"]);
            unset($_SESSION[$this->name]["err"]);
        }
    }
    public static function &getInstance()
    {
        if(empty(self::$objNaanalSession))
        {
            self::$objNaanalSession=new ClsNaanalSession();
        }
        return self::$objNaanalSession;
    }
    public static function setInstance(&$instance)
    {
        self::$objNaanalSession=$instance;
    }
    public function setGlobal($name,$data,$module=false,$action=false,$switch=false)
    {
        $this->arrNaanalSession[$this->name][$this->naanalPanel]["global"][]=array("name"=>$name,"data"=>$data,"module"=>$module,"action"=>$action,"switch"=>$switch);
        $_SESSION[$this->name][$this->naanalPanel]["global"][]=array("name"=>$name,"data"=>$data,"module"=>$module,"action"=>$action,"switch"=>$switch);
    }
    public function getGlobal($module=false,$action=false,$switch=false)
    {
        $arrVariable=array();
        foreach($this->arrNaanalSession[$this->name][$this->naanalPanel]["global"] as $arrGlobalData)
        {
            if($module===$arrGlobalData["module"] && $action===$arrGlobalData["action"] && $switch===$arrGlobalData["switch"])
            {
                $arrVariable[$arrGlobalData["name"]]=$arrGlobalData["data"];
            }
        }
        return $arrVariable;
    }
    public function getLoginPanel()
    {
        return isset($this->arrNaanalSession[$this->name]["loginpanel"])?$this->arrNaanalSession[$this->name]["loginpanel"]:"guest";
    }
    public function getLoginID()
    {
        return $this->currentLoginID;
    }
    public function getLoginName()
    {
        return $this->currentLoginName;
    }
    public function setData($name,$value)
    {
        if($name=="loginpanel")
        {
            if($this->naanalPanel!="guest") return false;
            $this->naanalPanel=$value;
        }
        $this->arrNaanalSession[$this->name][$name]=$value;
        $_SESSION[$this->name][$name]=$value;
    }
    private function setVisitedURL()
    {
        if(empty($this->name)) return false;
        $isLastURLSame=false;
        $currentURL=getCurrentURL();
        if(!isset($this->arrNaanalSession[$this->name]["VISITED_URL"]["page"]))
        {
            $this->arrNaanalSession[$this->name]["VISITED_URL"]["page"]=array();
            $this->arrNaanalSession[$this->name]["VISITED_URL"]["refresh"]=0;
            $_SESSION[$this->name]["VISITED_URL"]["page"]=$this->arrNaanalSession[$this->name]["VISITED_URL"]["page"];
            $_SESSION[$this->name]["VISITED_URL"]["refresh"]=$this->arrNaanalSession[$this->name]["VISITED_URL"]["refresh"];
        }
        else
        {
            $count=count($this->arrNaanalSession[$this->name]["VISITED_URL"]["page"]);
            $lastURL=$this->arrNaanalSession[$this->name]["VISITED_URL"]["page"][$count-1];
            if($lastURL==$currentURL)
            {
                $this->arrNaanalSession[$this->name]["VISITED_URL"]["refresh"]=$this->arrNaanalSession[$this->name]["VISITED_URL"]["refresh"]+1;
                $isLastURLSame=true;
            }
        }
        if(!$isLastURLSame) 
        {
            $this->arrNaanalSession[$this->name]["VISITED_URL"]["page"][]=$currentURL;
        }
        $_SESSION[$this->name]["VISITED_URL"]["page"]=$this->arrNaanalSession[$this->name]["VISITED_URL"]["page"];
        $_SESSION[$this->name]["VISITED_URL"]["refresh"]=$this->arrNaanalSession[$this->name]["VISITED_URL"]["refresh"];
    }
    public function setStorageData($name,$value,$module=false,$action=false,$switch=false,$id=0)
    {
        if($module===false && $action===false && $switch===false && $id===0)
        {
            $this->arrNaanalSession[$this->name][$this->naanalPanel]["AUIEO_APP_STORAGE"][$name]=$value;
            $_SESSION[$this->name][$this->naanalPanel]["AUIEO_APP_STORAGE"][$name]=$value;
        }
        else
        {
            if($action===false) $action="NO_ACTION";
            if($switch===false) $switch="NO_SWITCH";
            if($id===0) $action="NO_ID";
            $this->arrNaanalSession[$this->name][$this->naanalPanel]["AUIEO_MOD_STORAGE"][$module][$action][$switch][$name]=$value;
            $_SESSION[$this->name][$this->naanalPanel]["AUIEO_MOD_STORAGE"][$module][$action][$switch][$name]=$value;
        }
        return true;
    }
    public function getStorageData($name,$module=false,$action=false,$switch=false,$id=0)
    {
        if($module===false && $action===false && $switch===false && $id===0)
        {
            if(isset($this->arrNaanalSession[$this->name][$this->naanalPanel]["AUIEO_APP_STORAGE"][$name]))
                return $this->arrNaanalSession[$this->name][$this->naanalPanel]["AUIEO_APP_STORAGE"][$name];
            else
                return null;
        }
        else if($action===false && $switch===false && $id===0)
        {
            if($action===false) $action="NO_ACTION";
            if($switch===false) $switch="NO_SWITCH";
            if($id===0) $action="NO_ID";
            if(isset($this->arrNaanalSession[$this->name][$this->naanalPanel]["AUIEO_MOD_STORAGE"][$module][$action][$switch][$name]))
                return $this->arrNaanalSession[$this->name][$this->naanalPanel]["AUIEO_MOD_STORAGE"][$module][$action][$switch][$name];
            else
                return null;
        }
        return true;
    }
    public function setPanelData($name,$value)
    {
        if($name=="loginname")
        {
            if($this->currentLoginName!="guest") return false;
            $this->currentLoginName=$value;
        }
        else if($name=="loginid")
        {
            if($this->currentLoginID>0) return false;
            $this->currentLoginID=$value;
        }
        else if($name=="webpath")
        {
            $previousWebPath=$this->getWebPath();
            $this->previousWebPath=$previousWebPath;
            $_SESSION[$this->name]["previousWebPath"]=$this->previousWebPath;
        }
        $this->arrNaanalSession[$this->name][$this->naanalPanel][$name]=$value;
        $_SESSION[$this->name][$this->naanalPanel][$name]=$value;
        return true;
    }
    public function getData($name)
    {
        return isset($this->arrNaanalSession[$this->name][$name])?$this->arrNaanalSession[$this->name][$name]:false;
    }
    public function getPanelData($name)
    {
        return isset($this->arrNaanalSession[$this->name][$this->naanalPanel][$name])?$this->arrNaanalSession[$this->name][$this->naanalPanel][$name]:false;
    }
    public function getUserInfo($name)
    {
        static $AUIEO_USER_INFO=array();
        if(empty($AUIEO_USER_INFO))
        {
            $AUIEO_USER_INFO=$this->getPanelData("AUIEO_USER_INFO");
        }
        if(isset($AUIEO_USER_INFO[$name])) return $AUIEO_USER_INFO[$name];
        return null;
    }
    public function &getDataByRef($name)
    {
        return $this->arrNaanalSession[$this->name][$name];
    }
    public function getAll()
    {
        return $this->arrNaanalSession[$this->name];
    }
    public function isAdmin()
    {
        return $this->getPanelData("isadmin");
    }
    public function isDataSet($name)
    {
        return isset($this->arrNaanalSession[$this->name][$name])?true:false;
    }
    public function isDataSetNotEmpty($name)
    {
        if(!isset($this->arrNaanalSession[$this->name][$name])) return false;
        if(empty($this->arrNaanalSession[$this->name][$name])) return false;
        return true;
    }
    public function isPanelDataSet($name)
    {
        return isset($this->arrNaanalSession[$this->name][$this->naanalPanel][$name])?true:false;
    }
    public function isPanelDataSetNotEmpty($name)
    {
        if(!isset($this->arrNaanalSession[$this->name][$this->naanalPanel][$name])) return false;
        if(empty($this->arrNaanalSession[$this->name][$this->naanalPanel][$name])) return false;
        return true;
    }
    public function deleteData($name)
    {
        unset($this->arrNaanalSession[$this->name][$name]);
        unset($_SESSION[$this->name][$name]);
    }
    public function deletePanelData($name)
    {
        unset($this->arrNaanalSession[$this->name][$this->naanalPanel][$name]);
        unset($_SESSION[$this->name][$this->naanalPanel][$name]);
    }
    public function getAppPath()
    {
        return isset($this->arrNaanalSession[$this->name][$this->naanalPanel]["apppath"])?$this->arrNaanalSession[$this->name][$this->naanalPanel]["apppath"]:null;
    }
    public function getWebPath()
    {
        return isset($this->arrNaanalSession[$this->name][$this->naanalPanel]["webpath"])?$this->arrNaanalSession[$this->name][$this->naanalPanel]["webpath"]:null;
    }
    public function logout()
    {
        //$this->name="naanal";
        $this->arrNaanalSession=array();
        $this->naanalPanel="guest";
        $this->currentLoginID=0;
        $this->currentLoginName="guest";
        unset($_SESSION[$this->name]);
    }
    public function getLastURL()
    {
        if(isset($this->arrNaanalSession[$this->name]["VISITED_URL"]))
        {
            $count=count($this->arrNaanalSession[$this->name]["VISITED_URL"]);
            return $this->arrNaanalSession[$this->name]["VISITED_URL"]["page"][$count-1];
        }
        else
        {
            return "";
        }
    }
    public function render()
    {
        $this->setVisitedURL();
    }
}
class ClsNaanalRequest extends ClsNaanalInputBase
{
    protected $arrNaanalRequest=array();
    protected $id=0;
    protected $module="home";
    protected $action="create";
    protected $switch="default";
    protected $auieoActionParam=array();
    protected $entry=null;
    protected $formodule="";
    
    public function __construct($_AUIEO_INPUT_VALUE_ARRAY=false)
    {
        if($_AUIEO_INPUT_VALUE_ARRAY===false)
        {
            $this->arrNaanalRequest=isset($_REQUEST)?$_REQUEST:array();
        }
        else
        {
            $this->arrNaanalRequest=$_AUIEO_INPUT_VALUE_ARRAY;
        }
        parent::__construct($this->arrNaanalRequest);
        $this->processModuleInput($this->arrNaanalRequest,"request");
        if(!is_null($this->entry))
        {
            if(!isset($_REQUEST["entry"]))
            {
                $this->arrNaanalRequest["entry"]=$this->entry;
                $_REQUEST["entry"]=$this->entry;
            }
        }
        if(isset($this->arrNaanalRequest["id"])) $this->id=$this->arrNaanalRequest["id"];
    }
    public static function &getInstance($_AUIEO_INPUT_VALUE_ARRAY=array())
    {
        static $objNaanalRequest=null;
        if(empty($_AUIEO_INPUT_VALUE_ARRAY))
        {
            if(is_null($objNaanalRequest))
            {
                $objNaanalRequest=new ClsNaanalRequest();
            }
            return $objNaanalRequest;
        }
        else
        {
            $objNaanalRequest=new ClsNaanalRequest($_AUIEO_INPUT_VALUE_ARRAY);
        }
    }
    public function setModule($module)
    {
        $this->module=$module;
        $this->arrNaanalRequest[$this->urlModuleParam]=$module;
    }
    public function setAction($action)
    {
        $this->action=$action;
        $this->arrNaanalRequest[$this->urlActionParam]=$action;
    }
    public function setSwitch($switch)
    {
        $this->switch=$switch;
        $this->arrNaanalRequest[$this->urlSwitchParam]=$switch;
    }
    public function setData($name,$value)
    {
        $this->arrNaanalRequest[$name]=$value;
        if($name==$this->urlModuleParam)
        {
            $this->module=$value;
        }
        else if($name==$this->urlActionParam)
        {
            $this->action=$value;
        }
        else if($name==$this->urlSwitchParam)
        {
            $this->switch=$value;
        }
    }
    public function getData($name)
    {
        return isset($this->arrNaanalRequest[$name])?$this->arrNaanalRequest[$name]:null;
    }
    public function &getDataByRef($name)
    {
        return $this->arrNaanalRequest[$name];
    }
    public function getModule()
    {
        return $this->module;
    }
    public function getAction()
    {
        return $this->action;
    }
    public function getSwitch()
    {
        return $this->switch;
    }
    public function getForModule()
    {
        return $this->formodule;
    }
    public function getID()
    {
        return $this->id;
    }
    public function getAll()
    {
        return $this->arrNaanalRequest;
    }
    public function isDataSet($name)
    {
        return isset($this->arrNaanalRequest[$name])?true:false;
    }
    public function isDataSetNotEmpty($name)
    {
        if(!isset($this->arrNaanalRequest[$name])) return false;
        if(empty($this->arrNaanalRequest[$name])) return false;
        return true;
    }
    public function deleteData($name)
    {
        unset($this->arrNaanalRequest[$name]);
        unset($_REQUEST[$name]);
    }
    public function render()
    {
        $_REQUEST=$this->arrNaanalRequest;
    }
}
class ClsNaanalPost extends ClsNaanalInputBase
{
    protected $arrNaanalPost=array();
    protected $id=0;
    protected $module="home";
    protected $action="create";
    protected $switch="default";
    protected $entry=null;
    protected $formodule="";
    public function __construct()
    {
        $this->arrNaanalPost=isset($_POST)?$_POST:array();
        parent::__construct($this->arrNaanalPost);
        $this->processModuleInput($_POST,"post");
        if(!is_null($this->entry))
        {
            if(!isset($_POST["entry"]))
            {
                $this->arrNaanalPost["entry"]=$this->entry;
                $_POST["entry"]=$this->entry;
            }
        }
        $_POST=array();
        if(isset($this->arrNaanalPost["id"])) $this->id=$this->arrNaanalPost["id"];
    }
    public static function &getInstance()
    {
        static $objNaanalPost=null;
        if(empty($objNaanalPost))
        {
            $objNaanalPost=new ClsNaanalPost();
        }
        return $objNaanalPost;
    }
    public function setData($name,$value)
    {
        $this->arrNaanalPost[$name]=$value;
        if($this->isDataSet($name))
        {
            ClsNaanalRequest::getInstance()->setData($name, $value);
        }
        if($name==$this->urlModuleParam)
        {
            $this->module=$value;
        }
        else if($name==$this->urlActionParam)
        {
            $this->action=$value;
        }
        else if($name==$this->urlSwitchParam)
        {
            $this->switch=$value;
        }
    }
    public function getData($name)
    {
        return isset($this->arrNaanalPost[$name])?$this->arrNaanalPost[$name]:null;
    }
    public function getModule()
    {
        return $this->module;
    }
    public function getAction()
    {
        return $this->action;
    }
    public function getSwitch()
    {
        return $this->switch;
    }
    public function getForModule()
    {
        return $this->formodule;
    }
    public function getID()
    {
        return $this->id;
    }
    public function &getDataByRef($name)
    {
        return $this->arrNaanalPost[$name];
    }
    public function getAll()
    {
        return $this->arrNaanalPost;
    }
    public function isDataSet($name)
    {
        return isset($this->arrNaanalPost[$name])?true:false;
    }
    public function isDataSetNotEmpty($name)
    {
        if(!isset($this->arrNaanalPost[$name])) return false;
        if(empty($this->arrNaanalPost[$name])) return false;
        return true;
    }
    public function deleteData($name)
    {
        unset($this->arrNaanalPost[$name]);
        unset($_POST[$name]);
    }
    public function render()
    {
        $_POST=$this->arrNaanalPost;
    }
}

class ClsNaanalGet extends ClsNaanalInputBase
{
    protected $arrNaanalGet=array();
    protected $id=0;
    protected $module="home";
    protected $action="create";
    protected $switch="default";
    protected $auieoActionParam=array();
    protected $entry=null;
    protected $formodule="";
    public function __construct()
    {
        $this->arrNaanalGet=isset($_GET)?$_GET:array();
        parent::__construct($this->arrNaanalGet);
        $this->processModuleInput($_GET,"get");
        if(!is_null($this->entry))
        {
            if(!isset($_GET["entry"]))
            {
                $this->arrNaanalGet["entry"]=$this->entry;
                $_GET["entry"]=$this->entry;
            }
        }
        $_GET=array();
        if(isset($this->arrNaanalGet["id"])) $this->id=$this->arrNaanalGet["id"];
    }
    public static function &getInstance()
    {
        static $objNaanalGet=null;
        if(empty($objNaanalGet))
        {
            $objNaanalGet=new ClsNaanalGet();
        }
        return $objNaanalGet;
    }
    public function setData($name,$value)
    {
        $this->arrNaanalGet[$name]=$value;
        if($this->isDataSet($name))
        {
            ClsNaanalRequest::getInstance()->setData($name, $value);
        }
        if($name==$this->urlModuleParam)
        {
            $this->module=$value;
        }
        else if($name==$this->urlActionParam)
        {
            $this->action=$value;
        }
        else if($name==$this->urlSwitchParam)
        {
            $this->switch=$value;
        }
    }
    public function getData($name)
    {
        return isset($this->arrNaanalGet[$name])?$this->arrNaanalGet[$name]:null;
    }
    public function getModule()
    {
        return $this->module;
    }
    public function getAction()
    {
        return $this->action;
    }
    public function getSwitch()
    {
        return $this->switch;
    }
    public function getForModule()
    {
        return $this->formodule;
    }
    public function getID()
    {
        return $this->id;
    }
    public function &getDataByRef($name)
    {
        return $this->arrNaanalGet[$name];
    }
    public function getAll()
    {
        return $this->arrNaanalGet;
    }
    public function isDataSet($name)
    {
        return isset($this->arrNaanalGet[$name])?true:false;
    }
    public function isDataSetNotEmpty($name)
    {
        if(!isset($this->arrNaanalGet[$name])) return false;
        if(empty($this->arrNaanalGet[$name])) return false;
        return true;
    }
    public function deleteData($name)
    {
        unset($this->arrNaanalGet[$name]);
        unset($_GET[$name]);
    }
    public function render()
    {
        $_GET=$this->arrNaanalGet;
    }
}
class ClsNaanalInputBase
{
    protected $urlModuleParam="page";
    protected $urlActionParam="action";
    protected $urlSwitchParam="switch";
    protected $arrNaanalInput=array();
    protected $isSubmit=false;
    protected $otherVar=array();
    protected $isWebservice=false;
    
    protected function __construct(&$arrInput)
    {
        $this->arrNaanalInput=$arrInput;
        if(isset($arrInput["webservice"]))
        {
            $this->isWebservice=true;
        }
        if(class_exists("ClsConfig") && isset(ClsConfig::$CONTROL_PANEL))
        {
            //$loginControlPanel=getLoginControlPanel();
            if(AUIEO_CONTROL_PANEL=="index") $loginControlPanel = "user";
            else $loginControlPanel = AUIEO_CONTROL_PANEL;
            if(isset(ClsConfig::$CONTROL_PANEL[$loginControlPanel]["page"]["home"]["module"]))
            {
                $this->module=ClsConfig::$CONTROL_PANEL[$loginControlPanel]["page"]["home"]["module"];
                $this->action=ClsConfig::$CONTROL_PANEL[$loginControlPanel]["page"]["home"]["action"];
            }
        }
        if(isset($arrInput["AUIEO_ACTION_PARAM_DATA"]))
        {
            $this->auieoActionParam=$arrInput["AUIEO_ACTION_PARAM_DATA"];
        }
    }
    public function getAuieoActionParam()
    {
        return $this->auieoActionParam;
    }
    public function isWebservice()
    {
        return $this->isWebservice;
    }
    /**
     * load object with matching field by calling setter method
     * the methods can be set_{field})($value) or set($field,$value)
     */
    public function loadObject(&$object)
    {
        $arrVar=get_object_vars($object);
        foreach($arrVar as $var)
        {
            $setvar="set_{$var}";
            if(method_exists($object, $setvar))
            {
                $object->$setvar($this->arrNaanalInput[$var]);
            }
            else if(method_exists($object, "set"))
            {
                $object->set($var,$this->arrNaanalInput[$var]);
            }
        }
        return $object;
    }
    public  function isSubmit()
    {
        return $this->isSubmit;
    }
    protected function processModuleInput(&$arrInputParam,$type="get")
    {
        $retModule="";
        $retAction="";
        $retSwitch="";
        
        if(isset($arrInputParam["id"]))
        {
            $this->id=$arrInputParam["id"];
        }
        if(file_exists("ClsConfig.php") && isset(ClsConfig::$URL_MODULE_PARAM))
        {
            $this->urlModuleParam=ClsConfig::$URL_MODULE_PARAM;
        }
        if(file_exists("ClsConfig.php") && isset(ClsConfig::$URL_ACTION_PARAM))
        {
            $this->urlActionParam=ClsConfig::$URL_ACTION_PARAM;
        }
        if(file_exists("ClsConfig.php") && isset(ClsConfig::$URL_SWITCH_PARAM))
        {
            $this->urlSwitchParam=ClsConfig::$URL_SWITCH_PARAM;
        }
        if(isset($arrInputParam["rand"]))
        {
            $arrDecode=getModuleDecode($arrInputParam["rand"]);
            if(isset($arrDecode[$this->urlModuleParam]))
            {
                $this->module=$arrDecode[$this->urlModuleParam];
            }
            if(isset($arrDecode[$this->urlActionParam]))
            {
                $this->action=$arrDecode[$this->urlActionParam];
            }
            if(isset($arrDecode["switch"]))
            {
                $this->switch=$arrDecode[$this->urlSwitchParam];
            }
            $this->isSubmit=true;
        }
        if(isset($arrInputParam[$this->urlModuleParam]))
        {
            $this->module=$arrInputParam[$this->urlModuleParam];
        }
        else
        {
        }
        $urlActionParam=$this->urlActionParam;
        $arrConfigVar=getModuleConfigVars($this->module);
        if(isset($arrConfigVar["url_action_param"]))
        {
            $urlActionParam=$arrConfigVar["url_action_param"];
        }
        if(isset($arrInputParam[$urlActionParam]))
        {
            $this->action=$arrInputParam[$urlActionParam];
        }
        else
        {
            if($this->id>0)
            {
                if(isset($arrInputParam["issubmit"]))
                {
                    $this->action="update";
                }
                else
                {
                    $this->action="edit";
                }
            }
            else
            {
                if(isset($arrInputParam["issubmit"]))
                {
                    $this->action="insert";
                }
                else
                {
                    //$this->action="create";

                }
            }
        }
        if(isset($arrInputParam["switch"]))
        {
            $this->switch=$arrInputParam["switch"];
        }
        else
        {
            $this->switch="default";
        }
        
        $arrTrimParam=array("page","action","formodule");
        foreach($arrTrimParam as $data)
        {
            if(isset($arrInputParam[$data]))
            {
                if($data==$this->urlModuleParam || $data==$this->urlActionParam || $data==$this->urlSwitchParam) continue;
                $arrInputParam[$data]=trim($arrInputParam[$data]);
                $this->otherVar[$data]=$arrInputParam[$data];
            }
        }
        $requested_entry=_AuieoHook("{$type}_entry");
        if($requested_entry)
        {
            $entry=$requested_entry();
            if(!is_null($entry))
            {
                $this->entry=$entry;
            }
        }
        
        $requested_page=_AuieoHook("{$type}_page");
        if($requested_page)
        {
            $module=$requested_page();
            if(!is_null($module))
            {
                $this->module=$module;
            }
        }

        $requested_action=_AuieoHook("{$type}_action");
        if($requested_action)
        {
            $action=$requested_action();
            if(!is_null($action))
            {
                $this->action=$action;
            }
        }
        $requested_switch=_AuieoHook("{$type}_switch");
        if($requested_switch)
        {
            $switch=$requested_switch();
            if(!is_null($switch))
            {
                $this->switch=$switch;
            }
        }
        
        /**
         * If framework or application not installed, the user hook will be overridden
         */
        if(!isFrameworkInstalled() || !isApplicationInstalled())
        {
            $this->module="install";
        }
    }
    public function getPageVar()
    {
        return isset(ClsConfig::$PAGINATION_VAR)?ClsConfig::$PAGINATION_VAR:"pv";
    }
    public function getPager()
    {
        static $pager=null;
        if(is_null($pager))
        {
            $itemPerPage=isset(ClsConfig::$PAGINATION_ITEM_PER_PAGE)?ClsConfig::$PAGINATION_ITEM_PER_PAGE:10;
            $pagevar=$this->getPageVar();
            $curpage=$this->getData($pagevar);
            $curpage=empty($curpage)?1:$curpage;
            $start=($curpage-1)*$itemPerPage;
            $pager=array("current_page"=>$curpage,"start"=>$start,"items_per_page"=>$itemPerPage,"page_var"=>$pagevar);
        }
        return $pager;
    }
    public function getUrlModuleParam()
    {
        return $this->urlModuleParam;
    }
    public function getUrlActionParam()
    {
        return $this->urlActionParam;
    }
    public function getUrlSwitchParam()
    {
        return $this->urlSwitchParam;
    }
}
?>