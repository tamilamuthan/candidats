<?php
class PRGRecordPermission
{
    private $objPermission;
    private $arrOwnerUser=array();
    public function __construct($recordid,$data_item_type) 
    {
        $site_id=$_SESSION["CATS"]->getSiteID();
        $objDB=  DatabaseConnection::getInstance();
        switch ($data_item_type)
        {
            case 100:
            {
                $sql="select * from candidate where candidate_id={$recordid} and site_id={$site_id}";
                $record=$objDB->getAssoc($sql);
                $owner=$record["owner"];
                $ownertype=$record["ownertype"];
                if($ownertype>0)
                {
                    $objOwner = PRGGroup::getInstance($owner);
                }
                else
                {
                    $this->arrOwnerUser[]=$owner;
                }
                break;
            }
            case 200:
            {
                $data_item_type=200;
                break;
            }
            case 300:
            {
                $data_item_type=300;
                break;
            }
            case 400:
            {
                $data_item_type=400;
                break;
            }
        }
        $sql="select * from "
    }
}
?>