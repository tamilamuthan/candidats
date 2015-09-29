<?php
class PRGRole
{
    private $id=0;
    private $name="";
    private $arrProfile=null;
    private $objPermission=null;
    private $arrRoleUser=array();
    private $arrParentRole=array();
    private $arrChildRole=array();
    public function __construct($roleid) {
        $db=  DatabaseConnection::getInstance();
        $site_id=$_SESSION["CATS"]->getSiteID();
        $query = "SELECT id,parentid,rolename as title FROM auieo_roles where id={$roleid} and site_id={$site_id}";
        $record = $db->getAssoc($query);
        if($record)
        {
            $this->name=$record["title"];
            $this->id=$roleid;
            
            /**
             * get all users belong to this role
             */
            $sql="select * from user where roleid={$this->id} and site_id={$site_id}";
            $arrUserRecord=$db->getAllAssoc();
            foreach($arrUserRecord as $ind=>$urecord)
            {
                $this->arrRoleUser[$urecord["user_id"]]=$urecord["user_name"];
            }
            
            $sql="select * from auieo_roles2profiles where roleid={$this->id} and site_id={$site_id}";
            $arrAssoc=$db->getAllAssoc($sql);
            if(!empty($arrAssoc))
            foreach($arrAssoc as $record)
            {
                $this->arrProfile[]=new PRGProfile($record["profileid"]);
            }
            $childid=$roleid;
            while($parentid=$this->getParent($childid))
            {
                $this->arrParentRole[]=$parentid;
                $childid=$parentid;
            }
            $parentid=$roleid;
            $this->processChildren($parentid);
        }
    }
    public function &getParentRoles()
    {
        return $this->arrParentRole;
    }
    public function &getChildrenRoles()
    {
        return $this->arrChildRole;
    }
    public function getRoleUsers()
    {
        return $this->arrRoleUser;
    }
    /**
     *
     * @staticvar array $arrRole
     * @param type $roleid
     * @param type $isRefresh - refreshes with new database data
     * @return \PRGRole
     */
    public static function getInstance($roleid,$isRefresh=false)
    {
        static $arrRole=array();
        if(!isset($arrRole[$roleid]) || $isRefresh)
        {
            $arrRole[$roleid]=new PRGRole($roleid);
        }
        return $arrRole[$roleid];
    }
    private function getParent($roleid)
    {
        $db=  DatabaseConnection::getInstance();
        $site_id=$_SESSION["CATS"]->getSiteID();
        $query = "SELECT id,parentid FROM auieo_roles where id={$roleid} and site_id={$site_id}";
        $record = $db->getAssoc($query);
        return $record["parentid"];
    }
    private function processChildren($roleid)
    {
        $db=  DatabaseConnection::getInstance();
        $site_id=$_SESSION["CATS"]->getSiteID();
        $query = "SELECT id,parentid FROM auieo_roles where parentid={$roleid} and site_id={$site_id}";
        $records = $db->getAllAssoc($query);
        foreach($records as $record)
        {
            $this->arrChildRole[$record["id"]]="";
            $this->processChildren($record["id"]);
        }
    }
    public function getModulePermission($data_item_type,$permissionFor)
    {
        $arrPermission=$this->getPermission();
        $modulePermission=$arrPermission[$data_item_type];
        if(isset($modulePermission[$permissionFor]) && $modulePermission[$permissionFor]>0) return true;
        return false;
    }
    /**
     * get permission by adding all the profiles permission. If isRefresh is true, the permission will be refreshed
     * @param type $isRefresh
     * @return type
     */
    public function getPermission($isRefresh=false)
    {
        if(is_null($this->objPermission) || $isRefresh)
        {
            $arrProfile=$this->arrProfile;
            if($arrProfile)
            {
                $objProfile = array_shift($arrProfile);
                $this->objPermission=$objProfile->getPermission();
                foreach($arrProfile as $objProfile)
                {
                    $this->objPermission->addPermission($objProfile->getPermission());
                }
            }
        }
        if(!is_null($this->objPermission)) return $this->objPermission->getPermission();
        return array();
    }
}
?>