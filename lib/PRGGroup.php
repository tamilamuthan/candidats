<?php
class PRGGroup
{
    private $id=0;
    private $name="";
    private $arrUser=array();
    private $objPermission=null;
    public function __construct($groupid) {
        $groupInfo=getGroupInfo($groupid);trace($groupInfo);
    }
    public static function &getInstance($groupid)
    {
        static $arrObjGroup=array();
        if(!isset($arrObjGroup[$groupid]))
        {
            $arrObjGroup[$groupid]=new PRGGroup($groupid);
        }
        return $arrObjGroup[$groupid];
    }
    public function getAllUsers()
    {
        
    }
    public function getPermission()
    {
        if($this->arrProfile)
        {
            $objProfile = array_shift($this->arrProfile);
            $this->objPermission=$objProfile->getPermission();
            foreach($this->arrProfile as $objProfile)
            {
                $this->objPermission->addPermission($objProfile->getPermission());
            }
        }
        return $this->objPermission->getPermission();
    }
}
?>