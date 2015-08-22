<?php
function getGroupUIInfo($groupid)
{
    static $arrGroupInfo=array();
    if(!isset($arrGroupInfo[$groupid]))
    {
        $arrInfo=  getGroupInfo($groupid);
        $arrRecDefault=array();
        $arrRecExist=array();
        $arrRecNotExist=array();
        /**
        * build array with existing roles
        */
        if($arrInfo["role"]["exist"])
        foreach($arrInfo["role"]["exist"] as $id=>$rolename)
        {
            $arrRecDefault[]=array(
              "id"=>$id,
              "name"=>$rolename,
              "type"=>"role",
              "email"=>"Role:".$rolename,
          );
          $arrRecExist[]=array(
              "id"=>$id,
              "name"=>$rolename,
              "type"=>"role",
              "email"=>"Role:".$rolename,
          );
        }
        
        /**
        * build array with not existing roles
        */
        if($arrInfo["role"]["notexist"])
        foreach($arrInfo["role"]["notexist"] as $id=>$rolename)
        {
            $arrRecDefault[]=array(
              "id"=>$id,
              "name"=>$rolename,
              "type"=>"role",
              "email"=>"Role:".$rolename,
          );
          $arrRecNotExist[]=array(
              "id"=>$id,
              "name"=>$rolename,
              "type"=>"role",
              "email"=>"Role:".$rolename,
          );
        }
        
        /**
        * build array with existing users
        */
        if($arrInfo["user"]["exist"])
        foreach($arrInfo["user"]["exist"] as $id=>$user_name)
        {
            $arrRecDefault[]=array(
              "id"=>$id,
              "name"=>$user_name,
              "type"=>"user",
              "email"=>"User:".$user_name,
          );
          $arrRecExist[]=array(
              "id"=>$id,
              "name"=>$user_name,
              "type"=>"user",
              "email"=>"User:".$user_name,
          );
        }

        /**
        * build array with not existing users
        */
        if($arrInfo["user"]["notexist"])
        foreach($arrInfo["user"]["notexist"] as $id=>$user_name)
        {
            $arrRecDefault[]=array(
              "id"=>$id,
              "name"=>$user_name,
              "type"=>"user",
              "email"=>"User:".$user_name,
          );
          $arrRecNotExist[]=array(
              "id"=>$id,
              "name"=>$user_name,
              "type"=>"user",
              "email"=>"User:".$user_name,
          );
        }
        $arr=array();
        $arr["default"]=$arrRecDefault;
        $arr["exist"]=$arrRecExist;
        $arr["notexist"]=$arrRecNotExist;
        $arrGroupInfo[$groupid]=$arr;
    }
    return $arrGroupInfo[$groupid];
}
/**
 * add the where condition for listing the record
 * @param type $dgModule
 * @return string
 */
function loadPermittedRecordWhere(ClsAuieoSQL &$objSQL,$data_item_type)
{
     $access_level=Users::getInstance()->getUserInfo("access_level");
    if($access_level==500) return;
    $from=$objSQL->getDefaultFrom();
    if(getSharingAccess($data_item_type)<=0)
    {
        $obj = Users::getInstance();
        $objSQL->setWhereGroupStart();
        $objSQL->setWhereGroupStart();
        //$arrTmpWhere[]="({$dgTable}.owner = ".$_SESSION["CATS"]->getUserID()." and {$dgTable}.ownertype=0)";
        $objSQL->addWhere($from, "owner", $_SESSION["CATS"]->getUserID());
        $whr1=$objSQL->addWhere($from, "ownertype", 0);
        $whr1->setBoolean("OR");
        $objSQL->setWhereGroupEnd();
        $objSQL->setWhereGroupStart();
        $arrUserAllowed=$obj->getChildRolesUsers();
        if($arrUserAllowed)
        {
            foreach($arrUserAllowed as $userAllowed)
            {
                $objSQL->setWhereGroupStart();
                $objSQL->addWhere($from, "owner", $userAllowed);
                $whr2=$objSQL->addWhere($from, "ownertype", 0);
                $whr2->setBoolean("OR");
                $objSQL->setWhereGroupEnd();
            }
        }
        
        $arrGroup=$obj->getAllGroups();
        if($arrGroup)
        {
            foreach($arrGroup as $group)
            {
                $objSQL->setWhereGroupStart();
                $objSQL->addWhere($from, "owner", $group);
                $whr3=$objSQL->addWhere($from, "ownertype", 1);
                $whr3->setBoolean("OR");
                $objSQL->setWhereGroupend();
            } 
        }
        $objSQL->setWhereGroupEnd();
    }
}
/**
 * add the where condition for listing the record
 * @param type $dgModule
 * @return string
 */
