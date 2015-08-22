<?php
class PRGProfile
{
    private $id=0;
    private $name="";
    private $objPermission=null;
    public function __construct($profileid) {
        $this->id=$profileid;
        $objDB=  DatabaseConnection::getInstance();
        $site_id=$_SESSION["CATS"]->getSiteID();
        $sql="select * from auieo_profiles where id={$profileid} and site_id=".$_SESSION["CATS"]->getSiteID();
        $record=$objDB->getAssoc($sql);
        $this->name=$record["profilename"];
        $this->objPermission=new PRGPermissions($profileid);
    }
    public function getPermission()
    {
        return $this->objPermission;
    }
}
?>