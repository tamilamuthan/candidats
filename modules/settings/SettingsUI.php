<?php
/*
 * CATS
 * Settings Module
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * $Id: SettingsUI.php 3810 2007-12-05 19:13:25Z brian $
 */

include_once('./lib/LoginActivity.php');
include_once('./lib/NewVersionCheck.php');
include_once('./lib/Candidates.php');
include_once('./lib/Companies.php');
include_once('./lib/Contacts.php');
include_once('./lib/Graphs.php');
include_once('./lib/Site.php');
include_once('./lib/ListEditor.php');
include_once('./lib/SystemUtility.php');
include_once('./lib/Mailer.php');
include_once('./lib/EmailTemplates.php');
include_once('./lib/License.php');
include_once('./lib/History.php');
include_once('./lib/Pipelines.php');
include_once('./lib/CareerPortal.php');
include_once('./lib/WebForm.php');
include_once('./lib/CommonErrors.php');
include_once('./lib/Import.php');
include_once('./lib/Questionnaire.php');
eval(Hooks::get('XML_FEED_SUBMISSION_SETTINGS_HEADERS'));

/* Users.php is included by index.php already. */


class SettingsUI extends UserInterface
{
    /* Maximum number of login history entries to display on User Details. */
    const MAX_RECENT_LOGINS = 15;


    public function __construct()
    {
        parent::__construct();

        $this->_realAccessLevel = $_SESSION['CATS']->getRealAccessLevel();
        $this->_authenticationRequired = true;
        $this->_moduleDirectory = 'settings';
        $this->_moduleName = 'settings';
        $this->_moduleTabText = 'Settings';

        $mp = array(
            'Administration' => CATSUtility::getIndexName() . '?m=settings&amp;a=administration',
            'My Profile'     => CATSUtility::getIndexName() . '?m=settings'
        );

        $mp['Downloads'] = CATSUtility::getIndexName() . '?m=settings&a=downloads';

        $this->_subTabs = $mp;
        
        $this->_hooks = $this->defineHooks();
    }

