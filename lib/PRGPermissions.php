<?php
class PRGPermissions
{
    private $arrPermission=array();
    public function __construct($profileid) 
    {
        $objDB=DatabaseConnection::getInstance();
        $site_id=$_SESSION["CATS"]->getSiteID();
        $sql="select * from auieo_profiles2permissions where profileid={$profileid} and site_id=".$site_id;
        $arrAssoc=$objDB->getAllAssoc($sql);//trace($arrAssoc);
        foreach($arrAssoc as $record)
        {
            $this->arrPermission[$record["data_item_type"]][$record["operation"]]=$record["permissions"];
        }
    }
    public function getPermission()
    {
        return $this->arrPermission;
    }
    public function addPermission($objPermission)
    {
        foreach ($objPermission as $data_item_type=>$arrPerm)
        {
            foreach ($arrPerm as $operation=>$permission)
            {
                if(isset($this->arrPermission[$data_item_type][$operation]))
                {
                    if($permission>0)
                    {
                        $this->arrPermission[$data_item_type][$operation]=$permission;
                    }
                }
                else
                {
                    $this->arrPermission[$data_item_type][$operation]=$permission;
                }
            }
        }
    }
}
?>