function getPermittedRecordWhere($dgModule)
{
    $arrModuleInfo = getModuleInfo("modulename");
    if(!isset($arrModuleInfo[$dgModule])) return array();
    $moduleInfo=$arrModuleInfo[$dgModule];
    $dgTable=$moduleInfo["tablename"];
    $whereSQL=array();
    $access_level=Users::getInstance()->getUserInfo("access_level");
    if($access_level==500) return $whereSQL;
    if($dgTable)
    {
        $data_item_type=$moduleInfo["data_item_type_id"];
        if(getSharingAccess($data_item_type)<=0)
        {
            $obj = Users::getInstance();
            $arrTmpWhere=array();
            $arrTmpWhere[]="({$dgTable}.owner = ".$_SESSION["CATS"]->getUserID()." and {$dgTable}.ownertype=0)";

            $arrUserAllowed=$obj->getChildRolesUsers();
            if($arrUserAllowed)
            {
                foreach($arrUserAllowed as $userAllowed)
                {
                    $arrTmpWhere[]="({$dgTable}.owner = {$userAllowed} and {$dgTable}.ownertype=0)";
                }
            }

            $arrGroup=$obj->getAllGroups();
            if($arrGroup)
            {
                foreach($arrGroup as $group)
                {
                    $arrTmpWhere[]="({$dgTable}.owner = {$group} and {$dgTable}.ownertype=1)";
                } 
            }
            $whereSQL[]="(".implode(" OR ",$arrTmpWhere).")";
        }
    }
    return $whereSQL;
}

function getGroupInfo($groupid)
{
    static $arrGroupInfo=array();
    if(!isset($arrGroupInfo[$groupid]))
    {
        $objDB=DatabaseConnection::getInstance();
        $site_id=$_SESSION["CATS"]->getSiteID();
        /**
        * list all the roles for the site
        */
        $sql="select * from auieo_roles where site_id={$site_id} and rolename!='AUIEO_ROOT'";
        $arrRoleRecord=$objDB->getAllAssoc($sql);
        $arrRole=array();
        $arrRecDefault=array();
        if($arrRoleRecord)
        {
           foreach($arrRoleRecord as $record)
           {
               $arrRole[$record["id"]]=$record["rolename"];
           }
        }
        /**
        * list all the users for the site
        */
        $sql="select * from user where site_id={$site_id}";
        $arrUserRecord=$objDB->getAllAssoc($sql);
        $arrUser=array();
        $arrUserExist=array();
        $arrUserNotExist=array();
        if($arrUserRecord)
        {
           foreach($arrUserRecord as $record)
           {
               $arrUser[$record["user_id"]]=$record["user_name"];
           }
        }
        
        /**
        * load roles of the group
        */
        $sql="select auieo_roles.* from auieo_groups2roles 
           left join auieo_groups on auieo_groups2roles.groupid=auieo_groups.id
           left join auieo_roles on auieo_groups2roles.roleid=auieo_roles.id
           where auieo_groups.id={$groupid} and auieo_groups.site_id={$site_id}";
        $arrAssoc=$objDB->getAllAssoc($sql);
        $arrRoleExist=array();
        $arrRoleNotExist=array();
        $arrRoleRecord=array();
        $arrProfileRecord=array();
        $arrProfileNotExist=array();
        $arrProfileRecord=array();
        if($arrAssoc)
        {
           foreach($arrAssoc as $record)
           {
               /**
                * if the role id exist unset it. the balance array will be used to build $arrRoleNotExist
                */
               if(isset($arrRole[$record["id"]]))
               {
                   $arrRoleRecord[$record["id"]]=$record;
                   $arrRoleExist[$record["id"]]=$arrRole[$record["id"]];
                   unset($arrRole[$record["id"]]);
               }
           }
        }
        /**
        * remaining roles are not set for this group
        */
        $arrRoleNotExist=$arrRole;

        /**
        * load users of the group
        */
        $sql="select user.* from auieo_groups2users 
           left join auieo_groups on auieo_groups2users.groupid=auieo_groups.id
           left join user on auieo_groups2users.user_id=user.user_id
           where auieo_groups.id={$groupid} and auieo_groups.site_id={$site_id}";
        $arrAssoc=$objDB->getAllAssoc($sql);
        if($arrAssoc)
        {
           foreach($arrAssoc as $record)
           {
               /**
                * if the profile id exist unset it. the balance array will be used to build $arrRecNotExist
                */
               if(isset($arrUser[$record["user_id"]]))
               {
                   $arrProfileRecord[$record["user_id"]]=$record;
                   $arrUserExist[$record["user_id"]]=$arrUser[$record["user_id"]];
                   unset($arrUser[$record["user_id"]]);
               }
           }
        }
        /**
        * assign remaining user to arrUserNotExist;
        */
        $arrUserNotExist=$arrUser;
        $arr=array();
        $arr["role"]["exist"]=$arrRoleExist;
        $arr["role"]["notexist"]=$arrRoleNotExist;
        $arr["role"]["records"]=$arrRoleRecord;
        $arr["user"]["exist"]=$arrUserExist;
        $arr["user"]["notexist"]=$arrUserNotExist;
        $arr["user"]["records"]=$arrUserRecord;
        $arrGroupInfo[$groupid]=$arr;
    }
    return $arrGroupInfo[$groupid];
}
function getSharingAccess($data_item_type)
{
    static $arrSharingAccess=array();
    if(!isset($arrSharingAccess[$data_item_type]))
    {
        $sql="select * from auieo_sharingaccess where data_item_type={$data_item_type}";
        $objDB=DatabaseConnection::getInstance();
        $record=$objDB->getAssoc($sql);
        if(empty($record))
        {
            $arrSharingAccess[$data_item_type]=0;
        }
        else
        {
            $arrSharingAccess[$data_item_type]=$record["sharingaccess"];
        }
    }
    return $arrSharingAccess[$data_item_type];
}
?>