    public function defineHooks()
    {
        return array(
            /* Hide all tabs in career portal mode. */
            'TEMPLATE_UTILITY_EVALUATE_TAB_VISIBLE' => '
                if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\'))
                {
                    if (!in_array($moduleName, array(\'settings\')))
                    {
                        $displayTab = false;
                    }
                }
            ',
            
            /* Home goes to settings in career portal mode. */
            'HOME' => '
                if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\'))
                {
                    CATSUtility::transferRelativeURI(\'m=settings\');
                    return false;
                }
            ',
            
            /* My Profile goes to administration in career portal mode. */
            'SETTINGS_DISPLAY_PROFILE_SETTINGS' => '
                if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\'))
                {
                    CATSUtility::transferRelativeURI(\'m=settings&a=administration\');
                    return false;
                }
            ',

            /* Deny access to all modules in career portal mode but settings. */
            'CLIENTS_HANDLE_REQUEST' =>    'if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\')) $this->fatal("' . ERROR_NO_PERMISSION . '");',
            'CONTACTS_HANDLE_REQUEST' =>   'if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\')) $this->fatal("' . ERROR_NO_PERMISSION . '");',
            'CALENDAR_HANDLE_REQUEST' =>   'if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\')) $this->fatal("' . ERROR_NO_PERMISSION . '");',
            'JO_HANDLE_REQUEST' =>         'if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\')) $this->fatal("' . ERROR_NO_PERMISSION . '");',
            'CANDIDATES_HANDLE_REQUEST' => 'if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\')) $this->fatal("' . ERROR_NO_PERMISSION . '");',
            'ACTIVITY_HANDLE_REQUEST' =>   'if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\')) $this->fatal("' . ERROR_NO_PERMISSION . '");',
            'REPORTS_HANDLE_REQUEST' =>    'if ($_SESSION[\'CATS\']->hasUserCategory(\'careerportal\')) $this->fatal("' . ERROR_NO_PERMISSION . '");'
        );
    }

    public function render()
    {
        $action = $this->getAction();

        if (!eval(Hooks::get('SETTINGS_HANDLE_REQUEST'))) return;

        switch ($action)
        {
            case 'changePassword':
                if ($this->isPostBack())
                {
                    $this->onChangePassword();
                }
                break;
            case 'customizeFilter':
                if ($this->isPostBack())
                {
                    $this->onCustomizeFilter();
                }
                else
                {
                    $this->customizeFilter();
                }
                break;
            case 'filtergrouping':
                if(isset($_REQUEST["grouping"]))
                {
                    $this->filtergrouping(1);
                }
                else
                {
                    $this->filtergrouping(0);
                }
                break;
            case 'delete_password':
                file_put_contents("delete_password.ini", md5($_REQUEST["password"]));
                header("Location:index.php?m=settings&a=administration&s=passwords&msg=Password Changed Successfully");exit;
                break;
            case 'newInstallPassword':
                if ($this->isPostBack())
                {
                    $this->onNewInstallPassword();
                }
                else
                {
                    $this->newInstallPassword();
                }
                break;

            case 'forceEmail':
                if ($this->isPostBack())
                {
                    $this->onForceEmail();
                }
                else
                {
                    $this->forceEmail();
                }
                break;

            case 'newSiteName':
                if ($this->isPostBack())
                {
                    $this->onNewSiteName();
                }
                else
                {
                    $this->newSiteName();
                }
                break;

            case 'upgradeSiteName':
                if ($this->isPostBack())
                {
                    $this->onNewSiteName();
                }
                else
                {
                    $this->upgradeSiteName();
                }
                break;

            case 'newInstallFinished':
                if ($this->isPostBack())
                {
                    $this->onNewInstallFinished();
                }
                else
                {
                    $this->newInstallFinished();
                }
                break;

            case 'manageUsers':
                $this->manageUsers();
                break;

            case 'professional':
                $this->manageProfessional();
                break;

            case 'previewPage':
                $this->previewPage();
                break;

            case 'previewPageTop':
                $this->previewPageTop();
                break;

            case 'showUser':
                $this->showUser();
                break;

            case 'addUser':
                if ($this->isPostBack())
                {
                    $this->onAddUser();
                }
                else
                {
                    $this->addUser();
                }

                break;

            case 'editUser':
                if ($this->isPostBack())
                {
                    $this->onEditUser();
                }
                else
                {
                    $this->editUser();
                }

                break;

            case 'createBackup':
                $this->createBackup();
                break;

            case 'deleteBackup':
                $this->deleteBackup();
                break;

            case 'customizeExtraFields':
                if ($this->isPostBack())
                {
                    $this->onCustomizeExtraFields();
                }
                else
                {
                    $this->customizeExtraFields();
                }
                break;

            case 'customizeCalendar':
                if ($this->isPostBack())
                {
                    $this->onCustomizeCalendar();
                }
                else
                {
                    $this->customizeCalendar();
                }
                break;

            case 'reports':
                if ($this->isPostBack())
                {

                }
                else
                {
                    $this->reports();
                }
                break;

            case 'emailSettings':
                if ($this->isPostBack())
                {
                    $this->onEmailSettings();
                }
                else
                {
                    $this->emailSettings();
                }
                break;

            case 'careerPortalQuestionnairePreview':
                $this->careerPortalQuestionnairePreview();
                break;

            case 'careerPortalQuestionnaire':
                if ($this->isPostBack())
                {
                    $this->onCareerPortalQuestionnaire();
                }
                else
                {
                    $this->careerPortalQuestionnaire();
                }
                break;

            case 'careerPortalQuestionnaireUpdate':
                $this->careerPortalQuestionnaireUpdate();
                break;

            case 'careerPortalTemplateEdit':
                if ($this->isPostBack())
                {
                    $this->onCareerPortalTemplateEdit();
                }
                else
                {
                    $this->careerPortalTemplateEdit();
                }
                break;

            case 'careerPortalSettings':
                if ($this->isPostBack())
                {
                    $this->onCareerPortalSettings();
                }
                else
                {
                    $this->careerPortalSettings();
                }
                break;

            case 'eeo':
                if ($this->isPostBack())
                {
                    $this->onEEOEOCSettings();
                }
                else
                {
                    $this->EEOEOCSettings();
                }
                break;

            case 'onCareerPortalTweak':
                $this->onCareerPortalTweak();
                break;

            /* This really only exists for automated testing at this point. */
            case 'deleteUser':
                $this->onDeleteUser();
                break;

            case 'emailTemplates':
                if ($this->isPostBack())
                {
                    $this->onEmailTemplates();
                }
                else
                {
                    $this->emailTemplates();
                }
                break;

           case 'aspLocalization':
                if ($this->isPostBack())
                {
                    $this->onAspLocalization();
                }
                break;

           case 'loginActivity':
                include_once('./lib/BrowserDetection.php');

                $this->loginActivity();
                break;

            case 'viewItemHistory':
                $this->viewItemHistory();
                break;

            case 'getFirefoxModal':
                $this->getFirefoxModal();
                break;

            case 'downloads':
                $this->downloads();
                break;

            case 'ajax_wizardAddUser':
                $this->wizard_addUser();
                break;

            case 'ajax_wizardDeleteUser':
                $this->wizard_deleteUser();
                break;

            case 'ajax_wizardCheckKey':
                $this->wizard_checkKey();
                break;

            case 'ajax_wizardLocalization':
                $this->wizard_localization();
                break;

            case 'ajax_wizardFirstTimeSetup':
                $this->wizard_firstTimeSetup();
                break;

            case 'ajax_wizardLicense':
                $this->wizard_license();
                break;

            case 'ajax_wizardPassword':
                $this->wizard_password();
                break;

            case 'ajax_wizardSiteName':
                $this->wizard_siteName();
                break;

            case 'ajax_wizardEmail':
                $this->wizard_email();
                break;

            case 'ajax_wizardImport':
                $this->wizard_import();
                break;

            case 'ajax_wizardWebsite':
                $this->wizard_website();
                break;

            case 'administration':
                if ($this->isPostBack())
                {
                    $this->onAdministration();
                }
                else
                {
                    $this->administration();
                }
                break;

            /* Main settings page. */
            case 'myProfile':
                default:
                $this->myProfile();
                break;
        }
    }

    public function filtergrouping($value)
    {
        $_siteID = $_SESSION['CATS']->getSiteID();;
        $_db = DatabaseConnection::getInstance();
        $sql="Select * from settings where setting='filtergrouping' and site_id='{$_siteID}'";
        $arrData=$_db->getAssoc($sql);
        if(empty($arrData))
        {
            $sql="insert into settings (`setting`,`value`,`site_id`,`settings_type`) values('filtergrouping','{$value}','{$_siteID}','4')";
        }
        else
        {
            $sql="update settings set `value`='{$value}',`settings_type`=4 where setting='filtergrouping' and site_id='{$_siteID}'";
        }
        $_db->query($sql);
        CATSUtility::transferRelativeURI(
            'm=settings&a=administration'
        );
    }

    public function duplicate()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $sites = new Site($this->_siteID);
        $rs = $sites->getAll();

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Site Management');
        $this->_template->assign('rs', $rs);
        $this->_template->display('./modules/settings/duplicate.php');
    }
    public function fields()
    {
        if(isset($_REQUEST["s"]) && $_REQUEST["s"]=="addnewfield")
        {
            $_siteID = $_SESSION['CATS']->getSiteID();
            $fieldCaption=$_REQUEST["fieldname"];
            $data_item_type=$_REQUEST["data_item_type"];
            $arrExp=explode("_",$fieldCaption);
            $fieldName=  strtolower($fieldCaption);
            $fieldName= preg_replace("/\W|_/", " ", $fieldName);
            $fieldName= preg_replace("/[ ]{2,}/", " ", $fieldName);
            $fieldName= preg_replace("/\s/", "_", $fieldName);
            $fieldName=trim($fieldName);
            if(strlen($fieldName)>=3)
            {
                $db=  DatabaseConnection::getInstance();
                $sql="select * from auieo_fields where fieldname='{$fieldName}' and site_id={$_siteID} and data_item_type={$data_item_type}";
                $record=$db->getAssoc($sql);
                if(empty($record))
                {
                    /**
                     * get uitype details
                     */
                    $sql="select * from auieo_uitype where id={$_REQUEST["uitype"]}";
                    $record=$db->getAssoc($sql);
                    $fieldinfo=$record["fieldinfo"];
                    $length=$record["length"];
                    $arrFieldInfo=  getFieldInfoByUIType($fieldinfo);
                    $datatype=$arrFieldInfo["datatype"];
                    $tableInfo=getTableInfoByDataItemType($data_item_type);
                    $fieldmodule=$tableInfo["module"];
                    $tableName=$tableInfo["table"];
                    
                    $sql="select max(sequence) as seq from auieo_fields where site_id={$_siteID} and data_item_type={$data_item_type}";
                    $recordSeq=$db->getAssoc($sql);
                    $next_sequence=$recordSeq["seq"]+1;
                    /**
                     * create field in the table
                     */
                    $sql="alter table `{$tableName}` add {$fieldName} {$datatype}($length) NOT NULL";
                    $db->query($sql);
                    /**
                     * insert into auieo_fields
                     */
                    $sql="insert into auieo_fields (fieldname,fieldlabel,sequence,site_id,data_item_type,is_extra) values('{$fieldName}','{$fieldCaption}','{$next_sequence}',{$_siteID},{$data_item_type},1)";
                    $db->query($sql);
                }
            }
            header("Location:index.php?m=settings&a=fields&fieldmodule={$fieldmodule}");exit;
        }
    }
    
    public function moveDown()
    {
        $_siteID = $_SESSION['CATS']->getSiteID();
        $sql="select * from auieo_fields where id={$_REQUEST["field_id"]} and site_id={$_siteID}";
        $db=DatabaseConnection::getInstance();
        $record=$db->getAssoc($sql);
        if(empty($record)) die("Unknown field ID");
 
        $sql="select * from auieo_fields where sequence=".($record["sequence"]+1)." and data_item_type={$record["data_item_type"]} and site_id={$_siteID}";
        $prevRecord=$db->getAssoc($sql);
        $prevID=$prevRecord["id"];
        $sql="update auieo_fields set sequence=".$record["sequence"]." where id={$prevID}";
        $db->query($sql);
        $sql="update auieo_fields set sequence=".($record["sequence"]+1)." where id={$_REQUEST["field_id"]}";
        $db->query($sql);
        
        header("Location:index.php?m=settings&a=fields&fieldmodule={$_REQUEST["modulename"]}");
    }
    
    public function moveUp()
    {
        $_siteID = $_SESSION['CATS']->getSiteID();
        $sql="select * from auieo_fields where id={$_REQUEST["field_id"]} and site_id={$_siteID}";
        $db=DatabaseConnection::getInstance();
        $record=$db->getAssoc($sql);
        if(empty($record)) die("Unknown field ID");
 
        $sqlprev="select * from auieo_fields where sequence=".($record["sequence"]-1)." and data_item_type={$record["data_item_type"]} and site_id={$_siteID}";
        $prevRecord=$db->getAssoc($sqlprev);
        $prevID=$prevRecord["id"];
        $sql="update auieo_fields set sequence=".$record["sequence"]." where id={$prevID}";
        $db->query($sql);
        $sql="update auieo_fields set sequence=".($record["sequence"]-1)." where id={$_REQUEST["field_id"]}";
        $db->query($sql);
        
        header("Location:index.php?m=settings&a=fields&fieldmodule={$_REQUEST["modulename"]}");
    }
    
    public function delete()
    {
        $moduleInfo=getTableInfoByModule($_REQUEST["modulename"]);
        $_siteID = $_SESSION['CATS']->getSiteID();
        $db=DatabaseConnection::getInstance();
        
        $sql="select * from auieo_fields where data_item_type={$moduleInfo["data_item_type"]} and id='{$_REQUEST["field_id"]}' and site_id={$_siteID} and is_extra=1";
        $record=$db->getAssoc($sql);
        if(!empty($record))
        {
            $sql="delete from auieo_fields where data_item_type={$moduleInfo["data_item_type"]} and id='{$_REQUEST["field_id"]}' and site_id={$_siteID}";
            $db->query($sql);

            $sql="ALTER TABLE `{$moduleInfo["table"]}` DROP `{$record["fieldname"]}`";
            $db->query($sql);
        }
        header("Location:index.php?m=settings&a=fields&fieldmodule={$_REQUEST["modulename"]}");exit;
    }
    
    public function updateField()
    {
        $moduleInfo=getTableInfoByModule($_REQUEST["modulename"]);
        $_siteID = $_SESSION['CATS']->getSiteID();
        $sql="update auieo_fields set displaytype={$_REQUEST["checked"]} where data_item_type={$moduleInfo["data_item_type"]} and fieldname='{$_REQUEST["field_name"]}' and site_id={$_siteID}";
        $db=DatabaseConnection::getInstance();
        $db->query($sql);
        exit;
    }
    
    public function updateFieldReadonly()
    {
        $moduleInfo=getTableInfoByModule($_REQUEST["modulename"]);
        $_siteID = $_SESSION['CATS']->getSiteID();
        $sql="update auieo_fields set readonly={$_REQUEST["checked"]} where data_item_type={$moduleInfo["data_item_type"]} and fieldname='{$_REQUEST["field_name"]}' and site_id={$_siteID}";
        $db=DatabaseConnection::getInstance();
        $db->query($sql);
        exit;
    }
    
    private function getRootID()
    {
        $objDB=DatabaseConnection::getInstance();
        $site_id=$_SESSION['CATS']->getSiteID();
        $sql="select * from auieo_roles where site_id={$site_id} and parentid=0";
        $objAssoc=$objDB->getAllAssoc($sql);
        /**
         * this condition will never true until abnormal happens
         */
        if(empty($objAssoc))
        {
            return 0;
        }
        $rootid=$objAssoc[0]["id"];
        return $rootid;
    }
    
    private function getParentID($id)
    {
        $objDB=DatabaseConnection::getInstance();
        $site_id=$_SESSION['CATS']->getSiteID();
        $sql="select * from auieo_roles where site_id={$site_id} and id={$id}";
        $objAssoc=$objDB->getAllAssoc($sql);
        /**
         * this condition will never true until abnormal happens
         */
        if(empty($objAssoc))
        {
            return 0;
        }
        $parentid=$objAssoc[0]["parentid"];
        return $parentid;
    }
    
    private function createTreeFromList(array $array, $parent_id = 1) {
        $return = array();

        foreach ($array as $k => $v) {
            if ($v['parentid'] == $parent_id) 
            {
                unset($v["parentid"]);
                $return[$k] = $v; 
                $return[$k]['nodes'] = $this->createTreeFromList($array, $v['id']);    
            }
        }
        $arrReturn=array();
        foreach($return as $ret)
        {
            $arrReturn[]=$ret;
        }
        return $arrReturn;
    }

    private function getJSONTree()
    {
        $db=  DatabaseConnection::getInstance();
        $site_id=$_SESSION["CATS"]->getSiteID();
        $query = "SELECT id,parentid,rolename as title FROM auieo_roles where site_id={$site_id} and parentid!=0";
        $arrRow = $db->getAllAssoc($query);
        $tree=$this->createTreeFromList($arrRow);//trace($tree);
        $jsontree=  json_encode($tree);
        return $jsontree;
    }

    private function syncRole()
    {
        $syncData = json_decode(file_get_contents("php://input"),true);
        $callback=function ($childRecord,$parentRecordID)
        {
            $objDB=DatabaseConnection::getInstance();
            $site_id=$_SESSION['CATS']->getSiteID();
            $sql="select * from auieo_roles where site_id={$site_id} and id={$childRecord["id"]}";
            $objAssoc=$objDB->getAllAssoc($sql);
            $parentid=$objAssoc[0]["parentid"];
            Logger::getLogger("AuieoATS")->info("At api.php:syncRole:callback. {$parentid}!={$parentRecordID}");
            if($parentid!=$parentRecordID)
            {
                $sql="update auieo_roles set parentid={$parentRecordID} where site_id={$site_id} and id={$childRecord["id"]}";
                Logger::getLogger("AuieoATS")->info("At api.php:syncRole:callback:if");
                $objDB->query($sql);
            }
        };
        Logger::getLogger("AuieoATS")->info("At api.php:syncRole.");
        $this->recursiveNavigation($this->getRootID(), $syncData, $callback);
        $success = array('status' => "Success", "msg" => "Role Synced Successfully.");
        $this->response($this->json($success),200);
    }
    
    public function roles()
    {
        
    }
    
    public function webserviceInsertRole($api)
    {
        $role=$api->getInput();
        $objDB=DatabaseConnection::getInstance();
        $site_id=$_SESSION['CATS']->getSiteID();
        $sql="select * from auieo_roles where site_id={$site_id}";
        $objDB=DatabaseConnection::getInstance();
        $arrAssoc=$objDB->getAllAssoc($sql);
        if(empty($arrAssoc))
        {
            $sql="insert into auieo_roles (`id`,`rolename`,`site_id`,`parentid`) values (1,'root',$site_id,0)";
            $objDB->query($sql);
        }

        /**
         * if parentid not set in the input, get the root id and consider it as parent
         */
        if(!isset($role["parentid"]))
        {
            $role["parentid"]=$this->getRootID();
        }

        /**
         * check whether the parentid exist in database.
         * if exist, proceed as it is
         * else throw error
         */
        $site_id=$_SESSION['CATS']->getSiteID();
        $sql="select * from auieo_roles where site_id={$site_id} and id={$role["parentid"]}";
        $arrAssoc=$objDB->getAllAssoc($sql);
        if(empty($arrAssoc))
        {
            $fail = array('status' => "Fail", "msg" => "Unknown parent ID", "data" => $role);
            $api->response($api->json($fail),406);
            return;
        }
        /**
         * check whether the duplicate rolename exist in database.
         * if not exist, proceed as it is
         * else throw error
         */
        $sql="select * from auieo_roles where site_id={$site_id} and rolename='{$role["rolename"]}'";
        $objDB=DatabaseConnection::getInstance();
        $arrAssoc=$objDB->getAllAssoc($sql);
        if(!empty($arrAssoc))
        {
            $fail = array('status' => "Fail", "msg" => "Duplicate Role Name Exist", "data" => $role);
            $api->response($api->json($fail),406);
            return;
        }
        $role["site_id"]=$site_id;
        $column_names = array('rolename','parentid','site_id');
        $keys = array_keys($role);
        $columns = '';
        $values = '';
        foreach($column_names as $desired_key){ // Check the customer received. If blank insert blank into the array.
           if(!in_array($desired_key, $keys)) {
                        $$desired_key = '';
                }else{
                        $$desired_key = $role[$desired_key];
                }
                $columns = $columns.$desired_key.',';
                $values = $values."'".$$desired_key."',";
        }
        $query="select ";
        $query = "INSERT INTO auieo_roles(".trim($columns,',').") VALUES(".trim($values,',').")";
        if(!empty($role)){
                $r = $objDB->query($query);
                $id=$objDB->getLastInsertID();
                $success = array('status' => "Success", "msg" => "Role Created Successfully.", "data" => $role,"id"=>$id);
                $api->response($api->json($success),200);
        }else
                $api->response('',204);
    }
    
    private function updateParentID($id,$parentid){	
        $objDB=DatabaseConnection::getInstance();
        $site_id=$_SESSION['CATS']->getSiteID();
        $sql="update auieo_roles set parentid={$parentid} where site_id={$site_id} and id={$id}";
        $objDB->query($sql);
    }

    public function webserviceDeleteRole($api){
            $id = (int)$api->_request['id'];
            if($id > 0)
            {	
                $objDB=DatabaseConnection::getInstance();
                $site_id=$_SESSION['CATS']->getSiteID();
                $rootid=$this->getRootID();
                $sql="update auieo_roles set parentid={$rootid} where site_id={$site_id} and parentid={$id}";
                $objDB->query($sql);
                $query="DELETE FROM auieo_roles WHERE id = $id";
                $r = $objDB->query($query);
                $success = array('status' => "Success", "msg" => "Successfully deleted one record.", "tree"=>$this->getJSONTree());
                $api->response($api->json($success),200);
            }else
                    $api->response('',204);	// If no records "No Content" status
    }
    
    public function webserviceAddProfilesToRole($api)
    {
        $input=$api->getInput();
        $objDB=DatabaseConnection::getInstance();
        $site_id=$_SESSION['CATS']->getSiteID();
        foreach($input["profileid"] as $profileid)
        {
            $sql="insert into auieo_roles2profiles (`roleid`,`profileid`,`site_id`) values('{$input["roleid"]}','{$profileid}','{$site_id}')";
            $objDB->query($sql);
        }
        $success = array('status' => "Success", "msg" => "Successfully added ".(count($input["profileid"]))." record.");
        $api->response($api->json($success),200);
    }
    
    public function webserviceDeleteProfilesFromRole($api)
    {
        $input=$api->getInput();
        $objDB=DatabaseConnection::getInstance();
        $site_id=$_SESSION['CATS']->getSiteID();
        foreach($input["profileid"] as $profileid)
        {
            $sql="delete from auieo_roles2profiles where `roleid`='{$input["roleid"]}' and `profileid`='{$profileid}' and `site_id`='{$site_id}'";
            $objDB->query($sql);
        }
        $success = array('status' => "Success", "msg" => "Successfully deleted ".(count($input["profileid"]))." record.");
        $api->response($api->json($success),200);
    }

    public function transfer()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $sites = new Site($this->_siteID);
        $rs = $sites->getAll();

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Site Management');
        $this->_template->assign('rs', $rs);
        $this->_template->display('./modules/settings/transfer.php');
    }
    
    /*
     * Called by render() to process loading the site users page.
     */
    public function manageSites()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_siteID > 1 || $this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $sites = new Site($this->_siteID);
        $rs = $sites->getAll();
        $arrSite=array();
        foreach($rs as $ind=>$record)
        {
            if($record["siteID"]==180) continue;
            $arrSite[$ind]=$record; 
        }
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Site Management');
        $this->_template->assign('rs', $arrSite);
        $this->_template->display('./modules/settings/Sites.php');
    }
    
    /*
     * Called by render() to process loading the get firefox modal dialog.
     */
    public function getFirefoxModal()
    {
        $this->_template->display(
            './modules/settings/getFirefoxModal.tpl'
        );
    }
    public function customizeFilter()
    {
        $_siteID = $_SESSION['CATS']->getSiteID();;
        $_db = DatabaseConnection::getInstance();
        $sql="Select * from settings where setting='filtergrouping' and site_id='{$_siteID}'";
        $arrData=$_db->getAssoc($sql);
        if(isset($arrData) && !empty($arrData) && $arrData["value"]>0)
        {
            $this->_template->assign("checked","checked");
        }
        else
        {
            $this->_template->assign("checked","");
        }
        $this->_template->display(
            './modules/settings/customizeFilter.php'
        );
    }
    public function onCustomizeFilter()
    {
        $this->_template->display(
            './modules/settings/customizeFilter.php'
        );
    }
    /*
     * Called by render() to process loading the my profile page.
     */
    public function myProfile()
    {
        $isDemoUser = $_SESSION['CATS']->isDemo();

        if (isset($_GET['s']))
        {
            switch($_GET['s'])
            {
                case 'changePassword':
                    $templateFile = './modules/settings/ChangePassword.php';
                    break;

                default:
                    $templateFile = './modules/settings/MyProfile.php';
                    break;
            }
        }
        else
        {
            $templateFile = './modules/settings/MyProfile.php';
        }

        if (!eval(Hooks::get('SETTINGS_DISPLAY_PROFILE_SETTINGS'))) return;

        $this->_template->assign('isDemoUser', $isDemoUser);
        $this->_template->assign('userID', $this->_userID);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'My Profile');
        $this->_template->display($templateFile);
    }
    
    public function groups()
    {
        if(isset($_REQUEST["s"]))
        {
            $objDB=DatabaseConnection::getInstance();
            $site_id=$_SESSION["CATS"]->getSiteID();
            switch($_REQUEST["s"])
            {
                case 'update_cell':
                {
                    $sql="update auieo_groups set groupname='{$_REQUEST["cell_data"]}' where id='{$_REQUEST["recordid"]}' and site_id={$site_id}";
                    $objDB->query($sql);
                    break;
                }
                case 'update_permission':
                {
                    $data_item_type=0;
                    if($_REQUEST["module"]=="Candidate") $data_item_type=100;
                    else if($_REQUEST["module"]=="Company") $data_item_type=200;
                    else if($_REQUEST["module"]=="Contact") $data_item_type=300;
                    else if($_REQUEST["module"]=="Joborder") $data_item_type=400;
                    $sql="update auieo_profiles2permissions set permissions={$_REQUEST["cell_data"]}  where data_item_type='{$data_item_type}' and profileid={$_REQUEST["profileid"]} and operation={$_REQUEST["fieldname"]} and site_id={$site_id}";
                    $objDB->query($sql);
                    break;
                }
                case 'load_rolesusers':
                {
                    $arr=getGroupUIInfo($_REQUEST["groupid"]);
                    echo json_encode($arr);
                    break;
                }
                case "delete":
                {
                    $sql="delete from auieo_groups2roles  where groupid={$_REQUEST["groupid"]} and  site_id={$site_id}";
                    $objDB->query($sql);
                    $sql="delete from auieo_groups where id={$_REQUEST["groupid"]}";
                    $objDB->query($sql);
                    header("Location:index.php?m=settings&a=groups");exit;
                    break;
                }
                case 'assign':
                {
                    if($_REQUEST["type"]=="role")
                    {
                        $sql="select * from auieo_groups2roles where groupid={$_REQUEST["group"]} and roleid={$_REQUEST["id"]} and site_id={$site_id}";
                        $arrAssoc=$objDB->getAllAssoc($sql);
                        if(empty($arrAssoc))
                        {
                            $sql="insert into auieo_groups2roles (groupid,roleid,site_id) values ({$_REQUEST["group"]},{$_REQUEST["id"]},{$site_id})";
                            $objDB->query($sql);
                        }
                    }
                    else
                    {
                        $sql="select * from auieo_groups2users where groupid={$_REQUEST["group"]} and user_id={$_REQUEST["id"]} and site_id={$site_id}";
                        $arrAssoc=$objDB->getAllAssoc($sql);
                        if(empty($arrAssoc))
                        {
                            $sql="insert into auieo_groups2users (groupid,user_id,site_id) values ({$_REQUEST["group"]},{$_REQUEST["id"]},{$site_id})";
                            $objDB->query($sql);
                        }
                    }
                    
                    break;
                }
                case 'remove':
                {
                    if($_REQUEST["type"]=="role")
                    {
                        $sql="delete from auieo_groups2roles where groupid={$_REQUEST["group"]} and roleid={$_REQUEST["id"]} and site_id={$site_id}";
                        $arrAssoc=$objDB->query($sql);
                    }
                    else
                    {
                        $sql="delete from auieo_groups2users where groupid={$_REQUEST["group"]} and user_id={$_REQUEST["id"]} and site_id={$site_id}";
                        $arrAssoc=$objDB->getAllAssoc($sql);
                    }
                    
                    break;
                }
                case 'addnew':
                {
                    $sql="insert into auieo_groups (`groupname`,`site_id`) values ('{$_REQUEST["groupname"]}','{$site_id}')";
                    $objDB->query($sql);
                    echo $objDB->getLastInsertID();
                    break;
                }
                default:
                {
                    $sql="select * from auieo_groups where site_id=".$site_id;
                    $arrAssoc=$objDB->getAllAssoc($sql);
                    foreach( $arrAssoc as $ind=>$assoc)
                    {
                        $arrAssoc[$ind]["delete"]="Delete";
                    }
                    echo json_encode($arrAssoc);
                }
            }
            exit;
        }
    }
    
    public function profiles()
    {
        if(isset($_REQUEST["s"]))
        {
            $objDB=DatabaseConnection::getInstance();
            $site_id=$_SESSION["CATS"]->getSiteID();
            switch($_REQUEST["s"])
            {
                case 'update_cell':
                {
                    $sql="update auieo_profiles set profilename='{$_REQUEST["cell_data"]}' where id='{$_REQUEST["recordid"]}' and site_id={$site_id}";
                    $objDB->query($sql);
                    break;
                }
                case 'delete':
                {
                    $sql="delete from auieo_profiles where id={$_REQUEST["profileid"]}";
                    $objDB->query($sql);
                    header("Location:index.php?m=settings&a=profiles");exit;
                    break;
                }
                case 'update_permission':
                {
                    $data_item_type=0;
                    $arrModuleInfo=getModuleInfo("modulename");
                    foreach($arrModuleInfo as $modulename=>$moduleInfo)
                    {
                        if($_REQUEST["module"] == $modulename)
                        {
                            $data_item_type=$moduleInfo["data_item_type_id"];
                            break;
                        }
                    }
                    $sql="select * from auieo_profiles2permissions where  data_item_type='{$data_item_type}' and profileid={$_REQUEST["profileid"]} and operation={$_REQUEST["fieldname"]} and site_id={$site_id}";
                    if($objDB->getAssoc($sql))
                    {
                        $sql="update auieo_profiles2permissions set permissions={$_REQUEST["cell_data"]}  where data_item_type='{$data_item_type}' and profileid={$_REQUEST["profileid"]} and operation={$_REQUEST["fieldname"]} and site_id={$site_id}";
                        $objDB->query($sql);
                    }
                    else
                    {
                        $sql="insert into auieo_profiles2permissions (permissions,data_item_type,profileid,operation,site_id) values({$_REQUEST["cell_data"]},'{$data_item_type}',{$_REQUEST["profileid"]},'{$_REQUEST["fieldname"]}',{$site_id})";
                        $objDB->query($sql);
                    }
                    break;
                }
                case 'load_profile':
                {
                    $arrProfilePermission=array();
                    $modulesInfo=getModuleInfo("data_item_type");
                    foreach($modulesInfo as $data_item_type=>$modInfo)
                    {
                        $arrProfilePermission[$data_item_type]=array("module"=>$modInfo["modulename"],"data_item_id"=>$data_item_type,0=>0,1=>0,2=>0,3=>0,4=>0);
                    }

                    //$arrSite=array(1,180);
                    $arrSQL=array();

                    $profileid=$_REQUEST["profileid"];
                    $sql="select * from auieo_profiles2permissions where profileid={$profileid} and site_id=".$site_id;
                    $arrAssoc=$objDB->getAllAssoc($sql);
                    if($arrAssoc)
                    {
                        foreach($arrAssoc as $record)
                        {
                            $arrProfilePermission[$record["data_item_type"]][$record["operation"]]=$record["permissions"];
                        }
                    }
                    else
                    {
                        foreach($arrProfilePermission as $data_item_type=>$rowData)
                        {
                            array_shift($rowData);
                            array_shift($rowData);
                            foreach($rowData as $operation=>$permissions)
                            {
                                $arrSQL[]="insert into auieo_profiles2permissions (`profileid`,`data_item_type`,`operation`,`permissions`,`site_id`) 
                                values ('{$profileid}','{$data_item_type}','{$operation}','{$permissions}','{$site_id}')";
                            }
                        }
                    }
                    echo json_encode(array_values($arrProfilePermission));
                    break;
                }
                case 'assign':
                {
                    $sql="select * from auieo_roles2profiles where roleid={$_REQUEST["role"]} && profileid={$_REQUEST["profileid"]}";
                    $arrAssoc=$objDB->getAllAssoc($sql);
                    if(empty($arrAssoc))
                    {
                        $sql="insert into auieo_roles2profiles (roleid,profileid,site_id) values ({$_REQUEST["role"]},{$_REQUEST["profileid"]},{$site_id})";
                        $objDB->query($sql);
                    }
                    break;
                }
                case 'addnew':
                {
                    $sql="insert into auieo_profiles (`profilename`,`site_id`) values ('{$_REQUEST["profilename"]}','{$site_id}')";
                    $objDB->query($sql);
                    echo $objDB->getLastInsertID();
                    break;
                }
                default:
                {
                    $sql="select * from auieo_profiles where site_id=".$_SESSION["CATS"]->getSiteID();
                    $arrAssoc=$objDB->getAllAssoc($sql);
                    foreach($arrAssoc as $ind=>$assoc)
                    {
                        $arrAssoc[$ind]["delete"]="Delete";
                    }
                    echo json_encode($arrAssoc);
                }
            }
            exit;
        }
    }
    
    /*
     * Called by render() to process loading the user details page.
     */
    public function showSite()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_siteID > 1 || $this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }
        $privledged = true;

