<?php
include_once __DIR__."/PRGUtility.php";
//include_once __DIR__."/PRGUser.php";
class PRGManagement
{
    private $objUser=null;
    private $permission=null;
    public function __construct() 
    {
        $this->objUser=Users::getInstance();
        if($this->objUser->getUserInfo("user_id")>0)
        {
            $this->permission=$this->objUser->getPermission();
        }
    }
    public static function getInstance($isRefresh=false)
    {
        static $objManagement=null;
        if(is_null($objManagement) || $isRefresh)
        {
            $objManagement=new PRGManagement();
        }
        return $objManagement;
    }
    public function isModulePermitted($module=false)
    {
        if($module===false && isset($_REQUEST["m"]))
        {
            $module=$_REQUEST["m"];
        }
        /**
         * if module not set, it is home module. It is allowed
         */
        if($module===false) return true;
        $data_item_type=0;
        $actionMapping=array();
        
        $arrModuleInfo=getModuleInfo("modulename");
        if(isset($arrModuleInfo[$module]))
        {
            if($module=="joborders")
            {
                $reqModuleInfo=$arrModuleInfo["joborders"];
                $libModuleName=  "JobOrders";
                include_once("lib/{$libModuleName}.php");
                $data_item_type=$reqModuleInfo["data_item_type_id"];
                $actionMapping=  $libModuleName::actionMapping();
            }
            else
            {
                $reqModuleInfo=$arrModuleInfo[$module];
                $libModuleName=  ucfirst($module);
                include_once("lib/{$libModuleName}.php");
                $data_item_type=$reqModuleInfo["data_item_type_id"];
                $actionMapping=  $libModuleName::actionMapping();
            }
        }
        /*switch ($module)
        {
            case 'candidates':
            {
                include_once("lib/Candidates.php");
                $data_item_type=100;
                $actionMapping=  Candidates::actionMapping();
                break;
            }
            case 'companies':
            {
                include_once("lib/Companies.php");
                $data_item_type=200;
                $actionMapping= Companies::actionMapping();
                break;
            }
            case 'contacts':
            {
                include_once("lib/Contacts.php");
                $data_item_type=300;
                $actionMapping= Contacts::actionMapping();
                break;
            }
            case 'joborders':
            {
                include_once("lib/JobOrders.php");
                $data_item_type=400;
                $actionMapping= JobOrders::actionMapping();
                break;
            }
        }*/
        /**
         * if $data_item_type is 0, it indicates other modules. so it is allowed
         */
        if($data_item_type<=0) return true;
        //insert into auieo_profiles2permissions (`profileid`,`data_item_type`,`operation`,`permissions`,`site_id`) values ('3','400','4','0','180');
        /*$permissions = array(
            "listByView"=>4,
            "add"=>0,
            "edit"=>1,
            "show"=>2,
            "delete"=>3,
            "default"=>"listByView"
        );*/
        if(!isset($this->permission[$data_item_type])) return false;
        $modulePermission=$this->permission[$data_item_type];
        /**
         * checks whether any one operation is allowed
         */
        $isModulePermited=false;
        if($modulePermission)
        {
            foreach($modulePermission as $operation=>$permission)
            {
                if($permission>0) 
                {
                    return true;
                }
            }
        }
        /**
         * since all the operation is not allowed, don't allow
         */
        return false;
    }
    public function isModuleActionPermitted($module=false,$action=false)
    {
        if($module===false && isset($_REQUEST["m"]))
        {
            $module=$_REQUEST["m"];
        }
        if($action===false && isset($_REQUEST["a"]))
        {
            $action=$_REQUEST["a"];
        }
        /**
         * if module not set, it is home module. It is allowed
         */
        if($module===false) return true;
        $data_item_type=0;
        $actionMapping=array();
        switch ($module)
        {
            case 'candidates':
            {
                $data_item_type=100;
                $actionMapping=  Candidates::actionMapping();
                break;
            }
            case 'companies':
            {
                $data_item_type=200;
                $actionMapping= Companies::actionMapping();
                break;
            }
            case 'contacts':
            {
                $data_item_type=300;
                $actionMapping= Contacts::actionMapping();
                break;
            }
            case 'joborders':
            {
                $data_item_type=400;
                $actionMapping= JobOrders::actionMapping();
                break;
            }
        }
        /**
         * if $data_item_type is 0, it indicates other modules. so it is allowed
         */
        if($data_item_type<=0) return true;
        $modulePermission=isset($this->permission[$data_item_type])?$this->permission[$data_item_type]:array();
        /**
         * checks whether any one operation is allowed
         */
        $isModulePermited=false;
        if($modulePermission)
        {
            foreach($modulePermission as $operation=>$permission)
            {
                if($permission>0) 
                {
                    $isModulePermited=true;
                    break;
                }
            }
        }
        /**
         * since all the operation is not allowed, don't allow
         */
        if($isModulePermited===false) return false;
        /**
         * if $action is false, check whether default action exist in action mapping
         */
        if($action===false)
        {
            if(isset($actionMapping["default"])) $action=$actionMapping["default"];
        }
        if(!isset($actionMapping[$action])) return true;
        $operation=$actionMapping[$action];
        /**
         * if the action allowed
         */
        if(isset($modulePermission[$operation]) && $modulePermission[$operation]>0) return true;
        return false;
    }
}
?>