        $siteID = $_GET['siteID'];

        $site = new Site($this->_siteID);
        $data = $site->get($siteID);

        if (empty($data))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'No Site found with selected ID.');
        }
        $this->_template->assign('site_id', $siteID);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->assign('data', $data);
        $this->_template->display('./modules/settings/ShowSite.php');
    }

    /*
     * Called by render() to process loading the user details page.
     */
    public function showUser()
    {
        // FIXME: Does $_GET['userID'] exist?
        if (isset($_GET['privledged']) &&  $_GET['privledged'] == 'false' &&
            $this->_userID == $_GET['userID'])
        {
            $privledged = false;
        }
        else
        {
            /* Bail out if the user doesn't have SA permissions. */
            if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
            {
                CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
                return;
                //$this->fatal(ERROR_NO_PERMISSION);
            }

            $privledged = true;
        }

        $userID = $_GET['userID'];

        $users = new Users($this->_siteID);
        $data = $users->get($userID);

        if (empty($data))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'No user found with selected ID.');
        }

        $data['successfulDate'] = DateUtility::fixZeroDate(
            $data['successfulDate'], 'Never'
        );

        $data['unsuccessfulDate'] = DateUtility::fixZeroDate(
            $data['unsuccessfulDate'], 'Never'
        );

        $accessLevels = $users->getAccessLevels();

        $loginAttempts = $users->getLastLoginAttempts(
            $userID, self::MAX_RECENT_LOGINS
        );

        if (!empty($loginAttempts))
        {
            foreach ($loginAttempts as $rowIndex => $row)
            {
                $loginAttempts[$rowIndex]['shortUserAgent'] = implode(
                    ' ', BrowserDetection::detect($loginAttempts[$rowIndex]['userAgent'])
                );

                if ($loginAttempts[$rowIndex]['successful'] == 0)
                {
                    $loginAttempts[$rowIndex]['successful'] = 'No';
                }
                else
                {
                    $loginAttempts[$rowIndex]['successful'] = 'Yes';
                }
            }
        }

        $siteIDPosition = strpos($data['username'], '@' . $_SESSION['CATS']->getSiteID());

        // FIXME: The last test here might be redundant.
        if ($siteIDPosition !== false &&
            substr($data['username'], $siteIDPosition) == '@' . $_SESSION['CATS']->getSiteID())
        {
           $data['username'] = str_replace(
               '@' . $_SESSION['CATS']->getSiteID(), '', $data['username']
           );
        }

        /* Get user categories, if any. */
        $modules = ModuleUtility::getModules();
        $categories = array();
        foreach ($modules as $moduleName => $parameters)
        {
            $moduleCategories = $parameters[MODULE_SETTINGS_USER_CATEGORIES];

            if ($moduleCategories != false)
            {
                foreach ($moduleCategories as $category)
                {
                    $categories[] = $category;
                }
            }
        }

        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        $this->_template->assign('privledged', $privledged);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->assign('data', $data);
        $this->_template->assign('categories', $categories);
        $this->_template->assign('accessLevels', $accessLevels);
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('currentUser', $this->_userID);
        $this->_template->assign('loginDisplay', self::MAX_RECENT_LOGINS);
        $this->_template->assign('loginAttempts', $loginAttempts);
        $this->_template->display('./modules/settings/ShowUser.php');
    }
    
    /*
     * Called by render() to process loading the user add page.
     */
    public function addUser()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $users = new Users($this->_siteID);
        $accessLevels = $users->getAccessLevels();

        $rs = $users->getAll();
        $license = $users->getLicenseData();

        /* Get user categories, if any. */
        $modules = ModuleUtility::getModules();
        $categories = array();
        foreach ($modules as $moduleName => $parameters)
        {
            $moduleCategories = $parameters[MODULE_SETTINGS_USER_CATEGORIES];

            if ($moduleCategories != false)
            {
                foreach ($moduleCategories as $category)
                {
                    /* index 3 is the user level required to assign this type of category. */
                    if (!isset($category[3]) || $category[3] <= $this->_realAccessLevel)
                    {
                        $categories[] = $category;
                    }
                }
            }
        }

        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->assign('accessLevels', $accessLevels);
        $this->_template->assign('license', $license);
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('defaultAccessLevel', ACCESS_LEVEL_DELETE);
        $this->_template->assign('currentUser', $this->_userID);
        $this->_template->assign('categories', $categories);

        if (!eval(Hooks::get('SETTINGS_ADD_USER'))) return;

        $this->_template->display('./modules/settings/AddUser.php');
    }

    /*
     * Called by render() to process adding a user.
     */
    public function onAddUser()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $firstName      = $this->getTrimmedInput('firstName', $_POST);
        $lastName       = $this->getTrimmedInput('lastName', $_POST);
        $email          = $this->getTrimmedInput('email', $_POST);
        $username       = $this->getTrimmedInput('username', $_POST);
        $accessLevel    = $this->getTrimmedInput('accessLevel', $_POST);
        $password       = $this->getTrimmedInput('password', $_POST);
        $retypePassword = $this->getTrimmedInput('retypePassword', $_POST);
        $role           = $this->getTrimmedInput('role', $_POST);
        $eeoIsVisible   = $this->isChecked('eeoIsVisible', $_POST);
        $role        = $this->getTrimmedInput('role', $_POST);
        $roleid        = $this->getTrimmedInput('roleid', $_POST);
        $users = new Users($this->_siteID);
        $license = $users->getLicenseData();

        if (!$license['canAdd'] && $accessLevel > ACCESS_LEVEL_READ)
        {
            // FIXME: Shouldn't be a fatal, should go to ugprade
            $this->fatal(
                'You have no remaining user account allotments. Please upgrade your license or disable another user.'
            );
        }

        /* Bail out if any of the required fields are empty. */
        if (empty($firstName) || empty($lastName) || empty($username) ||
            empty($accessLevel) || empty($password) || empty($retypePassword))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
        }

        /* Bail out if the two passwords don't match. */
        if ($password !== $retypePassword)
        {
            CommonErrors::fatal(COMMONERROR_NOPASSWORDMATCH, $this, 'Passwords do not match.');
        }

        /* If adding an e-mail username, verify it is a valid e-mail. */
        if (strpos($username, '@') !== false && !eregi("^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})$", $username))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Username is in improper format for an E-Mail address.');
        }

        /* Make it a multisite user name if the user is part of a hosted site. */
        $unixName = $_SESSION['CATS']->getUnixName();
        if (strpos($username, '@') === false && !empty($unixName))
        {
           $username .= '@' . $_SESSION['CATS']->getSiteID();
        }

        /* Bail out if the specified username already exists. */
        if ($users->usernameExists($username))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'The specified username already exists.');
        }

        $userID = $users->add(
            $lastName, $firstName, $email, $username, $password, $accessLevel, $eeoIsVisible,$roleid
        );

        /* Check role (category) to make sure that the role is allowed to be set. */
        $modules = ModuleUtility::getModules();
        foreach ($modules as $moduleName => $parameters)
        {
            $moduleCategories = $parameters[MODULE_SETTINGS_USER_CATEGORIES];

            if ($moduleCategories != false)
            {
                foreach ($moduleCategories as $category)
                {
                    if ($category[1] == $role)
                    {
                        /* index 3 is the user level required to assign this type of category. */
                        if (!isset($category[3]) || $category[3] <= $this->_realAccessLevel)
                        {
                            /* Set this category. */
                            $users->updateCategories($userID, $role);
                        }
                    }
                }
            }
        }

        if ($userID <= 0)
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to add user.');
        }

        if (!eval(Hooks::get('SETTINGS_ON_ADD_USER'))) return;

        CATSUtility::transferRelativeURI(
            'm=settings&a=showUser&userID=' . $userID
        );
    }

    /*
     * Called by render() to process loading the user edit page.
     */
    public function editUser()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        /* Bail out if we don't have a valid user ID. */
        if (!$this->isRequiredIDValid('userID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid user ID.');
        }

        $userID = $_GET['userID'];

        $users = new Users($this->_siteID);
        $license = $users->getLicenseData();
        $accessLevels = $users->getAccessLevels();
        $data = $users->get($userID);

        if (empty($data))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'No user found with that ID.');
        }

        if ($this->_userID == $userID)
        {
            $disableAccessChange = true;
            $cannotEnableMessage = false;
        }
        else if (($data['accessLevel'] <= ACCESS_LEVEL_READ) && ($license['diff'] < 1) && ($license['userLicenses'] != 0))
        {
            $disableAccessChange = true;
            $cannotEnableMessage = true;
        }
        else
        {
            $disableAccessChange = false;
            $cannotEnableMessage = false;
        }

        /* Change multisite usernames into single site usernames. */
        // FIXME: The last test here might be redundant.
        // FIXME: Put this in a private method. It is duplicated twice so far.
        $siteIDPosition = strpos($data['username'], '@' . $_SESSION['CATS']->getSiteID());

        if ($siteIDPosition !== false &&
            substr($data['username'], $siteIDPosition) == '@' . $_SESSION['CATS']->getSiteID())
        {
           $data['username'] = str_replace(
               '@' . $_SESSION['CATS']->getSiteID(), '', $data['username']
           );
        }

        /* Get user categories, if any. */
        $modules = ModuleUtility::getModules();
        $categories = array();
        foreach ($modules as $moduleName => $parameters)
        {
            $moduleCategories = $parameters[MODULE_SETTINGS_USER_CATEGORIES];

            if ($moduleCategories != false)
            {
                foreach ($moduleCategories as $category)
                {
                    /* index 3 is the user level required to assign this type of category. */
                    if (!isset($category[3]) || $category[3] <= $this->_realAccessLevel)
                    {
                        $categories[] = $category;
                    }
                }
            }
        }

        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->assign('data', $data);
        $this->_template->assign('accessLevels', $accessLevels);
        $this->_template->assign('defaultAccessLevel', ACCESS_LEVEL_DELETE);
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('license', $license);
        $this->_template->assign('categories', $categories);
        $this->_template->assign('currentUser', $this->_userID);
        $this->_template->assign('cannotEnableMessage', $cannotEnableMessage);
        $this->_template->assign('disableAccessChange', $disableAccessChange);
        $this->_template->display('./modules/settings/editUser.php');
    }

    /*
     * Called by render() to process updating a user.
     */
    public function onEditUser()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        /* Bail out if we don't have a valid user ID. */
        if (!$this->isRequiredIDValid('userID', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid user ID.');
        }

        if ($this->isRequiredIDValid('accessLevel', $_POST, true))
        {
            $accessLevel = $_POST['accessLevel'];
        }
        else
        {
            $accessLevel = -1;
        }

        $userID = $_POST['userID'];

        $firstName   = $this->getTrimmedInput('firstName', $_POST);
        $lastName    = $this->getTrimmedInput('lastName', $_POST);
        $email       = $this->getTrimmedInput('email', $_POST);
        $username    = $this->getTrimmedInput('username', $_POST);
        $password1   = $this->getTrimmedInput('password1', $_POST);
        $password2   = $this->getTrimmedInput('password2', $_POST);
        $passwordRst = $this->getTrimmedInput('passwordIsReset', $_POST);
        $role        = $this->getTrimmedInput('role', $_POST);
        $roleid        = $this->getTrimmedInput('roleid', $_POST);
        $eeoIsVisible   = $this->isChecked('eeoIsVisible', $_POST);

        /* Bail out if any of the required fields are empty. */
        if (empty($firstName) || empty($lastName) || empty($username))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'First name, last name and username are required.');
        }

        /* Bail out if reseting password to null. */
        if (trim($password1) == '' && $passwordRst == 1)
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Cannot set a blank password.');
        }

        /* Bail out if the two passwords don't match. */
        if ($password1 !== $password2)
        {
            CommonErrors::fatal(COMMONERROR_NOPASSWORDMATCH, $this, 'Passwords do not match.');
        }

        /* Don't allow access level changes to the currently logged-in user's
         * account.
         */
        if ($userID == $this->_userID)
        {
            $accessLevel = $this->_realAccessLevel;
        }

        /* If adding an e-mail username, verify it is a valid e-mail. */
        // FIXME: PREG!
        if (strpos($username, '@') !== false && !eregi("^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})$", $username))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Username is in improper format for an E-Mail address.');
        }

        /* Make it a multisite user name if the user is part of a hosted site. */
        $unixName = $_SESSION['CATS']->getUnixName();
        if (strpos($username, '@') === false && !empty($unixName))
        {
           $username .= '@' . $_SESSION['CATS']->getSiteID();
        }

        $users = new Users($this->_siteID);

        if (!$users->update($userID, $lastName, $firstName, $email, $username,
            $accessLevel, $eeoIsVisible,$roleid))
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to update user.');
        }

        if (trim($password1) !== '')
        {
            /* Bail out if the password is 'cats'. */
            if ($password1 == 'cats')
            {
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'New password can not equal \'cats\'.');
            }

            if (!$users->resetPassword($userID, $password1))
            {
                CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to reset password.');
            }
        }

        /* Set categories. */
        $modules = ModuleUtility::getModules();
        $users->updateCategories($userID, '');
        foreach ($modules as $moduleName => $parameters)
        {
            $moduleCategories = $parameters[MODULE_SETTINGS_USER_CATEGORIES];

            if ($moduleCategories != false)
            {
                foreach ($moduleCategories as $category)
                {
                    if ($category[1] == $role)
                    {
                       /* index 3 is the user level required to assign this type of category. */
                        if (!isset($category[3]) || $category[3] <= $this->_realAccessLevel)
                        {
                            /* Set this category. */
                            $users->updateCategories($userID, $role);
                        }
                    }
                }
            }
        }

        CATSUtility::transferRelativeURI(
            'm=settings&a=showUser&userID=' . $userID
        );
    }

    /*
     * Called by render() to process loading the user add page.
     */
    public function addSite()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_siteID > 1 || $this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $sites = new Site($this->_siteID);

        $rs = $sites->getAll();

        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('defaultAccessLevel', ACCESS_LEVEL_DELETE);
        $this->_template->assign('currentUser', $this->_userID);
        $this->_template->assign('sites', $rs);

        if (!eval(Hooks::get('SETTINGS_ADD_USER'))) return;

        $this->_template->display('./modules/settings/AddSite.php');
    }

    /*
     * Called by render() to process adding a user.
     */
    public function onAddSite()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_siteID > 1 ||$this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $siteName      = $this->getTrimmedInput('siteName', $_POST);
        $unixName       = $this->getTrimmedInput('unixName', $_POST);
        $modelSite      = $this->getTrimmedInput('modelsite', $_POST);
        $isDemo   = $this->isChecked('isDemo', $_POST);
        $isDemo = empty($isDemo)?0:1;

        $eeoIsVisible   = $this->isChecked('eeoIsVisible', $_POST);

        $sites = new Site($this->_siteID);

        /* Bail out if any of the required fields are empty. */
        if (empty($siteName) || empty($unixName))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
        }

        /* Bail out if the specified username already exists. */
        if ($sites->getSiteByUnixName($unixName))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'The specified site already exists.');
        }

        $siteID = $sites->add(
            $unixName, $siteName, $isDemo, $modelSite
        );

        if ($siteID <= 0)
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to add site.');
        }

        CATSUtility::transferRelativeURI(
            'm=settings&a=showSite&siteID=' . $siteID
        );
    }

    /*
     * Called by render() to process loading the user edit page.
     */
    public function editSite()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_siteID > 1 ||$this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        /* Bail out if we don't have a valid user ID. */
        if (!$this->isRequiredIDValid('userID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid user ID.');
        }

        $userID = $_GET['userID'];

        $users = new Users($this->_siteID);
        $license = $users->getLicenseData();
        $accessLevels = $users->getAccessLevels();
        $data = $users->get($userID);

        if (empty($data))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'No user found with that ID.');
        }

        if ($this->_userID == $userID)
        {
            $disableAccessChange = true;
            $cannotEnableMessage = false;
        }
        else if (($data['accessLevel'] <= ACCESS_LEVEL_READ) && ($license['diff'] < 1) && ($license['userLicenses'] != 0))
        {
            $disableAccessChange = true;
            $cannotEnableMessage = true;
        }
        else
        {
            $disableAccessChange = false;
            $cannotEnableMessage = false;
        }

        /* Change multisite usernames into single site usernames. */
        // FIXME: The last test here might be redundant.
        // FIXME: Put this in a private method. It is duplicated twice so far.
        $siteIDPosition = strpos($data['username'], '@' . $_SESSION['CATS']->getSiteID());

        if ($siteIDPosition !== false &&
            substr($data['username'], $siteIDPosition) == '@' . $_SESSION['CATS']->getSiteID())
        {
           $data['username'] = str_replace(
               '@' . $_SESSION['CATS']->getSiteID(), '', $data['username']
           );
        }

        /* Get user categories, if any. */
        $modules = ModuleUtility::getModules();
        $categories = array();
        foreach ($modules as $moduleName => $parameters)
        {
            $moduleCategories = $parameters[MODULE_SETTINGS_USER_CATEGORIES];

            if ($moduleCategories != false)
            {
                foreach ($moduleCategories as $category)
                {
                    /* index 3 is the user level required to assign this type of category. */
                    if (!isset($category[3]) || $category[3] <= $this->_realAccessLevel)
                    {
                        $categories[] = $category;
                    }
                }
            }
        }

        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->assign('data', $data);
        $this->_template->assign('accessLevels', $accessLevels);
        $this->_template->assign('defaultAccessLevel', ACCESS_LEVEL_DELETE);
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('license', $license);
        $this->_template->assign('categories', $categories);
        $this->_template->assign('currentUser', $this->_userID);
        $this->_template->assign('cannotEnableMessage', $cannotEnableMessage);
        $this->_template->assign('disableAccessChange', $disableAccessChange);
        $this->_template->display('./modules/settings/EditUser.tpl');
    }

    /*
     * Called by render() to process updating a user.
     */
    public function onEditSite()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_siteID > 1 ||$this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        /* Bail out if we don't have a valid user ID. */
        if (!$this->isRequiredIDValid('userID', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid user ID.');
        }

        if ($this->isRequiredIDValid('accessLevel', $_POST, true))
        {
            $accessLevel = $_POST['accessLevel'];
        }
        else
        {
            $accessLevel = -1;
        }

        $userID = $_POST['userID'];

        $firstName   = $this->getTrimmedInput('firstName', $_POST);
        $lastName    = $this->getTrimmedInput('lastName', $_POST);
        $email       = $this->getTrimmedInput('email', $_POST);
        $username    = $this->getTrimmedInput('username', $_POST);
        $password1   = $this->getTrimmedInput('password1', $_POST);
        $password2   = $this->getTrimmedInput('password2', $_POST);
        $passwordRst = $this->getTrimmedInput('passwordIsReset', $_POST);
        $role        = $this->getTrimmedInput('role', $_POST);
        $eeoIsVisible   = $this->isChecked('eeoIsVisible', $_POST);

        /* Bail out if any of the required fields are empty. */
        if (empty($firstName) || empty($lastName) || empty($username))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'First name, last name and username are required.');
        }

        /* Bail out if reseting password to null. */
        if (trim($password1) == '' && $passwordRst == 1)
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Cannot set a blank password.');
        }

        /* Bail out if the two passwords don't match. */
        if ($password1 !== $password2)
        {
            CommonErrors::fatal(COMMONERROR_NOPASSWORDMATCH, $this, 'Passwords do not match.');
        }

        /* Don't allow access level changes to the currently logged-in user's
         * account.
         */
        if ($userID == $this->_userID)
        {
            $accessLevel = $this->_realAccessLevel;
        }

        /* If adding an e-mail username, verify it is a valid e-mail. */
        // FIXME: PREG!
        if (strpos($username, '@') !== false && !eregi("^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})$", $username))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Username is in improper format for an E-Mail address.');
        }

        /* Make it a multisite user name if the user is part of a hosted site. */
        $unixName = $_SESSION['CATS']->getUnixName();
        if (strpos($username, '@') === false && !empty($unixName))
        {
           $username .= '@' . $_SESSION['CATS']->getSiteID();
        }

        $users = new Users($this->_siteID);

        if (!$users->update($userID, $lastName, $firstName, $email, $username,
            $accessLevel, $eeoIsVisible))
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to update user.');
        }

        if (trim($password1) !== '')
        {
            /* Bail out if the password is 'cats'. */
            if ($password1 == 'cats')
            {
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'New password can not equal \'cats\'.');
            }

            if (!$users->resetPassword($userID, $password1))
            {
                CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to reset password.');
            }
        }

        /* Set categories. */
        $modules = ModuleUtility::getModules();
        $users->updateCategories($userID, '');
        foreach ($modules as $moduleName => $parameters)
        {
            $moduleCategories = $parameters[MODULE_SETTINGS_USER_CATEGORIES];

            if ($moduleCategories != false)
            {
                foreach ($moduleCategories as $category)
                {
                    if ($category[1] == $role)
                    {
                       /* index 3 is the user level required to assign this type of category. */
                        if (!isset($category[3]) || $category[3] <= $this->_realAccessLevel)
                        {
                            /* Set this category. */
                            $users->updateCategories($userID, $role);
                        }
                    }
                }
            }
        }

        CATSUtility::transferRelativeURI(
            'm=settings&a=showUser&userID=' . $userID
        );
    }

    /*
     * Called by render() to process deleting a user.
     *
     * This is only for automated testing right now. Deleting a user this way,
     * except for in special cases, will cause referential integrity problems.
     */
    public function onDeleteUser()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        /* Bail out if we don't have a valid user ID. */
        if (!$this->isRequiredIDValid('userID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid user ID.');
        }

        /* Keep users other than the automated tester from trying this. */
        if (!$this->isRequiredIDValid('iAmTheAutomatedTester', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'You are not the automated tester.');
        }

        $userID = $_GET['userID'];

        $users = new Users($this->_siteID);
        $users->delete($userID);

        CATSUtility::transferRelativeURI('m=settings&a=manageUsers');
    }

    /*
     * Called by render() to show the customize extra fields template.
     */
    public function customizeExtraFields()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $candidates = new Candidates($this->_siteID);
        $candidatesRS = $candidates->extraFields->getSettings();

        $contacts = new Contacts($this->_siteID);
        $contactsRS = $contacts->extraFields->getSettings();

        $companies = new Companies($this->_siteID);
        $companiesRS = $companies->extraFields->getSettings();

        $jobOrders = new JobOrders($this->_siteID);
        $jobOrdersRS = $jobOrders->extraFields->getSettings();

        $extraFieldTypes = $candidates->extraFields->getValuesTypes();

        $this->_template->assign('extraFieldSettingsCandidatesRS', $candidatesRS);
        $this->_template->assign('extraFieldSettingsContactsRS', $contactsRS);
        $this->_template->assign('extraFieldSettingsCompaniesRS', $companiesRS);
        $this->_template->assign('extraFieldSettingsJobOrdersRS', $jobOrdersRS);
        $this->_template->assign('extraFieldTypes', $extraFieldTypes);
        $this->_template->assign('active', $this);
        $this->_template->display('./modules/settings/CustomizeExtraFields.php');
    }

    /*
     * Called by render() to process the customize extra fields template.
     */
    public function onCustomizeExtraFields()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $extraFieldsMaintScript = $this->getTrimmedInput('commandList', $_POST);
        $extraFieldsMaintScriptArray = explode(',', $extraFieldsMaintScript);

        foreach($extraFieldsMaintScriptArray as $index => $commandEncoded)
        {
            $command = urldecode($commandEncoded);
            $args = explode(' ', $command);

            if (!isset($args[0]))
            {
                continue;
            }

            switch ($args[0])
            {
                case 'ADDFIELD':
                    $args = explode(' ', $command, 4);
                    $extraFields = new ExtraFields($this->_siteID, intval($args[1]));
                    $extraFields->define(urldecode($args[3]), intval($args[2]));
                    break;

                case 'DELETEFIELD':
                    $args = explode(' ', $command, 3);
                    $extraFields = new ExtraFields($this->_siteID, intval($args[1]));
                    $extraFields->remove(urldecode($args[2]));
                    break;

                case 'ADDOPTION':
                    $args = explode(' ', $command, 3);
                    $args2 = explode(':', $args[2]);

                    $extraFields = new ExtraFields($this->_siteID, intval($args[1]));
                    $extraFields->addOptionToColumn(urldecode($args2[0]), urldecode($args2[1]));
                    break;

                case 'DELETEOPTION':
                    $args = explode(' ', $command, 3);
                    $args2 = explode(':', $args[2]);

                    $extraFields = new ExtraFields($this->_siteID, intval($args[1]));
                    $extraFields->deleteOptionFromColumn(urldecode($args2[0]), urldecode($args2[1]));
                    break;

                case 'SWAPFIELDS':
                    $args = explode(' ', $command, 3);
                    $args2 = explode(':', $args[2]);

                    $extraFields = new ExtraFields($this->_siteID, intval($args[1]));
                    $extraFields->swapColumns(urldecode($args2[0]), urldecode($args2[1]));
                    break;

                case 'RENAMEROW':
                    $args = explode(' ', $command, 3);
                    $args2 = explode(':', $args[2]);

                    $extraFields = new ExtraFields($this->_siteID, intval($args[1]));
                    $extraFields->renameColumn(urldecode($args2[0]), urldecode($args2[1]));
                    break;
            }
        }

        CATSUtility::transferRelativeURI('m=settings&a=customizeExtraFields');
    }

    //FIXME: Document me.
    public function emailTemplates()
    {
        if(isset($_REQUEST["s"]))
        {
            $emailTemplates = new EmailTemplates($this->_siteID);
            $emailTemplates->delete($_REQUEST["id"]);
            CATSUtility::transferRelativeURI('m=settings&a=emailTemplates');
        }
        else
        {
            if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO && !$_SESSION['CATS']->hasUserCategory('careerportal'))
            {
                CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
                return;
                //$this->fatal(ERROR_NO_PERMISSION);
            }
            $objDatabase=  DatabaseConnection::getInstance();//trace($objDatabase);
            if(!$objDatabase->isFieldExist("email_template","basemodule"))
            {
                $objDatabase->addField("email_template","basemodule","VARCHAR",255);
            }
            $emailTemplates = new EmailTemplates($this->_siteID);
            $emailTemplatesRS = $emailTemplates->getAll();

            if (!eval(Hooks::get('SETTINGS_EMAIL_TEMPLATES'))) return;

            $this->_template->assign('active', $this);
            $this->_template->assign('subActive', 'Administration');
            $this->_template->assign('emailTemplatesRS', $emailTemplatesRS);
            $this->_template->display('./modules/settings/emailTemplates.php');
        }
    }

    public function templateVariables()
    {
        $objRequest=ClsNaanalRequest::getInstance();
        $templatemodule=$objRequest->getData("templatemodule");
        $templateID=$objRequest->getData("templateID");
        if(empty($templatemodule))
        {
            $template="";
            if(!empty($templateID))
            {
                $emailTemplates = new EmailTemplates($this->_siteID);
                $template=$emailTemplates->get($templateID);
                $template["text"]=  $template["text"];
                $template["templatemodule"]=  $template["baseModule"];
            }
            $arrTplVar=array();
            $arrTplVar = $template;
        }
        else
        {
            $arrModule=array();
            $arrModuleTable["candidates"]=array("module"=>"candidate","extra"=>"Candidate");
            $arrModuleTable["joborders"]=array("module"=>"joborder","extra"=>"Joborder");
            $arrModuleTable["contacts"]=array("module"=>"contact","extra"=>"Contact");
            $arrModuleTable["companies"]=array("module"=>"company","extra"=>"Company");
            $arrModuleTable["users"]=array("module"=>"user");
            $arrCandidateParentModule["contacts"]=array("contacts"=>"contacts","companies"=>"companies","users"=>"users");
            $arrCandidateParentModule["joborders"]=array("joborders"=>"joborders","companies"=>"companies","recruiter"=>"users","owner"=>"users");
            $arrCandidateParentModule["candidates"]=array("candidates"=>"candidates","joborders"=>"joborders","companies"=>"companies","users"=>"users");
            $arrTplVar=array();
         
            $allModules=$arrCandidateParentModule[$templatemodule];
            
            if($allModules)
            foreach($allModules as $foreignKey=>$amodule)
            {
                $tableName=$arrModuleTable[$amodule]["module"];
                $extraFieldTableName=isset($arrModuleTable[$amodule]["extra"])?$arrModuleTable[$amodule]["extra"]:"";
                $objDatabase = DatabaseConnection::getInstance();
                $sql="SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".DATABASE_NAME."' AND TABLE_NAME = '{$tableName}' order by COLUMN_NAME";
                $arrRow=$objDatabase->getAllAssoc($sql);
                $arrMainColumn=array();
                if($arrRow)
                foreach($arrRow as $row)
                {
                    $arrMainColumn[$row["COLUMN_NAME"]]=$row["COLUMN_NAME"];
                }
                asort($arrMainColumn,SORT_STRING);
                $arrTplVar[$foreignKey][$amodule]["main"]=$arrMainColumn;
                $arrExtraColumn=array();
                if($extraFieldTableName)
                {
                    $sql="select extra_field_settings_id,field_name from extra_field_settings left join data_item_type on data_item_type_id=data_item_type where short_description='{$extraFieldTableName}' order by field_name";
                    $arrRow=$objDatabase->getAllAssoc($sql);
                    if($arrRow)
                    foreach($arrRow as $row)
                    {
                        $arrExtraColumn["EXTRA_".$row["field_name"]]=$row["field_name"];
                    }
                    asort($arrExtraColumn,SORT_STRING);
                }
                if($arrExtraColumn)
                {
                    $arrTplVar[$foreignKey][$amodule]["extra"]=$arrExtraColumn;
                }
            }
            $arrTplVar[$foreignKey][$amodule]["other"]["currentTime"]="currentTime";
            if(empty($arrTplVar))
            {
                $arrTplVar=array();
                $arrTplVar["a"]="b";
            }
        }
        echo json_encode($arrTplVar);exit;
    }
    
    //FIXME: Document me.
    public function onEmailTemplates()
    {
        if (!isset($_POST['templateID']))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
        }
        if(empty($_POST['templateID']))
        {
            if ($this->_realAccessLevel < ACCESS_LEVEL_SA && !$_SESSION['CATS']->hasUserCategory('careerportal'))
            {
                CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
                return;
            }

            $useThisTemplate = isset($_POST['useThisTemplate']);
            $text = $this->getTrimmedInput('emailBody', $_POST);
            if ($useThisTemplate)
            {
                $disabled = 0;
            }
            else
            {
                $disabled = 1;
            }
            $emailTemplates = new EmailTemplates($this->_siteID);
            $emailTemplates->insert($_REQUEST["emailSubject"], $text, $_REQUEST["templatemodule"] ,$disabled);
        }
        else
        {
            if ($this->_realAccessLevel < ACCESS_LEVEL_SA && !$_SESSION['CATS']->hasUserCategory('careerportal'))
            {
                CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
                return;
                //$this->fatal(ERROR_NO_PERMISSION);
            }

            if (!$this->isRequiredIDValid('templateID', $_POST))
            {
                CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid template ID.');
            }

            $templateID = $_POST['templateID'];
            $useThisTemplate = isset($_POST['useThisTemplate']);
            
            $text = $this->getTrimmedInput('emailBody', $_POST);
            if ($useThisTemplate)
            {    
                $disabled = 0;
            }
            else
            {
                $disabled = 1;
            }
            $emailTemplates = new EmailTemplates($this->_siteID);
            $emailTemplates->update($templateID, $text, $disabled);
        }
        CATSUtility::transferRelativeURI('m=settings&a=emailTemplates');
    }

    /*
     * Called by render() to show a page with a message in the top frame
     * with a close window button.
     */
    public function previewPage()
    {
        $previewPage = $_GET['url'];
        $previewMessage = $_GET['message'];
        $this->_template->assign('previewPage', $previewPage);
        $this->_template->assign('previewMessage', $previewMessage);
        $this->_template->display('./modules/settings/PreviewPage.tpl');
    }

    /*
     * Called by render() to show the message in the top frame
     * with a close window button.
     */
    public function previewPageTop()
    {
        $previewMessage = $_GET['message'];
        $this->_template->assign('previewMessage', $previewMessage);
        $this->_template->display('./modules/settings/PreviewPageTop.tpl');
    }

    /*
     * Called by render() to show the careers website settings editor.
     */
    public function careerPortalTemplateEdit()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO && !$_SESSION['CATS']->hasUserCategory('careerportal'))
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
        }

        $templateName = $this->getTrimmedInput('templateName', $_GET);
        if (empty($templateName))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
        }

        $careerPortalSettings = new CareerPortalSettings($this->_siteID);

        $templateSource = $careerPortalSettings->getAllFromCustomTemplate($templateName);
        if (empty($templateSource))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'No custom template with that name exists.');
        }

        $templateBySetting = array();
        foreach ($templateSource as $templateLine)
        {
            $templateBySetting[$templateLine['setting']] = $templateLine['value'];
        }

        /* Arrange the array entries in a way that makes sense. */
        $desiredOrder = $careerPortalSettings->requiredTemplateFields;

        $template = array();
        foreach ($desiredOrder as $item)
        {
            if (isset($templateBySetting[$item]))
            {
                $template[$item] = $templateBySetting[$item];
            }
            else
            {
                $template[$item] = '';
            }
        }

        foreach ($templateBySetting as $item => $value)
        {
            if (!isset($template[$item]) && $item != '')
            {
                $template[$item] = $templateBySetting[$item];
            }
        }

        /* Get extra fields. */
        $jobOrders = new JobOrders($this->_siteID);
        $extraFieldsForJobOrders = $jobOrders->extraFields->getValuesForAdd();

        $candidates = new Candidates($this->_siteID);
        $extraFieldsForCandidates = $candidates->extraFields->getValuesForAdd();

        /* Get EEO settings. */
        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->assign('template', $template);
        $this->_template->assign('templateName', $templateName);
        $this->_template->assign('eeoEnabled', $EEOSettingsRS['enabled']);
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());
        $this->_template->assign('extraFieldsForJobOrders', $extraFieldsForJobOrders);
        $this->_template->assign('extraFieldsForCandidates', $extraFieldsForCandidates);
        //$this->_template->display('./modules/settings/CareerPortalTemplateEdit.php');
    }

    //FIXME: Document me.
    public function onCareerPortalTemplateEdit()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA && !$_SESSION['CATS']->hasUserCategory('careerportal'))
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
        }

        $templateName = $this->getTrimmedInput('templateName', $_POST);
        if (empty($templateName) || !isset($_POST['continueEdit']))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
        }

        $continueEdit = $_POST['continueEdit'];

        $careerPortalSettings = new CareerPortalSettings($this->_siteID);

        $templateSource = $careerPortalSettings->getAllFromCustomTemplate($templateName);

        // FIXME: Document this md5() stuff.
        foreach ($templateSource as $templateLine)
        {
            if ($templateLine['setting'] != '')
            {
                $careerPortalSettings->setForTemplate(
                    $templateLine['setting'],
                    $_POST[md5($templateLine['setting'])],
                    $templateName
                );
            }
        }

        foreach ($careerPortalSettings->requiredTemplateFields as $field)
        {
            if ($field != '' && isset($_POST[md5($field)]))
            {
                $careerPortalSettings->setForTemplate(
                    $field,
                    $_POST[md5($field)],
                    $templateName
                );
            }
        }

        if ($continueEdit == '1')
        {
            CATSUtility::transferRelativeURI(
                'm=settings&a=careerPortalTemplateEdit&templateName=' . urlencode($templateName)
            );
        }
        else
        {
            CATSUtility::transferRelativeURI(
                'm=settings&a=careerPortalSettings&templateName=' . urlencode($templateName)
            );
        }
    }

    /*
     * Called by render() to show the careers website settings template.
     */
    public function careerPortalSettings()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO && !$_SESSION['CATS']->hasUserCategory('careerportal'))
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
        }

        $careerPortalSettings = new CareerPortalSettings($this->_siteID);
        $careerPortalSettingsRS = $careerPortalSettings->getAll();
        $careerPortalTemplateNames = $careerPortalSettings->getDefaultTemplates();
        $careerPortalTemplateCustomNames = $careerPortalSettings->getCustomTemplates();

        $careerPortalURL = CATSUtility::getAbsoluteURI() . 'careers/';

        if (!eval(Hooks::get('SETTINGS_CAREER_PORTAL'))) return;

        $questionnaires = new Questionnaire($this->_siteID);
        $data = $questionnaires->getAll(true);

        $this->_template->assign('active', $this);
        $this->_template->assign('questionnaires', $data);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->assign('careerPortalSettingsRS', $careerPortalSettingsRS);
        $this->_template->assign('careerPortalTemplateNames', $careerPortalTemplateNames);
        $this->_template->assign('careerPortalTemplateCustomNames', $careerPortalTemplateCustomNames);
        $this->_template->assign('careerPortalURL', $careerPortalURL);
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());
        $this->_template->display('./modules/settings/CareerPortalSettings.php');
    }

    //FIXME: Document me.
    public function onCareerPortalSettings()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA && !$_SESSION['CATS']->hasUserCategory('careerportal'))
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
        }

        $careerPortalSettings = new CareerPortalSettings($this->_siteID);
        $careerPortalSettingsRS = $careerPortalSettings->getAll();

        foreach ($careerPortalSettingsRS as $setting => $value)
        {
            eval(Hooks::get('XML_FEED_SUBMISSION_SETTINGS_BODY'));
            if ($setting == 'enabled')
            {
                if ($this->isChecked($setting, $_POST))
                {
                    $careerPortalSettings->set($setting, '1');
                    if($value != '1')
                    {
                        CATSUtility::transferRelativeURI('m=settings&a=careerPortalSettings');
                    }
                }
                else
                {
                    $careerPortalSettings->set($setting, '0');
                    if($value != '0')
                    {
                        CATSUtility::transferRelativeURI('m=settings&a=careerPortalSettings');
                    }
                }
            }
            else if ($setting == 'allowBrowse')
            {
                if ($this->isChecked($setting, $_POST))
                {
                    $careerPortalSettings->set($setting, '1');
                }
                else
                {
                    $careerPortalSettings->set($setting, '0');
                }
            }
            else if ($setting == 'candidateRegistration')
            {
                if ($this->isChecked($setting, $_POST))
                {
                    $careerPortalSettings->set($setting, '1');
                }
                else
                {
                    $careerPortalSettings->set($setting, '0');
                }
            }
            else if ($setting == 'showDepartment')
            {
                if ($this->isChecked($setting, $_POST))
                {
                    $careerPortalSettings->set($setting, '1');
                }
                else
                {
                    $careerPortalSettings->set($setting, '0');
                }
            }
            else if ($setting == 'showCompany')
            {
                if ($this->isChecked($setting, $_POST))
                {
                    $careerPortalSettings->set($setting, '1');
                }
                else
                {
                    $careerPortalSettings->set($setting, '0');
                }
            }
            else
            {
                if (isset($_POST[$setting]))
                {
                    $careerPortalSettings->set($setting, $_POST[$setting]);
                }
            }
        }

        CATSUtility::transferRelativeURI('m=settings&a=administration');
    }

    public function onCareerPortalTweak()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA && !$_SESSION['CATS']->hasUserCategory('careerportal'))
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        if (!isset($_GET['p']))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid page.');
        }

        $page = $_GET['p'];

        $careerPortalSettings = new CareerPortalSettings($this->_siteID);

        switch ($page)
        {
            case 'new':
                $origName = 'Blank Page';
                $duplicateName = $this->getTrimmedInput('newName', $_POST);

                /* Copy default templates or existing customized templates from orig to duplicate. */
                $templateSource1 = $careerPortalSettings->getAllFromDefaultTemplate($origName);
                $templateSource2 = $careerPortalSettings->getAllFromCustomTemplate($origName);

                $templateSource = array_merge($templateSource1, $templateSource2);

                foreach ($templateSource as $setting)
                {
                    $careerPortalSettings->setForTemplate(
                        $setting['setting'],
                        $setting['value'],
                        $duplicateName
                    );
                }
                break;

            case 'duplicate':
                $origName      = $this->getTrimmedInput('origName', $_POST);
                $duplicateName = $this->getTrimmedInput('duplicateName', $_POST);

                if (empty($origName) || empty($duplicateName))
                {
                    CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
                }

                /* Copy default templates or existing customized templates from orig to duplicate. */
                $templateSource1 = $careerPortalSettings->getAllFromDefaultTemplate($origName);
                $templateSource2 = $careerPortalSettings->getAllFromCustomTemplate($origName);

                $templateSource = array_merge($templateSource1, $templateSource2);

                foreach ($templateSource as $setting)
                {
                    $careerPortalSettings->setForTemplate(
                        $setting['setting'],
                        $setting['value'],
                        $duplicateName
                    );
                }
                break;

            case 'delete':
                //FIXME: Input validation.
                $delName = $_POST['delName'];
                $careerPortalSettings->deleteCustomTemplate($delName);
                break;

            case 'setAsActive':
                //FIXME: Input validation.
                $activeName = $_POST['activeName'];
                $careerPortalSettings->set('activeBoard', $activeName);
                break;
        }

        CATSUtility::transferRelativeURI('m=settings&a=careerPortalSettings');
    }

    /*
     * Called by render() to show the careers website settings template.
     */
    public function EEOEOCSettings()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());
        $this->_template->display('./modules/settings/EEOEOCSettings.php');
    }

    //FIXME: Document me.
    public function onEEOEOCSettings()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        foreach ($EEOSettingsRS as $setting => $value)
        {
            if ($this->isChecked($setting, $_POST))
            {
                $EEOSettings->set($setting, '1');
            }
            else
            {
                $EEOSettings->set($setting, '0');
            }
        }

        CATSUtility::transferRelativeURI('m=settings&a=administration');
    }

    /*
     * Called by render() to show the e-mail settings template.
     */
    public function emailSettings()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $mailerSettings = new MailerSettings($this->_siteID);
        $mailerSettingsRS = $mailerSettings->getAll();

        $candidateJoborderStatusSendsMessage = unserialize($mailerSettingsRS['candidateJoborderStatusSendsMessage']);

        $emailTemplates = new EmailTemplates($this->_siteID);
        $emailTemplatesRS = $emailTemplates->getAll();

        $this->_template->assign('emailTemplatesRS', $emailTemplatesRS);
        $this->_template->assign('candidateJoborderStatusSendsMessage', $candidateJoborderStatusSendsMessage);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->assign('mailerSettingsRS', $mailerSettingsRS);
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());
        $this->_template->display('./modules/settings/EmailSettings.php');
    }

    /*
     * Called by render() to process the e-mail settings template.
     */
    public function onEmailSettings()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $mailerSettings = new MailerSettings($this->_siteID);
        $mailerSettingsRS = $mailerSettings->getAll();

        foreach ($mailerSettingsRS as $setting => $value)
        {
            if (isset($_POST[$setting]))
            {
                $mailerSettings->set($setting, $_POST[$setting]);
            }
        }

        $candidateJoborderStatusSendsMessage = unserialize($mailerSettingsRS['candidateJoborderStatusSendsMessage']);

        $candidateJoborderStatusSendsMessage[PIPELINE_STATUS_CONTACTED] = (UserInterface::isChecked('statusChangeContacted', $_POST) ? 1 : 0);
        $candidateJoborderStatusSendsMessage[PIPELINE_STATUS_CANDIDATE_REPLIED] = (UserInterface::isChecked('statusChangeReplied', $_POST) ? 1 : 0);
        $candidateJoborderStatusSendsMessage[PIPELINE_STATUS_QUALIFYING] = (UserInterface::isChecked('statusChangeQualifying', $_POST) ? 1 : 0);
        $candidateJoborderStatusSendsMessage[PIPELINE_STATUS_SUBMITTED] = (UserInterface::isChecked('statusChangeSubmitted', $_POST) ? 1 : 0);
        $candidateJoborderStatusSendsMessage[PIPELINE_STATUS_INTERVIEWING] = (UserInterface::isChecked('statusChangeInterviewing', $_POST) ? 1 : 0);
        $candidateJoborderStatusSendsMessage[PIPELINE_STATUS_OFFERED] = (UserInterface::isChecked('statusChangeOffered', $_POST) ? 1 : 0);
        $candidateJoborderStatusSendsMessage[PIPELINE_STATUS_CLIENTDECLINED] = (UserInterface::isChecked('statusChangeDeclined', $_POST) ? 1 : 0);
        $candidateJoborderStatusSendsMessage[PIPELINE_STATUS_PLACED] = (UserInterface::isChecked('statusChangePlaced', $_POST) ? 1 : 0);

        $mailerSettings->set('candidateJoborderStatusSendsMessage', serialize($candidateJoborderStatusSendsMessage));

        $emailTemplates = new EmailTemplates($this->_siteID);
        $emailTemplatesRS = $emailTemplates->getAll();

        foreach ($emailTemplatesRS as $index => $data)
        {
            $emailTemplates->updateIsActive($data['emailTemplateID'], (UserInterface::isChecked('useThisTemplate'.$data['emailTemplateID'], $_POST) ? 0 : 1));
        }

        $this->_template->assign('active', $this);
        CATSUtility::transferRelativeURI('m=settings&a=administration');
    }

    /*
     * Called by render() to show the customize calendar template.
     */
    public function customizeCalendar()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $calendarSettings = new CalendarSettings($this->_siteID);
        $calendarSettingsRS = $calendarSettings->getAll();

        $this->_template->assign('calendarSettingsRS', $calendarSettingsRS);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->display('./modules/settings/CustomizeCalendar.php');
    }


    /*
     * Called by render() to process the customize calendar template.
     */
    public function onCustomizeCalendar()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $calendarSettings = new CalendarSettings($this->_siteID);
        $calendarSettingsRS = $calendarSettings->getAll();

        foreach ($calendarSettingsRS as $setting => $value)
        {
            if ($setting == 'noAjax' || $setting == 'defaultPublic' || $setting == 'firstDayMonday')
            {
                if ($this->isChecked($setting, $_POST))
                {
                    $calendarSettings->set($setting, '1');
                }
                else
                {
                    $calendarSettings->set($setting, '0');
                }
            }
            else
            {
                if (isset($_POST[$setting]))
                {
                    $calendarSettings->set($setting, $_POST[$setting]);
                }
            }
        }

        $this->_template->assign('active', $this);
        CATSUtility::transferRelativeURI('m=settings&a=administration');
    }

    /*
     * Called by render() to show the customize reports template.
     */
    public function reports()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->display('./modules/settings/CustomizeReports.tpl');
    }

    /*
     * Called by render() to process loading new site pages.
     */
    public function newInstallPassword()
    {
        $this->_template->assign('inputType', 'password');
        $this->_template->assign('title', 'Create Administrator Password');
        $this->_template->assign('prompt', 'Congratulations! You have successfully logged onto CATS for the first time. Please create a new administrator password. Note that you cannot use \'cats\' as a password.');
        $this->_template->assign('action', $this->getAction());
        $this->_template->assign('home', 'home');
        $this->_template->display('./modules/settings/NewInstallWizard.tpl');
    }

    public function newSiteName()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            CATSUtility::transferRelativeURI('m=settings&a=newInstallFinished');
        }

        $this->_template->assign('inputType', 'siteName');
        $this->_template->assign('inputTypeTextParam', 'Please choose your site name.');
        $this->_template->assign('title', 'Site Name');
        $this->_template->assign('prompt', 'Your administrator password has been changed.<br /><br />Next, please create a name for your CATS installation (for example, MyCompany, Inc.). This will be displayed in the top right corner of all CATS pages.');
        $this->_template->assign('action', $this->getAction());
        $this->_template->assign('home', 'home');
        $this->_template->display('./modules/settings/NewInstallWizard.tpl');
    }

    public function upgradeSiteName()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            CATSUtility::transferRelativeURI('m=settings&a=newInstallFinished');
        }

        $this->_template->assign('inputType', 'siteName');
        $this->_template->assign('inputTypeTextParam', 'Site Name');
        $this->_template->assign('title', 'Site Name');
        $this->_template->assign('prompt', 'You have no site name defined. Please create a name for your CATS installation (for example, MyCompany, Inc.). This will be displayed in the top right corner of all CATS pages.');
        $this->_template->assign('action', $this->getAction());
        $this->_template->assign('home', 'home');
        $this->_template->display('./modules/settings/NewInstallWizard.tpl');
    }

    public function createBackup()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        /* Attachments */
        $attachments = new Attachments(CATS_ADMIN_SITE);
        $attachmentsRS = $attachments->getAll(
            DATA_ITEM_COMPANY, $_SESSION['CATS']->getSiteCompanyID()
        );

        foreach ($attachmentsRS as $index => $data)
        {
            $attachmentsRS[$index]['fileSize'] = fileUtility::sizeToHuman(
                filesize($data['retrievalURLLocal']), 2, 1
            );
        }

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->assign('attachmentsRS', $attachmentsRS);
        $this->_template->display('./modules/settings/Backup.php');
    }

    public function deleteBackup()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $attachments = new Attachments(CATS_ADMIN_SITE);
        $attachments->deleteAll(
            DATA_ITEM_COMPANY,
            $_SESSION['CATS']->getSiteCompanyID(),
            "AND content_type = 'catsbackup'"
        );

        CATSUtility::transferRelativeURI('m=settings&a=createBackup');
    }

    public function forceEmail()
    {
        $this->_template->assign('inputType', 'siteName');
        $this->_template->assign('inputTypeTextParam', 'E-Mail Address');
        $this->_template->assign('title', 'E-Mail Address');
        $this->_template->assign('prompt', 'CATS does not know what your e-mail address is for sending notifications. Please type your e-mail address in the box below.');
        $this->_template->assign('action', $this->getAction());
        $this->_template->assign('home', 'home');
        $this->_template->display('./modules/settings/NewInstallWizard.tpl');
    }

    public function onForceEmail()
    {
        $emailAddress = $this->getTrimmedInput('siteName', $_POST);

        if (empty($emailAddress))
        {
            $this->_template->assign('message', 'Please enter an e-mail address.');
            $this->_template->assign('messageSuccess', false);
            $this->forceEmail();
        }
        else
        {
            $site = new Users($this->_siteID);
            $site->updateSelfEmail($this->_userID, $emailAddress);

            $this->_template->assign('inputType', 'conclusion');
            $this->_template->assign('title', "E-Mail Address");
            $this->_template->assign('prompt', "Your e-mail settings have been saved. This concludes the CATS initial configuration wizard.");
            $this->_template->assign('action', $this->getAction());
            $this->_template->assign('home', 'home');
            $this->_template->display('./modules/settings/NewInstallWizard.tpl');
        }
    }

    public function newInstallFinished()
    {
        NewVersionCheck::checkForUpdate();

        $accessLevel = $_SESSION['CATS']->getAccessLevel();

        $mailerSettings = new MailerSettings($this->_siteID);
        $mailerSettingsRS = $mailerSettings->getAll();

        $this->_template->assign('inputType', 'conclusion');
        $this->_template->assign('title', 'Settings Saved');

        if ($mailerSettingsRS['configured'] == '0' &&
            $accessLevel >= ACCESS_LEVEL_SA)
        {
            $this->_template->assign('prompt', 'Your site name has been saved. This concludes the required CATS configuration wizard.<BR><BR><span style="font-weight: bold;">Warning:</span><BR><BR> E-mail features are disabled. In order to enable e-mail features (such as e-mail notifications), please configure your e-mail settings by clicking on the Settings tab and then clicking on Administration.');
        }
        else
        {
            $this->_template->assign('prompt', 'Your site name has been saved. This concludes the required CATS configuration wizard.');
        }

        $this->_template->assign('action', $this->getAction());
        $this->_template->assign('home', 'home');
        $this->_template->display('./modules/settings/NewInstallWizard.tpl');
    }

    /*
     * Called by render() to process handling new site pages.
     */
    public function onNewInstallPassword()
    {
        $error = '';

        $newPassword = $this->getTrimmedInput(
            'password1',
            $_POST
        );
        $retypeNewPassword = $this->getTrimmedInput(
            'password2',
            $_POST
        );

        /* Bail out if the two passwords don't match. */
        if ($retypeNewPassword !== $newPassword)
        {
            $error = 'New passwords do not match.';
        }

        /* Bail out if the password is 'cats'. */
        if ($newPassword == 'cats')
        {
            $error = 'New password cannot equal \'cats\'.';
        }

        /* Attempt to change the user's password. */
        if (!$error)
        {
            $users = new Users($this->_siteID);
            if ($users->changePassword($this->_userID, 'cats', $newPassword) != LOGIN_SUCCESS)
            {
                $error = 'Unable to reset password.';
            }
        }

        if ($error)
        {
            $this->_template->assign('message', $error);
            $this->_template->assign('messageSuccess', false);
            $this->newInstallPassword();
        }
        else
        {
            CATSUtility::transferRelativeURI('m=settings&a=newSiteName');
        }
    }

    public function onNewSiteName()
    {
        /* The user shouldn't be here if they are not an SA */
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            CATSUtility::transferRelativeURI('m=home');
            return;
        }

        $newSiteName = $this->getTrimmedInput('siteName', $_POST);

        if (empty($newSiteName) || $newSiteName === 'default_site')
        {
            $this->_template->assign('message', "Please enter a site name.");
            $this->_template->assign('messageSuccess', false);
            $this->upgradeSiteName();
        }
        else
        {
            $site = new Site($this->_siteID);
            $site->setName($newSiteName);

            $companies = new Companies($this->_siteID);
            $companyIDInternal = $companies->add(
                'Internal Postings', '', '', '', '', '', '', '', '', '', '',
                '', '', 'Internal postings.', $this->_userID, $this->_userID
            );

            $companies->setCompanyDefault($companyIDInternal);

            $_SESSION['CATS']->setSiteName($newSiteName);

            /* If no E-Mail set for current user, make user set E-Mail address. */
            if (trim($_SESSION['CATS']->getEmail()) == '')
            {
                CATSUtility::transferRelativeURI('m=settings&a=forceEmail');
            }
            else
            {
                CATSUtility::transferRelativeURI('m=settings&a=newInstallFinished');
            }
        }
    }

    public function onNewInstallFinished()
    {
        CATSUtility::transferRelativeURI('m=home');
    }

    /*
     * Called by render() to process loading the administration page.
     */
    public function administration()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO && !$_SESSION['CATS']->hasUserCategory('careerportal'))
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
        }

        $systemInfo = new SystemInfo();
        $systemInfoData = $systemInfo->getSystemInfo();

        if (isset($systemInfoData['available_version']) && $systemInfoData['available_version'] > CATSUtility::getVersionAsInteger())
        {
            $newVersion = true;
        }
        else
        {
            $newVersion = false;
        }

        if (isset($systemInfoData['disable_version_check']) && $systemInfoData['disable_version_check'])
        {
            $versionCheckPref = false;
        }
        else
        {
            $versionCheckPref = true;
        }

        if ($this->_realAccessLevel >= ACCESS_LEVEL_ROOT || $this->_realAccessLevel == ACCESS_LEVEL_DEMO)
        {
            $systemAdministration = true;
        }
        else
        {
            $systemAdministration = false;
        }

        // FIXME: 's' isn't a good variable name.
        if (isset($_GET['s']))
        {
            switch($_GET['s'])
            {
                case 'siteName':
                    $templateFile = './modules/settings/SiteName.php';
                    break;

                case 'newVersionCheck':
                    if (!$systemAdministration)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
                        return;
                        //$this->fatal(ERROR_NO_PERMISSION);
                    }

                    $this->_template->assign('versionCheckPref', $versionCheckPref);
                    $this->_template->assign('availableVersion', $systemInfoData['available_version']);
                    $this->_template->assign('newVersion', $newVersion);
                    $this->_template->assign('newVersionNews', NewVersionCheck::getNews());
                    $templateFile = './modules/settings/NewVersionCheck.php';
                    break;

                case 'passwords':
                    if (!$systemAdministration)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
                        return;
                        //$this->fatal(ERROR_NO_PERMISSION);
                    }

                    $templateFile = './modules/settings/Passwords.php';
                    break;

                case 'localization':
                    if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
                        return;
                        //$this->fatal(ERROR_NO_PERMISSION);
                    }

                    $this->_template->assign('timeZone', $_SESSION['CATS']->getTimeZone());
                    $this->_template->assign('isDateDMY', $_SESSION['CATS']->isDateDMY());
                    $templateFile = './modules/settings/Localization.php';
                    break;

                case 'systemInformation':
                    if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
                    {
                        CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
                        return;
                        //$this->fatal(ERROR_NO_PERMISSION);
                    }

                    $db = DatabaseConnection::getInstance();
                    $databaseVersion = $db->getRDBMSVersion();

                    $installationDirectory = realpath('./');

                    if (SystemUtility::isWindows())
                    {
                        $OSType = 'Windows';
                    }
                    else if (SystemUtility::isMacOSX())
                    {
                        $OSType = 'Mac OS X';
                    }
                    else
                    {
                        $OSType = 'UNIX';
                    }

                    $schemaVersions = ModuleUtility::getModuleSchemaVersions();

                    $this->_template->assign('databaseVersion', $databaseVersion);
                    $this->_template->assign('installationDirectory', $installationDirectory);
                    $this->_template->assign('OSType', $OSType);
                    $this->_template->assign('schemaVersions', $schemaVersions);
                    $templateFile = './modules/settings/SystemInformation.tpl';
                    break;

                default:
                    $templateFile = './modules/settings/Administration.php';
                    break;
            }
        }
        else
        {
            $templateFile = './modules/settings/Administration.php';

            /* Load extra settings. */
            $extraSettings = array();

            $modules = ModuleUtility::getModules();
            foreach ($modules as $moduleName => $parameters)
            {
                $extraSettingsModule = $parameters[MODULE_SETTINGS_ENTRIES];

                if ($extraSettingsModule != false)
                {
                    foreach ($extraSettingsModule as $extraSettingsModuleData)
                    {
                        if ($extraSettingsModuleData[2] <= $this->_realAccessLevel)
                        {
                            $extraSettings[] = $extraSettingsModuleData;
                        }
                    }
                }
            }
            $this->_template->assign('extraSettings', $extraSettings);
        }

        if (!strcmp($templateFile, './modules/settings/Administration.php'))
        {
            // Highlight certain rows of importance based on criteria
            $candidates = new Candidates($this->_siteID);
            $this->_template->assign('totalCandidates', $candidates->getCount());
        }

        if (!eval(Hooks::get('SETTINGS_DISPLAY_ADMINISTRATION'))) return;

        /* Check if careers website is enabled or can be enabled */
        $careerPortalUnlock = false;
        $careerPortalSettings = new CareerPortalSettings($this->_siteID);
        $cpData = $careerPortalSettings->getAll();
        if (intval($cpData['enabled']) || (file_exists('modules/asp') && !$_SESSION['CATS']->isFree()) ||
            LicenseUtility::isProfessional())
        {
            $careerPortalUnlock = true;
        }

        $this->_template->assign('careerPortalUnlock', $careerPortalUnlock);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->assign('systemAdministration', $systemAdministration);
        $this->_template->assign('active', $this);
        $this->_template->display($templateFile);
    }

    /*
     * Called by render() to process loading the administration page.
     */
    public function downloads()
    {
        //FIXME: This needs to give an appropriate error message to both Open Source and ASP Free users.
        //       The current message is geared toward Open Source users.
        if (!file_exists('modules/asp') && !LicenseUtility::isProfessional())
        {
            CommonErrors::fatal(COMMONERROR_RESTRICTEDEXTENSION, $this);
        }

        // FIXME: Temporary! We need a better error message.
        if ($_SESSION['CATS']->isFree() || $_SESSION['CATS']->isDemo())
        {
            CommonErrors::fatal(COMMONERROR_RESTRICTEDEXTENSION, $this);
        }

        // FIXME: 's' isn't a good variable name.
        if (isset($_GET['s']))
        {
            switch($_GET['s'])
            {
                case 'toolbar':
                    $templateFile = './modules/asp/toolbar.tpl';
                    break;

                default:
                    $templateFile = './modules/settings/AspDownloads.php';
                    break;
            }
        }
        else
        {
            $templateFile = './modules/settings/AspDownloads.php';
        }

        $this->_template->assign('isFree', $_SESSION['CATS']->isFree());
        $this->_template->assign('subActive', 'Extras');
        $this->_template->assign('active', $this);
        $this->_template->display($templateFile);
    }

    /*
     * Called by render() to process the administration page.
     */
    public function onAdministration()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $administrationMode = $this->getTrimmedInput(
            'administrationMode',
            $_POST
        );

        switch ($administrationMode)
        {
            case 'changeSiteName':
                $siteName = $this->getTrimmedInput(
                    'siteName',
                    $_POST
                );

                if (empty($siteName))
                {
                    CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
                }

                $this->changeSiteName($siteName);
                CATSUtility::transferRelativeURI('m=settings&a=administration');
                break;

            case 'changeVersionCheck':
                if ($this->_realAccessLevel < ACCESS_LEVEL_ROOT)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
                    return;
                    //$this->fatal(ERROR_NO_PERMISSION);
                }

                $this->changeNewVersionCheck(
                    $this->isChecked('versionCheck', $_POST)
                );

                $versionCheckPref = $this->isChecked('versionCheck', $_POST);
                CATSUtility::transferRelativeURI('m=settings&a=administration');
                break;

            case 'localization':
                if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
                {
                    CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
                    return;
                    //$this->fatal(ERROR_NO_PERMISSION);
                }


                //FIXME: Validation (escaped at lib level anyway)
                $timeZone = $_POST['timeZone'];
                $dateFormat = $_POST['dateFormat'];
                if ($dateFormat == 'mdy')
                {
                    $isDMY = false;
                }
                else
                {
                    $isDMY = true;
                }

                $site = new Site($this->_siteID);
                $site->setLocalization($timeZone, $isDMY);

                $_SESSION['CATS']->logout();
                unset($_SESSION['CATS']);

                CATSUtility::transferRelativeURI('?m=settings&a=administration&messageSuccess=true&message='.urlencode('Localization settings saved!  Please log back in for the settings to take effect.'));
                break;

            default:
                CATSUtility::transferRelativeURI('m=settings&a=administration');
                break;
        }
    }

    /*
     * Called by render to change localization settings at administrator login for ASP systems.
     */
    public function onAspLocalization()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        // FIXME: Input validation!

        $timeZone = $_POST['timeZone'];
        $dateFormat = $_POST['dateFormat'];
        if ($dateFormat == 'mdy')
        {
            $isDMY = false;
        }
        else
        {
            $isDMY = true;
        }

        $site = new Site($this->_siteID);
        $site->setLocalization($timeZone, $dateFormat);

        /* Reload the new data for the session. */
        $_SESSION['CATS']->setTimeDateLocalization($timeZone, $isDMY);

        $this->_template->assign('inputType', 'conclusion');
        $this->_template->assign('title', 'Localization Settings Saved!');
        $this->_template->assign('prompt', 'Your localization settings have been saved. This concludes the CATS initial configuration wizard.');
        $this->_template->assign('action', $this->getAction());
        $this->_template->assign('home', 'home');
        $this->_template->display('./modules/settings/NewInstallWizard.tpl');
    }

    /*
     * Called by Administration to change site name.
     */
    public function changeSiteName($newSiteName)
    {
        $site = new Site($this->_siteID);
        $site->setName($newSiteName);

        $_SESSION['CATS']->setSiteName($newSiteName);
        NewVersionCheck::checkForUpdate();
    }

    /*
     *  Called by Administration to change new version preferences.
     */
    public function changeNewVersionCheck($enableNewVersionCheck)
    {
        $systemInfo = new SystemInfo();
        $systemInfo->updateVersionCheckPrefs($enableNewVersionCheck);

        NewVersionCheck::checkForUpdate();
    }

    /*
     * Called by render() to process loading the site users page.
     */
    public function manageUsers()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        $users = new Users($this->_siteID);
        $rs = $users->getAll();
        $license = $users->getLicenseData();

        foreach ($rs as $rowIndex => $row)
        {
            $rs[$rowIndex]['successfulDate'] = DateUtility::fixZeroDate(
                $rs[$rowIndex]['successfulDate'], 'Never'
            );

            $rs[$rowIndex]['unsuccessfulDate'] = DateUtility::fixZeroDate(
                $rs[$rowIndex]['unsuccessfulDate'], 'Never'
            );

            // FIXME: The last test here might be redundant.
            // FIXME: Put this in a private method. It is duplicated twice so far.
            $siteIDPosition = strpos($row['username'], '@' .  $_SESSION['CATS']->getSiteID());

            if ($siteIDPosition !== false &&
                substr($row['username'], $siteIDPosition) == '@' . $_SESSION['CATS']->getSiteID())
            {
               $rs[$rowIndex]['username'] = str_replace(
                   '@' . $_SESSION['CATS']->getSiteID(), '', $row['username']
               );
            }
        }

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'User Management');
        $this->_template->assign('rs', $rs);
        $this->_template->assign('license', $license);
        $this->_template->display('./modules/settings/Users.php');
    }

    public function manageProfessional()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
        }
        if (ModuleUtility::moduleExists('asp') && (!defined('CATS_TEST_MODE') || !CATS_TEST_MODE))
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
        }

        $wf = new WebForm();
        $wf->addField('licenseKey', 'License Key', WFT_TEXT, true, 60, 30, 190, '', '/[A-Za-z0-9 ]+/',
            'That is not a valid license key!');
        $message = '';
        $license = new License();

        $upgradeStatus = false;

        if (isset($_GET['webFormPostBack']))
        {
            list ($fields, $errors) = $wf->getValidatedFields();
            if (count($errors) > 0) $message = 'Please enter a license key in order to continue.';

            $key = trim($fields['licenseKey']);

            $configWritten = false;

            if ($license->setKey($key) === false)
            {
                $message = 'That is not a valid license key<br /><span style="font-size: 16px; color: #000000;">Please verify that you have the correct key and try again.</span>';
            }
            else if ($license->isProfessional())
            {
                if (!CATSUtility::isSOAPEnabled())
                {
                    $message = 'CATS Professional requires the PHP SOAP library which isn\'t currently installed.<br /><br />'
                        . 'Installation Instructions:<br /><br />'
                        . 'WAMP/Windows Users:<dl>'
                        . '<li>Left click on the wamp icon.</li>'
                        . '<li>Select "PHP Settings" from the drop-down list.</li>'
                        . '<li>Select "PHP Extensions" from the drop-down list.</li>'
                        . '<li>Check the "php_soap" option.</li>'
                        . '<li>Restart WAMP.</li></dl>'
                        . 'Linux Users:<br /><br />'
                        . 'Re-install PHP with the --enable-soap configuration option.<br /><br />'
                        . 'Please visit http://www.catsone.com for more support options.';
                }
                /*if (!LicenseUtility::validateProfessionalKey($key))
                {
                    $message = 'That is not a valid Professional membership key<br /><span style="font-size: 16px; color: #000000;">Please verify that you have the correct key and try again.</span>';
                }
                else */if (!CATSUtility::changeConfigSetting('LICENSE_KEY', "'" . $key . "'"))
                {
                    $message = 'Internal Permissions Error<br /><span style="font-size: 12px; color: #000000;">CATS is unable '
                        . 'to write changes to your <b>config.php</b> file. Please change the file permissions or contact us '
                        . 'for support. Our support e-mail is <a href="mailto:support@catsone.com">support@catsone.com</a> '
                        . 'and our office number if (952) 417-0067.</span>';
                }
                else
                {
                    $upgradeStatus = true;
                }
            }
            else
            {
                $message = 'That is not a valid Professional membership key<br /><span style="font-size: 16px; color: #000000;">Please verify that you have the correct key and try again.</span>';
            }
        }

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Professional Membership');
        $this->_template->assign('message', $message);
        $this->_template->assign('upgradeStatus', $upgradeStatus);
        $this->_template->assign('webForm', $wf);
        $this->_template->assign('license', $license);
        $this->_template->display('./modules/settings/Professional.tpl');
    }

    /*
     * Called by render() to process changing a user's password.
     */
    public function onChangePassword()
    {
        /* Bail out if the user is demo. */
        if ($this->_realAccessLevel == ACCESS_LEVEL_DEMO || $this->_realAccessLevel == ENABLE_DEMO_MODE)
        {
            $this->fatal(
                'You are not allowed to change your password.'
            );
        }

        $logout = false;

        $currentPassword = $this->getTrimmedInput(
            'currentPassword', $_POST
        );
        $newPassword = $this->getTrimmedInput(
            'newPassword', $_POST
        );
        $retypeNewPassword = $this->getTrimmedInput(
            'retypeNewPassword', $_POST
        );

        /* Bail out if we don't have a current password. */
        if (empty($currentPassword))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid current password.');
        }

        /* Bail out if we don't have a new password. */
        if (empty($newPassword))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid new password.');
        }

        /* Bail out if we don't have a retyped new password. */
        if (empty($retypeNewPassword))
        {
            CommonErrors::fatal(COMMONERROR_NOPASSWORDMATCH, $this, 'Invalid retyped new password.');
        }

        /* Bail out if the two passwords don't match. */
        if ($retypeNewPassword !== $newPassword)
        {
            CommonErrors::fatal(COMMONERROR_NOPASSWORDMATCH, $this, 'Passwords do not match.');
        }

        /* Attempt to change the user's password. */
        $users = new Users($this->_siteID);
        $status = $users->changePassword(
            $this->_userID, $currentPassword, $newPassword
        );

        switch ($status)
        {
            case LOGIN_INVALID_PASSWORD:
                /* FIXME: No fatal()... we need a back button. */
                $error[] = 'The password that you specified for "Current Password" is incorrect.';
                break;

            case LOGIN_CANT_CHANGE_PASSWORD:
                /* FIXME: No fatal()... we need a back button. */
                $error[] = 'You are not allowed to change your password.';
                break;

            case LOGIN_INVALID_USER:
                $error[] = 'Your username appears to be invalid. Your password has not been changed and you have been logged out.';
                $messageSuccess = 'false';
                $logout = true;
                break;

            case LOGIN_DISABLED:
                $message = 'Your account is disabled. Your password cannot be changed and you have been logged out.';
                $messageSuccess = 'false';
                $logout = true;
                break;

            case LOGIN_SUCCESS:
                $message = 'Your password has been successfully changed. Please log in again using your new password.';
                $messageSuccess = 'true';
                $logout = true;
                break;

            default:
                $message = 'An unknown error occurred.';
                $messageSuccess = 'false';
                $logout = true;
                break;
        }

        if ($logout)
        {
            CATSUtility::transferRelativeURI(
                'm=logout&message=' . urlencode($message) .
                '&messageSuccess=' . urlencode($messageSuccess)
            );
        }
        else
        {
            $isDemoUser = $_SESSION['CATS']->isDemo();
            $this->_template->assign('userID', $this->_userID);
            $this->_template->assign('isDemoUser', $isDemoUser);

            $this->_template->assign('active', $this);
            $this->_template->assign('subActive', 'My Profile');
            $this->_template->assign('errorMessage', join('<br />', $error));
            $this->_template->display('./modules/settings/ChangePassword.php');
        }
    }

    /*
     * Called by render() to process loading the login activity page.
     */
    public function loginActivity()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        if (isset($_GET['view']) && !empty($_GET['view']))
        {
            $view = $_GET['view'];
        }
        else
        {
            $view = '';
        }

        if ($this->isRequiredIDValid('page', $_GET))
        {
            $currentPage = $_GET['page'];
        }
        else
        {
            $currentPage = 1;
        }

        switch ($view)
        {
            case 'unsuccessful':
                $successful = false;
                break;

            case 'successful':
            default:
                $successful = true;
        }

        $loginActivityPager = new LoginActivityPager(
            LOGIN_ENTRIES_PER_PAGE, $currentPage, $this->_siteID, $successful
        );

        if ($loginActivityPager->isSortByValid('sortBy', $_GET))
        {
            $sortBy = $_GET['sortBy'];
        }
        else
        {
            $sortBy = 'dateSort';
        }

        if ($loginActivityPager->isSortDirectionValid('sortDirection', $_GET))
        {
            $sortDirection = $_GET['sortDirection'];
        }
        else
        {
            $sortDirection = 'DESC';
        }

        $loginActivityPager->setSortByParameters(
            'm=settings&amp;a=loginActivity&amp;view=' . $view,
            $sortBy,
            $sortDirection
        );

        $currentPage       = $loginActivityPager->getCurrentPage();
        $totalPages        = $loginActivityPager->getTotalPages();
        $validSortByFields = $loginActivityPager->getSortByFields();

        $rs = $loginActivityPager->getPage();

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Login Activity');
        $this->_template->assign('rs', $rs);
        $this->_template->assign('currentPage', $currentPage);
        $this->_template->assign('totalPages', $totalPages);
        $this->_template->assign('pager', $loginActivityPager);
        $this->_template->assign('view', $view);
        $this->_template->display('./modules/settings/LoginActivity.php');
    }

    /*
     * Called by render() to process loading the item history page.
     */
    public function viewItemHistory()
    {
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
            //$this->fatal(ERROR_NO_PERMISSION);
        }

        /* Bail out if we don't have a valid data item type. */
        if (!$this->isRequiredIDValid('dataItemType', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid data item type.');
        }

        /* Bail out if we don't have a valid data item ID. */
        if (!$this->isRequiredIDValid('dataItemID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid data item ID.');
        }

        $dataItemType = $_GET['dataItemType'];
        $dataItemID   = $_GET['dataItemID'];

        switch ($dataItemType)
        {
            case DATA_ITEM_CANDIDATE:
                $candidates = new Candidates($this->_siteID);
                $data = $candidates->get($dataItemID);
                break;

            case DATA_ITEM_JOBORDER:
                $jobOrders = new JobOrders($this->_siteID);
                $data = $jobOrders->get($dataItemID);
                break;

            case DATA_ITEM_COMPANY:
                $companies = new Companies($this->_siteID);
                $data = $companies->get($dataItemID);
                break;

            case DATA_ITEM_CONTACT:
                $contacts = new Contacts($this->_siteID);
                $data = $contacts->get($dataItemID);
                break;

            default:
                CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid data item type.');
                break;
        }

        /* Get revision information. */
        $history = new History($this->_siteID);
        $revisionRS = $history->getAll($dataItemType, $dataItemID);

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Login Activity');
        $this->_template->assign('data', $data);
        $this->_template->assign('revisionRS', $revisionRS);
        $this->_template->display('./modules/settings/ItemHistory.php');
    }

    public function wizard_addUser()
    {
        if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
        {
            echo 'CATS has lost your session data!';
            return;
        }

        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            echo 'You do not have access to add a user.';
            return;
        }

        if (isset($_GET[$id = 'firstName'])) $firstName = $_GET[$id]; else $firstName = '';
        if (isset($_GET[$id = 'lastName'])) $lastName = $_GET[$id]; else $lastName = '';
        if (isset($_GET[$id = 'password'])) $password = $_GET[$id]; else $password = '';
        if (isset($_GET[$id = 'loginName'])) $loginName = $_GET[$id]; else $loginName = '';
        if (isset($_GET[$id = 'email'])) $email = $_GET[$id]; else $email = '';
        if (isset($_GET[$id = 'accessLevel']) && intval($_GET[$id]) < ACCESS_LEVEL_SA)
            $accessLevel = intval($_GET[$id]); else $accessLevel = ACCESS_LEVEL_READ;

        if (strlen($firstName) < 2 || strlen($lastName) < 2 || strlen($loginName) < 2 || strlen($password) < 2)
        {
            echo 'First and last name are too short.';
            return;
        }

        $users = new Users($this->_siteID);

        /* If adding an e-mail username, verify it is a valid e-mail. */
        if (strpos($loginName, '@') !== false && !eregi("^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})$", $loginName))
        {
            echo 'That is not a valid login name.';
            return;
        }

        /* Make it a multisite user name if the user is part of a hosted site. */
        $unixName = $_SESSION['CATS']->getUnixName();
        if (strpos($loginName, '@') === false && !empty($unixName))
        {
           $loginName .= '@' . $_SESSION['CATS']->getSiteID();
        }

        /* Bail out if the specified username already exists. */
        if ($users->usernameExists($loginName))
        {
            echo 'That username already exists.';
            return;
        }

        $data = $users->getLicenseData();
        if ($data['totalUsers'] >= $data['userLicenses'])
        {
            echo 'You cannot add any more users with your license.';
            return;
        }

        if ($users->add($lastName, $firstName, $email, $loginName, $password, $accessLevel, false) !== -1)
        {
            echo 'Ok';
            return;
        }
        else
        {
            echo 'Unable to add user. One of the fields you entered may have been formatted incorrectly.';
            return;
        }
    }

    public function wizard_deleteUser()
    {
        if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
        {
            echo 'CATS has lost your session!';
            return;
        }
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            echo 'You do not have access to delete a user.';
            return;
        }

        if (isset($_GET[$id = 'userID'])) $userID = intval($_GET[$id]);
        else
        {
            echo 'Unable to find the user you are trying to delete.';
            return;
        }

        if ($userID == $_SESSION['CATS']->getUserID())
        {
            echo 'You cannot delete yourself!';
            return;
        }

        $users = new Users($this->_siteID);
        $users->delete($userID);
        echo 'Ok';
    }

    public function wizard_checkKey()
    {
        $fileError = false;

        if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
        {
            echo 'CATS has lost your session!';
            return;
        }
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            echo 'You do not have access to set the key.';
            return;
        }

        if (isset($_GET[$id = 'key']) && $_GET[$id] != '')
        {
            $license = new License();
            $key = strtoupper(trim($_GET[$id]));

            $configWritten = false;

            if ($license->setKey($key) !== false)
            {
                if ($license->isProfessional())
                {
                    if (!CATSUtility::isSOAPEnabled())
                    {
                        echo "CATS Professional requires the PHP SOAP library which isn't currently installed.\n\n"
                            . "Installation Instructions:\n\n"
                            . "WAMP/Windows Users:\n"
                            . "1) Left click on the wamp icon.\n"
                            . "2) Select \"PHP Settings\" from the drop-down list.\n"
                            . "3) Select \"PHP Extensions\" from the drop-down list.\n"
                            . "4) Check the \"php_soap\" option.\n"
                            . "5) Restart WAMP.\n\n"
                            . "Linux Users:\n"
                            . "Re-install PHP with the --enable-soap configuration option.\n\n"
                            . "Please visit http://www.catsone.com for more support options.";
                        return;
                    }
                    else
                    {
                        /*if (!LicenseUtility::validateProfessionalKey($key))
                        {
                            echo "That is not a valid CATS Professional license key. Please visit "
                                . "http://www.catsone.com/professional for more information about CATS Professional.\n\n"
                                . "For a free open-source key, please visit http://www.catsone.com/ and "
                                . "click on \"Downloads\".";
                            return;
                        }*/
                    }
                }

                if (CATSUtility::changeConfigSetting('LICENSE_KEY', "'" . $key . "'"))
                {
                    $configWritten = true;
                }
            }

            if ($configWritten)
            {
                echo 'Ok';
                return;
            }
        }

        // The key hasn't been written. But they may have manually inserted the key into their config.php, check
        if (LicenseUtility::isLicenseValid())
        {
            echo 'Ok';
            return;
        }

        if ($fileError)
        {
            echo 'You entered a valid key, but this wizard is unable to write to your config.php file! You have '
                . 'two choices: ' . "\n\n"
                . '1) Change the file permissions of your config.php file.'."\n".'If you\'re using unix, try:' . "\n" . 'chmod 777 config.php' . "\n\n"
                . '2) Edit your config.php file manually and enter your valid key near this line: ' . "\n"
                . 'define(\'LICENSE_KEY\', \'ENTER YOUR KEY HERE\');' . "\n" . 'Once you\'ve done this, refresh your browser.' . "\n\n"
                . 'For more help, visit our website at http://www.catsone.com for support options.';
        }

        echo 'That is not a valid key. You can register for a free open source license key on our website '
            . 'at http://www.catsone.com or a professional key to unlock all of the available features at '
            . 'http://www.catsone.com/professional';
    }

    public function wizard_localization()
    {
        if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
        {
            echo 'CATS has lost your session!';
            return;
        }
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            echo 'You do not have access to change your localization settings.';
            return;
        }

        if (!isset($_GET['timeZone']) || !isset($_GET['dateFormat']))
        {
            echo 'You didn\'t provide a time zone or date format.';
            return;
        }

        $timeZone = $_GET['timeZone'];
        $dateFormat = $_GET['dateFormat'];
        if ($dateFormat == 'mdy')
        {
            $isDMY = false;
        }
        else
        {
            $isDMY = true;
        }

        $site = new Site($this->_siteID);
        $site->setLocalization($timeZone, $isDMY);
        $site->setLocalizationConfigured();

        echo 'Ok';
    }

    public function wizard_license()
    {
        if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
        {
            echo 'CATS has lost your session!';
            return;
        }
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            echo 'You do not have access to accept the license agreement.';
            return;
        }

        $site = new Site($this->_siteID);
        $site->setAgreedToLicense();

        echo 'Ok';
    }

    public function wizard_firstTimeSetup()
    {
        if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
        {
            echo 'CATS has lost your session!';
            return;
        }
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            echo 'You do not has access to this first-time-setup wizard.';
            return;
        }

        $site = new Site($this->_siteID);
        $site->setFirstTimeSetup();

        echo 'Ok';
    }

    public function wizard_password()
    {
        if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
        {
            echo 'CATS has lost your session!';
            exit;
        }
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            echo 'You do not have acess to set the site password.';
            exit;
        }

        if (isset($_GET['password']) && !empty($_GET['password'])) $password = $_GET['password'];
        else $password = '';

        if (strlen($password) < 5)
        {
            echo 'Your password length must be at least 5 characters long.';
            exit;
        }

        $users = new Users($this->_siteID);
        if ($users->changePassword($this->_userID, 'cats', $password) != LOGIN_SUCCESS)
        {
            echo 'Cannot change your site password!';
            exit;
        }

        echo 'Ok';exit;
    }

    public function wizard_email()
    {
        if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
        {
            echo 'CATS has lost your session!';
            exit;
        }

        if (isset($_GET['email']) && !empty($_GET['email'])) $email = $_GET['email'];
        else $email = '';

        if (strlen($email) < 5)
        {
            echo 'Your e-mail address must be at least 5 characters long.';
            exit;
        }

        $site = new Users($this->_siteID);
        $site->updateSelfEmail($this->_userID, $email);

        echo 'Ok';exit;
    }

    public function wizard_siteName()
    {
        if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
        {
            echo 'CATS has lost your session!';
            exit;
        }
        /* Bail out if the user doesn't have SA permissions. */
        if ($this->_realAccessLevel < ACCESS_LEVEL_SA)
        {
            echo 'You do not have permission to change the site name.';
            exit;
        }

        if (isset($_GET['siteName']) && !empty($_GET['siteName'])) $siteName = $_GET['siteName'];
        else $siteName = '';

        if ($siteName == 'default_site' || strlen($siteName) <= 0)
        {
            echo 'That is not a valid site name. Please choose a different one.';
            exit;
        }

        $site = new Site($this->_siteID);
        $site->setName($siteName);

        $companies = new Companies($this->_siteID);
        $companyIDInternal = $companies->add(
            'Internal Postings', '', '', '', '', '', '', '', '', '', '',
            '', '', 'Internal postings.', $this->_userID, $this->_userID
        );

        $companies->setCompanyDefault($companyIDInternal);

        $_SESSION['CATS']->setSiteName($siteName);

        echo 'Ok';exit;
    }

    public function wizard_import()
    {
        if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
        {
            echo 'CATS has lost your session!';
            return;
        }

        $siteID = $_SESSION['CATS']->getSiteID();

        // Echos Ok to redirect to the import stage, or Fail to go to home module
        $files = ImportUtility::getDirectoryFiles(FileUtility::getUploadPath($siteID, 'massimport'));

        if (count($files)) echo 'Ok';
        else echo 'Fail';
    }

    public function wizard_website()
    {
        if (!isset($_SESSION['CATS']) || empty($_SESSION['CATS']))
        {
            echo 'CATS has lost your session!';
            return;
        }

        $website = trim(isset($_GET[$id='website']) ? $_GET[$id] : '');
        if (strlen($website) > 10)
        {
            if (!eval(Hooks::get('SETTINGS_CP_REQUEST'))) return;
        }

        echo 'Ok';
    }

    public function careerPortalQuestionnaire($fromPostback = false)
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
        }

        // Get the ID if provided, otherwise we're adding a questionnaire
        $questionnaireID = isset($_GET[$id='questionnaireID']) ? $_GET[$id] : '';

        $questions = array();

        if (!$fromPostback)
        {
            $title = $description = '';
            $isActive = 1;

            // If questionairreID is provided, this is an edit
            if ($questionnaireID != '')
            {
                $questionnaire = new Questionnaire($this->_siteID);
                if (count($data = $questionnaire->get($questionnaireID)))
                {
                    $questions = $questionnaire->getQuestions($questionnaireID);

                    for ($i=0; $i<count($questions); $i++)
                    {
                        $questions[$i]['questionTypeLabel'] = $questionnaire->convertQuestionConstantToType(
                            $questions[$i]['questionType']
                        );
                    }

                    $this->_template->assign('title', $title = $data['title']);
                    $this->_template->assign('description', $description = $data['description']);
                    $this->_template->assign('isActive', $isActive = $data['isActive']);
                    $this->_template->assign('questions', $questions);
                }
                else
                {
                    $questionnaireID = '';
                }
            }

            // Store the questionnaire in a sesssion. That way we can make post changes
            // without changing the database data. Only save the session to the DB if the
            // user requests it.
            if (isset($_SESSION['CATS_QUESTIONNAIRE'])) unset($_SESSION['CATS_QUESTIONNAIRE']);
            $_SESSION['CATS_QUESTIONNAIRE'] = array(
                'id' => $questionnaireID,
                'title' => $title,
                'description' => $description,
                'questions' => $questions,
                'isActive' => $isActive
            );
        }
        else
        {
            // This is being called from a postback, so we're actively working out of the
            // session. Postback will handle saves.
            if (!isset($_SESSION['CATS_QUESTIONNAIRE']) || empty($_SESSION['CATS_QUESTIONNAIRE']))
            {
                CommonErrors::fatal(COMMONERROR_BADINDEX, 'Please return to your careers website '
                    . 'and load the questionnaire a second time as your session has '
                    . 'expired.');
            }

            // Save/restore the scroll position of the page
            $scrollX = isset($_POST[$id = 'scrollX']) ? $_POST[$id] : '';
            $scrollY = isset($_POST[$id = 'scrollY']) ? $_POST[$id] : '';

            $questions = $_SESSION['CATS_QUESTIONNAIRE']['questions'];
            $questionnaireID = $_SESSION['CATS_QUESTIONNAIRE']['id'];

            $this->_template->assign('scrollX', $scrollX);
            $this->_template->assign('scrollY', $scrollY);
            $this->_template->assign('title', $_SESSION['CATS_QUESTIONNAIRE']['title']);
            $this->_template->assign('description', $_SESSION['CATS_QUESTIONNAIRE']['description']);
            $this->_template->assign('isActive', $_SESSION['CATS_QUESTIONNAIRE']['isActive']);
            $this->_template->assign('questions', $questions);
        }

        $this->_template->assign('questionnaireID', $questionnaireID);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', '');
        $this->_template->display('./modules/settings/CareerPortalQuestionnaire.php');
    }

    public function onCareerPortalQuestionnaire()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
        }

        if (!isset($_SESSION['CATS_QUESTIONNAIRE']) || empty($_SESSION['CATS_QUESTIONNAIRE']))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, 'Please return to your careers website '
                . 'and load the questionnaire a second time as your session has '
                . 'expired.');
        }

        // Get the title
        $title = isset($_POST[$id = 'title']) ? substr(trim($_POST[$id]), 0, 255) : '';
        if (!strlen($title)) $title = '';

        // Get the description
        $description = isset($_POST[$id = 'description']) ? substr(trim($_POST[$id]), 0, 255) : '';
        if (!strlen($description)) $description = '';

        // Is this active?
        $active = isset($_POST[$id = 'isActive']) ? !strcasecmp($_POST[$id], 'yes') : 0;

        $_SESSION['CATS_QUESTIONNAIRE']['title'] = $title;
        $_SESSION['CATS_QUESTIONNAIRE']['description'] = $description;
        $_SESSION['CATS_QUESTIONNAIRE']['isActive'] = $active ? true : false;

        $questionnaire = new Questionnaire($this->_siteID);
        $questions = $_SESSION['CATS_QUESTIONNAIRE']['questions'];

        /**
         * STEP 1
         * Check for changes to question and answer texts, mark questions or
         * answers that the user specified to remove as "remove" which will be done
         * in the final step to prevent index changes.
         */
        for ($questionIndex=0; $questionIndex<count($questions); $questionIndex++)
        {
            // Update the position of the question
            $field = sprintf('question%dPosition', $questionIndex);
            if (isset($_POST[$field]))
            {
                $position = intval(trim($_POST[$field]));
                $questions[$questionIndex]['questionPosition'] = $position;
            }

            // Update the text of the question
            $field = sprintf('question%dTextValue', $questionIndex);
            if (isset($_POST[$field]))
            {
                if (strlen($text = substr(trim($_POST[$field]), 0, 255)))
                {
                    $questions[$questionIndex]['questionText'] = $text;
                }
            }

            // Update the type of the question
            $field = sprintf('question%dTypeValue', $questionIndex);
            if (isset($_POST[$field]))
            {
                $type = $questionnaire->convertQuestionTypeToConstant($_POST[$field]);
                $questions[$questionIndex]['questionType'] = $type;
                $questions[$questionIndex]['questionTypeLabel'] = (
                    $questionnaire->convertQuestionConstantToType($type)
                );
            }

            // Check if this question should be removed (user checked the box)
            $field = sprintf('question%dRemove', $questionIndex);
            if (isset($_POST[$field]) && !strcasecmp($_POST[$field], 'yes'))
            {
                $questions[$questionIndex]['remove'] = true;
            }
            else
            {
                $questions[$questionIndex]['remove'] = false;
            }

            for ($answerIndex=0; $answerIndex<count($questions[$questionIndex]['answers']); $answerIndex++)
            {
                // Update the position of the question
                $field = sprintf('question%dAnswer%dPosition', $questionIndex, $answerIndex);
                if (isset($_POST[$field]))
                {
                    $position = intval(trim($_POST[$field]));
                    $questions[$questionIndex]['answers'][$answerIndex]['answerPosition'] = $position;
                }

                // Update the text of the answer
                $field = sprintf('question%dAnswer%dTextValue', $questionIndex, $answerIndex);
                if (isset($_POST[$field]))
                {
                    if (strlen($text = substr(trim($_POST[$field]), 0, 255)))
                    {
                        $questions[$questionIndex]['answers'][$answerIndex]['answerText'] = $text;
                    }
                }

                // Check if this answer should be removed (user checked the box)
                $field = sprintf('question%dAnswer%dRemove', $questionIndex, $answerIndex);
                if (isset($_POST[$field]) && !strcasecmp($_POST[$field], 'yes'))
                {
                    $questions[$questionIndex]['answers'][$answerIndex]['remove'] = true;
                }
                else
                {
                    $questions[$questionIndex]['answers'][$answerIndex]['remove'] = false;
                }

                // Check the actions for whether or not they should exist
                $actionSourceField = sprintf('question%dAnswer%dActionSource',
                    $questionIndex, $answerIndex
                );
                $actionNotesField = sprintf('question%dAnswer%dActionNotes',
                    $questionIndex, $answerIndex
                );
                $actionIsHotField = sprintf('question%dAnswer%dActionIsHot',
                    $questionIndex, $answerIndex
                );
                $actionIsActiveField = sprintf('question%dAnswer%dActionIsActive',
                    $questionIndex, $answerIndex
                );
                $actionCanRelocateField = sprintf('question%dAnswer%dActionCanRelocate',
                    $questionIndex, $answerIndex
                );
                $actionKeySkillsField = sprintf('question%dAnswer%dActionKeySkills',
                    $questionIndex, $answerIndex
                );

                $actionSourceActive = isset($_POST[$id = $actionSourceField . 'Active']) ? $_POST[$id] : '';
                $actionNotesActive = isset($_POST[$id = $actionNotesField . 'Active']) ? $_POST[$id] : '';
                $actionIsHotActive = isset($_POST[$id = $actionIsHotField . 'Active']) ? $_POST[$id] : '';
                $actionIsActiveActive = isset($_POST[$id = $actionIsActiveField . 'Active']) ? $_POST[$id] : '';
                $actionCanRelocateActive = isset($_POST[$id = $actionCanRelocateField . 'Active']) ? $_POST[$id] : '';
                $actionKeySkillsActive = isset($_POST[$id = $actionKeySkillsField . 'Active']) ? $_POST[$id] : '';

                $actionSourceValue = isset($_POST[$id = $actionSourceField . 'Value']) ? $_POST[$id] : '';
                $actionNotesValue = isset($_POST[$id = $actionNotesField . 'Value']) ? $_POST[$id] : '';
                $actionIsHotValue = isset($_POST[$id = $actionIsHotField . 'Value']) ? $_POST[$id] : '';
                $actionIsActiveValue = isset($_POST[$id = $actionIsActiveField . 'Value']) ? $_POST[$id] : '';
                $actionCanRelocateValue = isset($_POST[$id = $actionCanRelocateField . 'Value']) ? $_POST[$id] : '';
                $actionKeySkillsValue = isset($_POST[$id = $actionKeySkillsField . 'Value']) ? $_POST[$id] : '';

                $questions[$questionIndex]['answers'][$answerIndex]['actionSource'] = (
                    strcasecmp($actionSourceActive, 'yes') ?
                    '' :
                    $actionSourceValue
                );
                $questions[$questionIndex]['answers'][$answerIndex]['actionNotes'] = (
                    strcasecmp($actionNotesActive, 'yes') ?
                    '' :
                    $actionNotesValue
                );
                $questions[$questionIndex]['answers'][$answerIndex]['actionIsHot'] = (
                    strcasecmp($actionIsHotActive, 'yes') ?
                    0 :
                    1
                );
                $questions[$questionIndex]['answers'][$answerIndex]['actionIsActive'] = (
                    strcasecmp($actionIsActiveActive, 'yes') ?
                    1 :
                    0
                );
                $questions[$questionIndex]['answers'][$answerIndex]['actionCanRelocate'] = (
                    strcasecmp($actionCanRelocateActive, 'yes') ?
                    0 :
                    1
                );
                $questions[$questionIndex]['answers'][$answerIndex]['actionKeySkills'] = (
                    strcasecmp($actionKeySkillsActive, 'yes') ?
                    '' :
                    $actionKeySkillsValue
                );
            }
        }

        /**
         * STEP 2
         * Perform addition requests like add question, answer or action. We do this before
         * performing the removal step because if a user removes a question and adds a answer
         * to it in the same step, the indexes will be misaligned. This way, the addition is
         * processed and then immediately removed if requested by the user (which is naughty).
         */
        $restrictAction = isset($_POST[$id = 'restrictAction']) ? $_POST[$id] : '';
        $restrictQuestionID = isset($_POST[$id = 'restrictActionQuestionID']) ? intval($_POST[$id]) : '';
        $restrictAnswerID = isset($_POST[$id = 'restrictActionAnswerID']) ? intval($_POST[$id]) : '';

        if (!strcasecmp($restrictAction, 'question'))
        {
            // Adding a new question to the questionnaire
            $questionText = isset($_POST[$id = 'questionText']) ? trim($_POST[$id]) : '';
            $questionTypeText = isset($_POST[$id = 'questionType']) ? $_POST[$id] : '';

            // Make sure the question doesn't already exist (re-submit)
            for ($i = 0, $exists = false; $i < count($questions); $i++)
            {
                if (!strcmp($questions[$i]['questionText'], $questionText))
                {
                    $exists = true;
                }
            }

            if (strlen($questionText) && !$exists)
            {
                $questions[] = array(
                    'questionID' => -1, // -1 indicates a record needs to be added
                    'questionType' => QUESTIONNAIRE_QUESTION_TYPE_TEXT,
                    'questionTypeLabel' =>
                        $questionnaire->convertQuestionConstantToType(QUESTIONNAIRE_QUESTION_TYPE_TEXT),
                    'questionText' => $questionText,
                    'minimumLength' => 0,
                    'maximumLength' => 255,
                    'questionPosition' => 1000, // should be positioned last (users can't enter higher than 999)
                    'answers' => array()
                );
            }
        }
        else if (!strcasecmp($restrictAction, 'answer') &&
            isset($questions[$restrictQuestionID]))
        {
            // Adding a new answer to an existing question
            $field = sprintf('question%dAnswerText', $restrictQuestionID);
            $answerText = substr(trim(isset($_POST[$field]) ? $_POST[$field] : ''), 0, 255);

            if (strlen($answerText))
            {
                $questions[$restrictQuestionID]['answers'][] = array(
                    'answerID' => -1, // append to the db
                    'answerText' => $answerText,
                    'actionSource' => '',
                    'actionNotes' => '',
                    'actionIsHot' => 0,
                    'actionIsActive' => 1,
                    'actionCanRelocate' => 0,
                    'actionKeySkills' => '',
                    'answerPosition' => 1000 // should be positioned last (see above)
                );
            }
        }
        else if (!strcasecmp($restrictAction, 'action') &&
            isset($questions[$restrictQuestionID]) &&
            isset($questions[$restrictQuestionID]['answers'][$restrictAnswerID]))
        {
            // Adding a new action to an existing answer of an existing question
            $field = sprintf('question%dAnswer%d', $restrictQuestionID, $restrictAnswerID);
            $newAction = isset($_POST[$id = $field . 'NewAction']) ? $_POST[$id] : '';
            $actionText = substr(trim(isset($_POST[$id = $field . 'NewActionText']) ? $_POST[$id] : ''), 0, 255);

            if (isset($questions[$restrictQuestionID]['answers'][$restrictAnswerID][$newAction]))
            {
                switch ($newAction)
                {
                    case 'actionSource': case 'actionNotes': case 'actionKeySkills':
                        $value = $actionText;
                        break;

                    case 'actionIsActive':
                        $value = 0;
                        break;

                    default:
                        $value = 1;
                        break;
                }

                $questions[$restrictQuestionID]['answers'][$restrictAnswerID][$newAction] = $value;
            }
        }

        /**
         * STEP 5
         * Remove any questions/answers that have "remove" checked prior to sorting/positioning
         */
        $savedQuestions = array();
        for ($questionIndex = 0, $savedQuestionIndex = 0;
             $questionIndex < count($questions);
             $questionIndex++)
        {
            if (isset($questions[$questionIndex]['remove']) && $questions[$questionIndex]['remove']) continue;
            $savedQuestions[$savedQuestionIndex] = $questions[$questionIndex];
            $savedQuestions[$savedQuestionIndex]['answers'] = array();

            for ($answerIndex = 0; $answerIndex < count($questions[$questionIndex]['answers']); $answerIndex++)
            {
                if (isset($questions[$questionIndex]['answers'][$answerIndex]['remove']) &&
                    $questions[$questionIndex]['answers'][$answerIndex]['remove']) continue;
                $savedQuestions[$savedQuestionIndex]['answers'][] =
                    $questions[$questionIndex]['answers'][$answerIndex];
            }

            $savedQuestionIndex++;
        }
        $questions = $savedQuestions;

        /**
         * STEP 6
         * Corrections. Any removals or changes that have altered the "way of things" need to
         * be fixed before sort.
         */
        for ($questionIndex = 0; $questionIndex < count($questions); $questionIndex++)
        {
            // If the question has no answers it is a TEXT automatically
            if (!count($questions[$questionIndex]['answers']))
            {
                $questions[$questionIndex]['questionType'] = QUESTIONNAIRE_QUESTION_TYPE_TEXT;
                $questions[$questionIndex]['questionTypeLabel'] =
                    $questionnaire->convertQuestionConstantToType(QUESTIONNAIRE_QUESTION_TYPE_TEXT);
            }
            // Otherwise, if there are answers, it cannot be a TEXT
            else if ($questions[$questionIndex]['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_TEXT)
            {
                $questions[$questionIndex]['questionType'] = QUESTIONNAIRE_QUESTION_TYPE_SELECT;
                $questions[$questionIndex]['questionTypeLabel'] =
                    $questionnaire->convertQuestionConstantToType(QUESTIONNAIRE_QUESTION_TYPE_SELECT);
            }
        }

        /**
         * STEP 7
         * Perform a bubble sort on the questions and answers. Then provide real values
         * (1, 2, 3) based on the results.
         */
        for ($questionIndex2 = 0;
             $questionIndex2 < count($questions);
             $questionIndex2++)
        {
            if ($questionIndex2 < count($questions) - 1)
            {
                for ($questionIndex3 = 0;
                     $questionIndex3 < count($questions) - 1;
                     $questionIndex3++)
                {
                    if (intval($questions[$questionIndex3]['questionPosition']) >
                        intval($questions[$questionIndex3+1]['questionPosition']))
                    {
                        $tmp = $questions[$questionIndex3];
                        $questions[$questionIndex3] = $questions[$questionIndex3+1];
                        $questions[$questionIndex3+1] = $tmp;
                    }
                }
            }

            // Bubble sort the answers for each question using the same method
            for ($answerIndex2 = 0;
                 $answerIndex2 < count($questions[$questionIndex2]['answers']) - 1;
                 $answerIndex2++)
            {
                for ($answerIndex3 = 0;
                     $answerIndex3 < count($questions[$questionIndex2]['answers']) - 1;
                     $answerIndex3++)
                {
                    if (intval($questions[$questionIndex2]['answers'][$answerIndex3]['answerPosition']) >
                        intval($questions[$questionIndex2]['answers'][$answerIndex3+1]['answerPosition']))
                    {
                        $tmp = $questions[$questionIndex2]['answers'][$answerIndex3];
                        $questions[$questionIndex2]['answers'][$answerIndex3] =
                            $questions[$questionIndex2]['answers'][$answerIndex3+1];
                        $questions[$questionIndex2]['answers'][$answerIndex3+1] = $tmp;
                    }
                }
            }
        }

        // Now define real position values (never trust the naughty user)
        for ($questionIndex2 = 0;
             $questionIndex2 < count($questions);
             $questionIndex2++)
        {
            $questions[$questionIndex2]['questionPosition'] = $questionIndex2 + 1;

            for ($answerIndex2 = 0;
                 $answerIndex2 < count($questions[$questionIndex2]['answers']);
                 $answerIndex2++)
            {
                $questions[$questionIndex2]['answers'][$answerIndex2]['answerPosition'] = ($answerIndex2 + 1);
            }
        }

        if (isset($_POST[$id = 'startOver']) && !strcasecmp($_POST[$id], 'yes'))
        {
            // User wants to start over
            $_SESSION['CATS_QUESTIONNAIRE']['questions'] = array();
        }
        else if (isset($_POST[$id = 'saveChanges']) && !strcasecmp($_POST[$id], 'yes'))
        {
            // User wants to add the new questionnaire
            if (($id = intval($_SESSION['CATS_QUESTIONNAIRE']['id'])) != 0)
            {
                $questionnaire->update(
                    $id, // the questionnaire id to update
                    $_SESSION['CATS_QUESTIONNAIRE']['title'],
                    $_SESSION['CATS_QUESTIONNAIRE']['description'],
                    $_SESSION['CATS_QUESTIONNAIRE']['isActive']
                );
            }
            // User is editting an existing questionnaire
            else
            {
                $id = $questionnaire->add(
                    $_SESSION['CATS_QUESTIONNAIRE']['title'],
                    $_SESSION['CATS_QUESTIONNAIRE']['description'],
                    $_SESSION['CATS_QUESTIONNAIRE']['isActive']
                );
            }

            if ($id !== false)
            {
                // Delete all existing questions/answers (replace with session values)
                $questionnaire->deleteQuestions($id);

                // Save the questions to the new or old questionnaire
                $questionnaire->addQuestions(
                    $id,
                    $_SESSION['CATS_QUESTIONNAIRE']['questions']
                );

                CATSUtility::transferRelativeURI('m=settings&a=careerPortalSettings');
                return;
            }
        }
        else
        {
            // Now save changes to the session
            $_SESSION['CATS_QUESTIONNAIRE']['questions'] = $questions;
        }

        // Now view the page as if we've just loaded it from the database
        $this->careerPortalQuestionnaire(true);
    }

    public function careerPortalQuestionnaireUpdate()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
            return;
        }

        $questionnaire = new Questionnaire($this->_siteID);
        $data = $questionnaire->getAll(true);

        for ($i = 0; $i < count($data); $i++)
        {
            if (isset($_POST[$id = 'removeQuestionnaire' . $i]) &&
                !strcasecmp($_POST[$id], 'yes'))
            {
                $questionnaire->delete($data[$i]['questionnaireID']);
            }
        }

        CATSUtility::transferRelativeURI('m=settings&a=careerPortalSettings');
    }

    public function careerPortalQuestionnairePreview()
    {
        if ($this->_realAccessLevel < ACCESS_LEVEL_DEMO)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this);
        }

        if (!isset($_GET['questionnaireID']))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX);
        }

        $questionnaireID = intval($_GET['questionnaireID']);
        $questionnaire = new Questionnaire($this->_siteID);
        $data = $questionnaire->get($questionnaireID);

        if (empty($data))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX);
        }

        $questions = $questionnaire->getQuestions($questionnaireID);

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Administration');
        $this->_template->assign('isModal', false);
        $this->_template->assign('questionnaireID', $questionnaireID);
        $this->_template->assign('data', $data);
        $this->_template->assign('questions', $questions);
        $this->_template->display('./modules/settings/CareerPortalQuestionnaireShow.tpl');
    }
}

?>
