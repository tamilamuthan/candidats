<?php
/*
 * Auieo Applicant Tracking System
 * Companies Module
 *
 * 
 *
 *
 * The contents of this file are subject to the Mozilla Public License
 * Version 2.0 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http:
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * 
 *
 * The Initial Developer of the Original Code is Unicomtech.
 * 
 * 
 * 
 *
 *
 * CompaniesUI.php 3460 2007-11-07 03:50:34Z brian $
 */

include_once('./lib/StringUtility.php');
include_once('./lib/DateUtility.php'); /* Depends on StringUtility. */
include_once('./lib/ResultSetUtility.php');
include_once('./lib/Companies.php');
include_once('./lib/Contacts.php');
include_once('./lib/JobOrders.php');
include_once('./lib/Attachments.php');
include_once('./lib/Export.php');
include_once('./lib/ListEditor.php');
include_once('./lib/FileUtility.php');
include_once('./lib/ExtraFields.php');
include_once('./lib/CommonErrors.php'); 

class ClsAsterisk extends UserInterface
{
    /* Maximum number of characters of the job notes to show without the user
     * clicking "[More]"
     */
    const NOTES_MAXLEN = 500;


    public function __construct()
    {
        parent::__construct();
        $this->_authenticationRequired = true;
        $this->_moduleDirectory = 'companies';
        $this->_moduleName = 'companies';
        $this->_moduleTabText = 'Companies';
        $this->_subTabs = array(
            'Add Company'     => URL_BASE_FILE_NAME . '?module=companies&amp;action=add*al='.ACCESS_LEVEL_EDIT.'*hrmode=0',
            'Search Companies' => URL_BASE_FILE_NAME . '?module=companies&amp;action=search*hrmode=0',
            'Go To My Company' => URL_BASE_FILE_NAME . '?module=companies&amp;action=internalPostings*hrmode=0'
        );
    }
    public function display($content)
    {
        print_r($content);
    }
    public function connect()
    {
        $errno=null;
        $errstr=null;
        $timeout=null;
        //$socket = fsockopen("49.248.213.20","5038") or die("unable to open socket");
        $socket = fsockopen("192.168.1.6","5038") or die("unable to open socket. ".print_r($socket,true));
        $content=fgets($socket,128);
        $this->display($content);
        fputs($socket, "Action: Login\r\n");
        $content=fgets($socket,128);
        $this->display($content);
        //fputs($socket, "UserName: ami_vTiger\r\n");
        fputs($socket, "UserName: tamil\r\n");
        $content=fgets($socket,128);
        $this->display($content);
        //fputs($socket, "Secret: ami_All15W3ll@Sun15En3rgy!\r\n\r\n");
        fputs($socket, "Secret: parthiban\r\n\r\n");
        $content=fgets($socket,128);
        $this->display($content);

        fputs($socket, "Action: Originate\r\n");
        $content=fgets($socket,128);
        $this->display($content);
        fputs($socket, "Channel: Local/loop@test\r\n");
        $content=fgets($socket,128);
        $this->display($content);
        fputs($socket, "Application: Playback\r\n");
        $content=fgets($socket,128);
        $this->display($content);
        fputs($socket, "Exten: demo-congrats\r\n");
        $content=fgets($socket,128);
        $this->display($content);
        //fputs($socket, "Context: international-record-welco\r\n");
        //fputs($socket, "Async: yes\r\n");
        //fputs($socket, "Priority: 1\r\n");
        //fputs($socket, "Async: yes\r\n");
        $wrets="";
        $loop=1;
        while($loop--)
        {
            $data=fgets($socket,128);
            $wrets=$wrets."<br />".$data;
        }
        trace($wrets);
    }

    public function render()
    {
        $action = $this->mode;

        if (!eval(Hooks::get('CLIENTS_HANDLE_REQUEST'))) return;

        switch ($action)
        {
            case 'show':
                return $this->show();

            case 'internalPostings':
                return $this->internalPostings();

            case 'add':
                if ($this->isPostBack())
                {
                    return $this->onAdd();
                }
                else
                {
                    return $this->add();
                }

            case 'edit':
                if ($this->isPostBack())
                {
                    return $this->onEdit();
                }
                else
                {
                    return $this->edit();
                }

            case 'delete':
                return $this->onDelete();

            case 'deleteSelected':
                return $this->deleteSelected();
            case 'sidemenu':
                return $this->sidemenu();
            /* Add an attachment */
            case 'createAttachment':
                include_once('./lib/DocumentToText.php');

                if ($this->isPostBack())
                {
                    return $this->onCreateAttachment();
                }
                else
                {
                    return $this->createAttachment();
                }

            /* Delete an attachment */
            case 'deleteAttachment':
                return $this->onDeleteAttachment();
            case 'webcall':
                return $this->webcall();
            case 'call':
                return $this->call();
            /* Main companies page. */
            case 'connect':
                return $this->connect();
                break;
            case 'search':
                break;
            default:
                return $this->onCreate();
        }
    }


    /*
     * Called by handleRequest() to process loading the list / main page.
     */
    private function listByView($errMessage = '')
    {
        /* First, if we are operating in HR mode we will never see the
           companies pager.  Immediantly forward to My Company. */

        /*if ($_SESSION['CATS']->isHrMode())
        {
            $this->internalPostings();
            die();
        }

        $dataGridProperties = DataGrid::getRecentParamaters("companies:CompaniesListByViewDataGrid");


        if ($dataGridProperties == array())
        {
            $dataGridProperties = array('rangeStart'    => 0,
                                        'maxResults'    => 15,
                                        'filterVisible' => false);
        }

        $dataGrid = DataGrid::get("companies:CompaniesListByViewDataGrid", $dataGridProperties);

        $arrTpl["active"]=$this;
        $arrTpl["dataGrid"]=$dataGrid;
        $arrTpl["userID"]=$_SESSION['CATS']->getUserID();
        $arrTpl["errMessage"]=$errMessage;
        $this->modifyAction("create");
        if (!eval(Hooks::get('CLIENTS_LIST_BY_VIEW'))) return;
        return $arrTpl;*/
        $this->modifyAction("search");
        return $this->onSearch();
    }

    /*
     * Called by handleRequest() to process loading the details page.
     */
    private function show()
    {
        loadScriptFiles("js/sorttable.js","sorttable.js","js");
        loadScriptFiles("js/attachment.js","attachment.js","js");
        //$companyID = $_GET['companyID'];
        $objWCompany=ClsWrapper::getInstance("companies");
        
        $objWCompany->fldID="company_id";
        $companyID=ClsNaanalRequest::getInstance()->getData("companyID");
        $success=$objWCompany->load($companyID);
        $companies = new Companies($this->_siteID);
        if (!$success)
        {
            $this->listByView('The specified company ID could not be found.');
            return;
        }
        $data=$companies->get($companyID);
        /* We want to handle formatting the city and state here instead
         * of in the template.
         */
        //$data=$objWCompany->getRow();
        $data['cityAndState'] = StringUtility::makeCityStateString(
            $data['city'], $data['state']
        );

        /*
         * Replace newlines with <br />, fix HTML "special" characters, and
         * strip leading empty lines and spaces.
         */
        $data['notes'] = trim(
            nl2br(htmlspecialchars($data['notes'], ENT_QUOTES))
        );

        /* Chop $data['notes'] to make $data['shortNotes']. */
        if (strlen($data['notes']) > self::NOTES_MAXLEN)
        {
            $data['shortNotes']  = substr(
                $data['notes'], 0, self::NOTES_MAXLEN
            );
            $isShortNotes = true;
        }
        else
        {
            $data['shortNotes'] = $data['notes'];
            $isShortNotes = false;
        }

        /* Hot companies [can] have different title styles than normal companies. */
        if ($data['isHot'] == 1)
        {
            $data['titleClass'] = 'jobTitleHot';
        }
        else
        {
            $data['titleClass'] = 'jobTitleCold';
        }

        /* Link to Google Maps for this address */
        if (!empty($data['address']) && !empty($data['city']) && !empty($data['state']))
        {
            $data['googleMaps'] = '<a href="http://maps.google.com/maps?q=' .
                     urlencode($data['address']) . '+' .
                     urlencode($data['city'])     . '+' .
                     urlencode($data['state']);

            /* Google Maps will find an address without Zip. */
            if (!empty($data['zip']))
            {
                $data['googleMaps'] .= '+' . $data['zip'];
            }

            $data['googleMaps'] .= '" target=_blank><img src="images/google_maps.gif" style="border: none;" class="absmiddle" /></a>';
        }
        else
        {
            $data['googleMaps'] = '';
        }

        /* Attachments */
        $attachments = new Attachments($this->_siteID);
        $attachmentsRS = $attachments->getAll(
            DATA_ITEM_COMPANY, $companyID
        );
        if($attachmentsRS)
        foreach ($attachmentsRS as $rowNumber => $attachmentsData)
        {
            /* Show an attachment icon based on the document's file type. */
            $attachmentIcon = strtolower(
                FileUtility::getAttachmentIcon(
                    $attachmentsRS[$rowNumber]['originalFilename']
                )
            );

            $attachmentsRS[$rowNumber]['attachmentIcon'] = $attachmentIcon;
        }

        /* Job Orders for this company */
        $jobOrders   = new JobOrders($this->_siteID);
        $jobOrdersRS = $jobOrders->getAll(
            JOBORDERS_STATUS_ALL, -1, $companyID, -1
        );

        if (!empty($jobOrdersRS))
        {
            foreach ($jobOrdersRS as $rowIndex => $row)
            {
                /* Convert '00-00-00' dates to empty strings. */
                $jobOrdersRS[$rowIndex]['startDate'] = DateUtility::fixZeroDate(
                    $jobOrdersRS[$rowIndex]['startDate']
                );

                /* Hot jobs [can] have different title styles than normal
                 * jobs.
                 */
                if ($jobOrdersRS[$rowIndex]['isHot'] == 1)
                {
                    $jobOrdersRS[$rowIndex]['linkClass'] = 'jobLinkHot';
                }
                else
                {
                    $jobOrdersRS[$rowIndex]['linkClass'] = 'jobLinkCold';
                }

                $jobOrdersRS[$rowIndex]['recruiterAbbrName'] = StringUtility::makeInitialName(
                    $jobOrdersRS[$rowIndex]['recruiterFirstName'],
                    $jobOrdersRS[$rowIndex]['recruiterLastName'],
                    false,
                    LAST_NAME_MAXLEN
                );

                $jobOrdersRS[$rowIndex]['ownerAbbrName'] = StringUtility::makeInitialName(
                    $jobOrdersRS[$rowIndex]['ownerFirstName'],
                    $jobOrdersRS[$rowIndex]['ownerLastName'],
                    false,
                    LAST_NAME_MAXLEN
                );
            }
        }

        /* Contacts for this company */
        $contacts   = new Contacts($this->_siteID);
        $contactsRS = $contacts->getAll(-1, $companyID);
        $contactsRSWC = null;

        if (!empty($contactsRS))
        {
            foreach ($contactsRS as $rowIndex => $row)
            {

                /* Hot contacts [can] have different title styles than normal contacts. */
                if ($contactsRS[$rowIndex]['isHot'] == 1)
                {
                    $contactsRS[$rowIndex]['linkClass'] = 'jobLinkHot';
                }
                else
                {
                    $contactsRS[$rowIndex]['linkClass'] = 'jobLinkCold';
                }

                if (!empty($contactsRS[$rowIndex]['ownerFirstName']))
                {
                    $contactsRS[$rowIndex]['ownerAbbrName'] = StringUtility::makeInitialName(
                        $contactsRS[$rowIndex]['ownerFirstName'],
                        $contactsRS[$rowIndex]['ownerLastName'],
                        false,
                        LAST_NAME_MAXLEN
                    );
                }
                else
                {
                    $contactsRS[$rowIndex]['ownerAbbrName'] = 'None';
                }

                if ($contactsRS[$rowIndex]['leftCompany'] == 0)
                {
                    $contactsRSWC[] = $contactsRS[$rowIndex];
                }
                else
                {
                    $contactsRS[$rowIndex]['linkClass'] = 'jobLinkDead';
                }
            }
        }

        /* Add an MRU entry. */
        $_SESSION['CATS']->getMRU()->addEntry(
            DATA_ITEM_COMPANY, $companyID, $data['name']
        );

        /* Get extra fields. */
        $extraFieldRS = $companies->extraFields->getValuesForShow($companyID);

        /* Get departments. */
        $departmentsRS = $companies->getDepartments($companyID);

        /* Is the user an admin - can user see history? */
        if ($this->_accessLevel < ACCESS_LEVEL_DEMO)
        {
            $privledgedUser = false;
        }
        else
        {
            $privledgedUser = true;
        }

        $arrTpl["active"]=$this;
        $arrTpl["data"]=$data;
        $arrTpl["attachmentsRS"]=$attachmentsRS;
        $arrTpl["departmentsRS"]=$departmentsRS;
        $arrTpl["extraFieldRS"]=$extraFieldRS;
        $arrTpl["isShortNotes"]=$isShortNotes;
        $arrTpl["jobOrdersRS"]=$jobOrdersRS;
        $arrTpl["contactsRS"]=$contactsRS;
        $arrTpl["contactsRSWC"]=$contactsRSWC;
        $arrTpl["privledgedUser"]=$privledgedUser;
        $arrTpl["companyID"]=$companyID;

        if (!eval(Hooks::get('CLIENTS_SHOW'))) return;

        return $arrTpl;
    }

    /*
     * Called by handleRequest() to process loading the internal postings company.
     */
    private function internalPostings()
    {
        $companies = new Companies($this->_siteID);
        $companyID = $companies->getDefaultCompany();

        header("Location:index.php?module=companies&action=show&companyID=" . $companyID);die();
    }

    /*
     * Called by handleRequest() to process loading the add page.
     */
    private function add()
    {
        $companies = new Companies($this->_siteID);

        /* Get extra fields. */
        $extraFieldRS = $companies->extraFields->getValuesForAdd();

        if (!eval(Hooks::get('CLIENTS_ADD'))) return;

        $arrTpl["extraFieldRS"]=$extraFieldRS;
        $arrTpl["active"]=$this;
        $arrTpl["subActive"]='Add Company';
        return $arrTpl;
    }
    
    public function sidemenu()
    {
        if(class_exists("SavedSearches"))
        {
            $savedSearches = new SavedSearches($this->_siteID);
            $savedSearchRS = $savedSearches->get(DATA_ITEM_COMPANY);
        }
        else
        {
            $savedSearchRS=false;
        }
        $mode = trim(ClsNaanalRequest::getInstance()->getData("mode"));
        $query = trim(ClsNaanalRequest::getInstance()->getData("wildCardString"));
        if(empty($query))
        {
            $mode="";
        }
        $arrTpl["savedSearchRS"]=$savedSearchRS;
        $arrTpl["mode"]=$mode;
        $arrTpl["wildCardString"]=$query;
        return $arrTpl;
    }

    /*
     * Called by handleRequest() to process saving / submitting the add page.
     */
    private function onAdd()
    {
        $objPost=ClsNaanalPost::getInstance();
        $formattedPhone1 = StringUtility::extractPhoneNumber(
            $objPost->getData("phone1")
        );
        if (!empty($formattedPhone1))
        {
            $phone1 = $formattedPhone1;
        }
        else
        {
            $phone1 = $objPost->getData("phone1");
        }

        $formattedPhone2 = StringUtility::extractPhoneNumber(
                $objPost->getData("phone2")
        );
        if (!empty($formattedPhone2))
        {
            $phone2 = $formattedPhone2;
        }
        else
        {
            $phone2 = $objPost->getData("phone2");
        }

        $formattedFaxNumber = StringUtility::extractPhoneNumber(
            $objPost->getData("faxNumber")
        );
        if (!empty($formattedFaxNumber))
        {
            $faxNumber = $formattedFaxNumber;
        }
        else
        {
            $faxNumber = $objPost->getData("faxNumber");
        }
        
        $url = $objPost->getData("url");
        if (!empty($url))
        {
            $formattedURL = StringUtility::extractURL($url);

            if (!empty($formattedURL))
            {
                $url = $formattedURL;
            }
        }

        /* Hot company? */
        $isHot = $objPost->getData("isHot");

        $name            = $objPost->getData("name");
        $address         = $objPost->getData("address");
        $city            = $objPost->getData("city");
        $state           = $objPost->getData("state");
        $zip             = $objPost->getData("zip");
        $keyTechnologies = $objPost->getData("keyTechnologies");
        $notes           = $objPost->getData("notes");

        /* Departments list editor. */
        $departmentsCSV = $objPost->getData("departmentsCSV");

        /* Bail out if any of the required fields are empty. */
        if (empty($name))
        {
            $this->listByView('Required fields are missing.');
            return;
        }

        if (!eval(Hooks::get('CLIENTS_ON_ADD_PRE'))) return;

        $companies = new Companies($this->_siteID);
        $companyID = $companies->add(
            $name, $address, $city, $state, $zip, $phone1,
            $phone2, $faxNumber, $url, $keyTechnologies, $isHot,
            $notes, $this->_userID, $this->_userID
        );

        if ($companyID <= 0)
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to add company.');
        }

        if (!eval(Hooks::get('CLIENTS_ON_ADD_POST'))) return;

        /* Update extra fields. */
        $arrUpdatedData=$companies->extraFields->setValuesOnEdit($companyID);
        
        /* Add departments */
        $departments = array();
        $departmentsDifferences = ListEditor::getDifferencesFromList(
            $departments, 'name', 'departmentID', $departmentsCSV
        );

        $companies->updateDepartments($companyID, $departmentsDifferences);
        $arrData=array("name"=>$name, "address"=>$address, "city"=>$city, "state"=>$state, "zip"=>$zip, "phone1"=>$phone1,
            "phone2"=>$phone2, "faxNumber"=>$faxNumber, "url"=>$url, "keyTechnologies"=>$keyTechnologies, "isHot"=>$isHot,
            "notes"=>$notes, "enteredBy"=>$this->_userID, "owner"=>$this->_userID);
        foreach($arrUpdatedData as $k=>$v)
        {
            $arrData[$k]=$v;
        }
        $arrData["companyID"]=$companyID;
        if($onActionHook=getAuieoHook("on_insert"))
        {
            $onActionHook($arrData);
        }
        header("Location:index.php?module=companies&action=show&companyID=" . $companyID);die();
    }

    /*
     * Called by handleRequest() to process loading the edit page.
     */
    private function edit()
    {
        /* Bail out if we don't have a valid company ID. */
        if (!$this->isRequiredIDValid('companyID', $_GET))
        {
            $this->listByView('Invalid company ID.');
            return;
        }

        $companyID = $_GET['companyID'];

        $companies = new Companies($this->_siteID);
        $data = $companies->getForEditing($companyID);

        /* Bail out if we got an empty result set. */
        if (empty($data))
        {
            $this->listByView('The specified company ID could not be found.');
            return;
        }

        /* Get the company's contacts data. */
        $contactsRS = $companies->getContactsArray($companyID);

        $users = new Users($this->_siteID);
        $usersRS = $users->getSelectList();

        /* Add an MRU entry. */
        $_SESSION['CATS']->getMRU()->addEntry(
            DATA_ITEM_COMPANY, $companyID, $data['name']
        );

        /* Get extra fields. */
        $extraFieldRS = $companies->extraFields->getValuesForEdit($companyID);

        /* Get departments. */
        $departmentsRS = $companies->getDepartments($companyID);
        $departmentsString = ListEditor::getStringFromList($departmentsRS, 'name');

        $emailTemplates = new EmailTemplates($this->_siteID);
        $statusChangeTemplateRS = $emailTemplates->getByTag(
            'EMAIL_TEMPLATE_OWNERSHIPASSIGNCLIENT'
        );

        if (!isset($statusChangeTemplateRS['disabled']) || $statusChangeTemplateRS['disabled'] == 1)
        {
            $emailTemplateDisabled = true;
        }
        else
        {
            $emailTemplateDisabled = false;
        }

        if ($this->_accessLevel == ACCESS_LEVEL_DEMO)
        {
            $canEmail = false;
        }
        else
        {
            $canEmail = true;
        }

        if (!eval(Hooks::get('CLIENTS_EDIT'))) return;

        $arrTpl["canEmail"]=$canEmail;
        $arrTpl["active"]=$this;
        $arrTpl["data"]=$data;
        $arrTpl["usersRS"]=$usersRS;
        $arrTpl["extraFieldRS"]=$extraFieldRS;
        $arrTpl["contactsRS"]=$contactsRS;
        $arrTpl["departmentsRS"]=$departmentsRS;
        $arrTpl["departmentsString"]=$departmentsString;
        $arrTpl["emailTemplateDisabled"]=$emailTemplateDisabled;
        $arrTpl["companyID"]=$companyID;
        return $arrTpl;
    }

    /*
     * Called by handleRequest() to process saving / submitting the edit page.
     */
    private function onEdit()
    {
        if ($this->_accessLevel < ACCESS_LEVEL_EDIT)
        {
            $this->listByView('Invalid user level for action.');
            return;
        }

        $companies = new Companies($this->_siteID);

        /* Bail out if we don't have a valid company ID. */
        if (!$this->isRequiredIDValid('companyID', $_POST))
        {
            $this->listByView('Invalid company ID.');
            return;
        }

        /* Bail out if we don't have a valid owner user ID. */
        if (!$this->isOptionalIDValid('owner', $_POST))
        {
            $this->listByView('Invalid owner user ID.');
            return;
        }

        /* Bail out if we don't have a valid billing contact ID. */
        if (!$this->isOptionalIDValid('billingContact', $_POST))
        {
            $this->listByView('Invalid billing contact ID.');
            return;
        }

        $formattedPhone1 = StringUtility::extractPhoneNumber(
            $this->getTrimmedInput('phone1', $_POST)
        );
        if (!empty($formattedPhone1))
        {
            $phone1 = $formattedPhone1;
        }
        else
        {
            $phone1 = $this->getTrimmedInput('phone1', $_POST);
        }

        $formattedPhone2 = StringUtility::extractPhoneNumber(
            $this->getTrimmedInput('phone2', $_POST)
        );
        if (!empty($formattedPhone2))
        {
            $phone2 = $formattedPhone2;
        }
        else
        {
            $phone2 = $this->getTrimmedInput('phone2', $_POST);
        }

        $formattedFaxNumber = StringUtility::extractPhoneNumber(
            $this->getTrimmedInput('faxNumber', $_POST)
        );
        if (!empty($formattedFaxNumber))
        {
            $faxNumber = $formattedFaxNumber;
        }
        else
        {
            $faxNumber = $this->getTrimmedInput('faxNumber', $_POST);
        }

        $url = $this->getTrimmedInput('url', $_POST);
        if (!empty($url))
        {
            $formattedURL = StringUtility::extractURL($url);

            if (!empty($formattedURL))
            {
                $url = $formattedURL;
            }
        }

        /* Hot company? */
        $isHot = $this->isChecked('isHot', $_POST);

        $companyID       = $_POST['companyID'];
        $owner           = $_POST['owner'];
        $billingContact  = $_POST['billingContact'];

        /* Change ownership email? */
        if ($this->isChecked('ownershipChange', $_POST) && $owner > 0)
        {
            $companyDetails = $companies->get($companyID);

            $users = new Users($this->_siteID);
            $ownerDetails = $users->get($_POST['owner']);

            if (!empty($ownerDetails))
            {
                $emailAddress = $ownerDetails['email'];

                /* Get the change status email template. */
                $emailTemplates = new EmailTemplates($this->_siteID);
                $statusChangeTemplateRS = $emailTemplates->getByTag(
                    'EMAIL_TEMPLATE_OWNERSHIPASSIGNCLIENT'
                );

                if (empty($statusChangeTemplateRS) ||
                    empty($statusChangeTemplateRS['textReplaced']))
                {
                    $statusChangeTemplate = '';
                }
                else
                {
                    $statusChangeTemplate = $statusChangeTemplateRS['textReplaced'];
                }
                /* Replace e-mail template variables. */
                $stringsToFind = array(
                    '%CLNTOWNER%',
                    '%CLNTNAME%',
                    '%CLNTCATSURL%'
                );
                $replacementStrings = array(
                    $ownerDetails['fullName'],
                    $companyDetails['name'],
                    '<a href="http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) . '?module=companies&amp;action=show&amp;companyID=' . $companyID . '">'.
                        'http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) . '?module=companies&amp;action=show&amp;companyID=' . $companyID . '</a>'
                );
                $statusChangeTemplate = str_replace(
                    $stringsToFind,
                    $replacementStrings,
                    $statusChangeTemplate
                );

                $email = $statusChangeTemplate;
            }
            else
            {
                $email = '';
                $emailAddress = '';
            }
        }
        else
        {
            $email = '';
            $emailAddress = '';
        }

        $name            = $this->getTrimmedInput('name', $_POST);
        $address         = $this->getTrimmedInput('address', $_POST);
        $city            = $this->getTrimmedInput('city', $_POST);
        $state           = $this->getTrimmedInput('state', $_POST);
        $zip             = $this->getTrimmedInput('zip', $_POST);
        $keyTechnologies = $this->getTrimmedInput('keyTechnologies', $_POST);
        $notes           = $this->getTrimmedInput('notes', $_POST);

        /* Departments list editor. */
        $departmentsCSV = $this->getTrimmedInput('departmentsCSV', $_POST);

        /* Bail out if any of the required fields are empty. */
        if (empty($name))
        {
            $this->listByView('Required fields are missing.');
            return;
        }

       if (!eval(Hooks::get('CLIENTS_ON_EDIT_PRE'))) return;

        $departments = $companies->getDepartments($companyID);
        $departmentsDifferences = ListEditor::getDifferencesFromList(
            $departments, 'name', 'departmentID', $departmentsCSV
        );
        $companies->updateDepartments($companyID, $departmentsDifferences);

        if (!$companies->update($companyID, $name, $address, $city, $state,
            $zip, $phone1, $phone2, $faxNumber, $url, $keyTechnologies,
            $isHot, $notes, $owner, $billingContact, $email, $emailAddress))
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to update company.');
        }

       if (!eval(Hooks::get('CLIENTS_ON_EDIT_POST'))) return;

        /* Update extra fields. */
        $arrUpdatedData=$companies->extraFields->setValuesOnEdit($companyID);

        /* Update contacts? */
        if (isset($_POST['updateContacts']))
        {
            if ($_POST['updateContacts'] == 'yes')
            {
                $contacts = new Contacts($this->_siteID);
                $contacts->updateByCompany($companyID, $address, $city, $state, $zip);
            }
        }
        
        $arrData=array("name"=>$name, "address"=>$address, "city"=>$city, "state"=>$state, "zip"=>$zip, "phone1"=>$phone1,
            "phone2"=>$phone2, "faxNumber"=>$faxNumber, "url"=>$url, "keyTechnologies"=>$keyTechnologies, "isHot"=>$isHot,
            "notes"=>$notes, "enteredBy"=>$this->_userID, "owner"=>$this->_userID);
        
        foreach($arrUpdatedData as $k=>$v)
        {
            $arrData[$k]=$v;
        }
        $arrData["companyID"]=$companyID;
        if($onActionHook=getAuieoHook("on_update"))
        {
            $onActionHook($arrData);
        }
        header("Location:index.php?module=companies&action=show&companyID=" . $companyID);die();
    }
    
    private function deleteSelected()
    {
        foreach($_REQUEST as $k=>$v)
        {
            if($v=="on")
            {
                $arrK=explode("_",$k);
                $id=$arrK[1];
                $companies = new Companies($this->_siteID);
                $companies->delete($id);
            }
        }
        header("Location:index.php?module={$_REQUEST["module"]}&action=search&getback=getback&mode={$_REQUEST["mode"]}&wildCardString={$_REQUEST["wildCardString"]}");exit;
    }

    /*
     * Called by handleRequest() to process deleting a company.
     */
    private function onDelete()
    {
        if ($this->_accessLevel < ACCESS_LEVEL_DELETE)
        {
            $this->listByView('Invalid user level for action.');
            return;
        }

        /* Bail out if we don't have a valid company ID. */
        if (!$this->isRequiredIDValid('companyID', $_GET))
        {
            $this->listByView('Invalid company ID.');
            return;
        }

        $companyID = $_GET['companyID'];

        $companies = new Companies($this->_siteID);
        $rs = $companies->get($companyID);

        if (empty($rs))
        {
            $this->listByView('The specified company ID could not be found.');
            return;
        }

        if ($rs['defaultCompany'] == 1)
        {
            $this->listByView('Cannot delete internal postings company.');
            return;
        }

       if (!eval(Hooks::get('CLIENTS_ON_DELETE_PRE'))) return;

        $companies->delete($companyID);

        /* Delete the MRU entry if present. */
        $_SESSION['CATS']->getMRU()->removeEntry(
            DATA_ITEM_COMPANY, $companyID
        );

       if (!eval(Hooks::get('CLIENTS_ON_DELETE_POST'))) return;
       header("Location:index.php?module=companies&action=listByView");die();
    }

    /*
     * Called by handleRequest() to process loading the search page.
     */
    private function search()
    {
        $savedSearches = new SavedSearches($this->_siteID);
        $savedSearchRS = $savedSearches->get(DATA_ITEM_COMPANY);

        if (!eval(Hooks::get('CLIENTS_SEARCH'))) return;

        $arrTpl["wildCardString"]='';
        $arrTpl["savedSearchRS"]=$savedSearchRS;
        $arrTpl["active"]=$this;
        $arrTpl["subActive"]='Search Companies';
        $arrTpl["isResultsMode"]=false;
        $this->_template->assign('wildCardCompanyName' , '');
        $arrTpl["wildCardKeyTechnologies"]='';
        $arrTpl["mode"]='';
        return $arrTpl;
    }
    
    private function onCreate()
    {
        $arrTpl=array();
        $arrTpl["hello"]="hello asterisk";
    }
    
    private function webcall()
    {
        $arrTpl=array();
        $arrTpl["hello"]="hello asterisk";
    }

    private function call()
    {
        $_REQUEST=ClsNaanalRequest::getInstance()->getAll();
        $sender = $_REQUEST ['sender'];
        $receiver = $_REQUEST ['receiver'];

        // Asterisk server IP
        $sys_ip = "49.248.213.20";
        //$sys_ip = "192.168.1.6";
        // username for manager
        $User_str = "ami_vTiger";
        // ... and 'is password
        $Secret_str = "ami_All15W3ll@Sun15En3rgy!";
        $our_exten = "SIP/$sender";

        $WaitTime = "30";
        $domain = "127.0.0.1";
        // this will be shown in sip-client if call goes to SIP/xxx
        $strCustdata = "Call to $receiver";
        $digit_len = strlen ($receiver);

        // default manager port - 5038, or one, which was set manager.conf
        $oSocket = fsockopen ($sys_ip, 5038, $errnum, $errdesc) or die ("Connection to host failed");
        sleep (1);

        fputs ($oSocket, "Action: login\r\n");
        fputs ($oSocket, "Username: $User_str\r\n");
        fputs ($oSocket, "Secret: $Secret_str\r\n\r\n");

        $wrets = fgets ($oSocket,128);

        echo "Autheticaton message: ".$wrets;

        fputs ($oSocket, "Events: off\r\n\r\n");
        echo "<br />Events: off. Output: ".fgets ($oSocket,128);
        fputs ($oSocket, "Action: originate\r\n");
        echo "<br />Action: originate. output: ".fgets ($oSocket,128);
        fputs ($oSocket, "Channel: $our_exten\r\n");
        echo "<br />Channel: $our_exten. Output: ".fgets ($oSocket,128);
        fputs ($oSocket, "WaitTime: $WaitTime\r\n");
        echo "<br />WaitTime: $WaitTime. Output: ".fgets ($oSocket,128);
        fputs ($oSocket, "CallerId: $strCustdata\r\n");
        echo "<br />CallerId: $strCustdata. Output: ".fgets ($oSocket,128);
        fputs ($oSocket, "Exten: $receiver\r\n");
        echo "<br />Exten: $receiver. Output: ".fgets ($oSocket,128);
        fputs ($oSocket, "Context: domestic-record-welco\r\n");
        //fputs ($oSocket, "Context: internal\r\n");
        echo "<br />Context: domestic-record-welco. Output: ".fgets ($oSocket,128);
        fputs ($oSocket, "Async: yes\r\n");
        echo "<br />Async: yes. Output: ".fgets ($oSocket,128);
        fputs ($oSocket, "Priority: 1\r\n\r\n");
        //echo "<br />Priority: 1. Output: ".fgets ($oSocket,128);
        fputs ($oSocket, "Action: Logoff\r\n\r\n");
        echo "<br />Action: Logoff. Output: ".fgets ($oSocket,128);
        sleep (2);
        fclose ($oSocket);
        exit;
    }
    
    /*
     * Called by handleRequest() to process displaying the search results.
     */
    private function onSearch()
    {
        $wildCardCompanyName = '';
        $wildCardKeyTechnologies = '';
        loadScriptFiles("js/export.js","export.js","js");
        $query = trim(ClsNaanalRequest::getInstance()->getData("wildCardString"));
        if ($this->isRequiredIDValid('page', $_GET))
        {
            $currentPage = $_GET['page'];
        }
        else
        {
            $currentPage = 1;
        }

        $searchPager = new SearchPager(
            CANDIDATES_PER_PAGE, $currentPage, $this->_siteID, $_GET
        );

        if ($searchPager->isSortByValid('sortBy', $_GET))
        {
            $sortBy = $_GET['sortBy'];
        }
        else
        {
            $sortBy = 'name';
        }

        if ($searchPager->isSortDirectionValid('sortDirection', $_GET))
        {
            $sortDirection = $_GET['sortDirection'];
        }
        else
        {
            $sortDirection = 'ASC';
        }

        $baseURL = CATSUtility::getFilteredGET(
            array('sortBy', 'sortDirection', 'page'), '&amp;'
        );
        $searchPager->setSortByParameters($baseURL, $sortBy, $sortDirection);

        if (!eval(Hooks::get('CLIENTS_ON_SEARCH_PRE'))) return;

        /* Get our current searching mode. */
        $mode = trim(ClsNaanalRequest::getInstance()->getData("mode"));

        /* Execute the search. */
        $search = new SearchCompanies($this->_siteID);
        switch ($mode)
        {
            case 'searchByName':
                $wildCardCompanyName = $query;
                $rs = $search->byName($query, $sortBy, $sortDirection);
                break;

            case 'searchByKeyTechnologies':
                $wildCardKeyTechnologies = $query;
                $rs = $search->byKeyTechnologies($query, $sortBy, $sortDirection);
                break;

            default:
                $arrData = $search->byAll($sortBy, $sortDirection);
                $rs=$arrData["records"];
                $total_records=$arrData["total_records"];
                $arrPager=ClsNaanalRequest::getInstance()->getPager();
                $pagination=getPagination("index.php?module=companies", $total_records, $arrPager["current_page"], $arrPager["items_per_page"]);
                break;
        }

        foreach ($rs as $rowIndex => $row)
        {
            if ($row['isHot'] == 1)
            {
                $rs[$rowIndex]['linkClass'] = 'jobLinkHot';
            }
            else
            {
                $rs[$rowIndex]['linkClass'] = 'jobLinkCold';
            }

            if (!empty($row['ownerFirstName']))
            {
                $rs[$rowIndex]['ownerAbbrName'] = StringUtility::makeInitialName(
                    $row['ownerFirstName'],
                    $row['ownerLastName'],
                    false,
                    LAST_NAME_MAXLEN
                );
            }
            else
            {
                $rs[$rowIndex]['ownerAbbrName'] = 'None';
            }
        }

        $companyIDs = implode(',', ResultSetUtility::getColumnValues($rs, 'companyID'));
        $exportForm = ExportUtility::getForm(
            DATA_ITEM_COMPANY, $companyIDs, 40, 15
        );

        /* Save the search. */
        $savedSearches = new SavedSearches($this->_siteID);
        $savedSearches->add(
            DATA_ITEM_COMPANY,
            $query,
            $_SERVER['REQUEST_URI'],
            false
        );
        $savedSearchRS = $savedSearches->get(DATA_ITEM_COMPANY);

        $query = rawurlencode(htmlspecialchars($query));

        if (!eval(Hooks::get('CLIENTS_ON_SEARCH_POST'))) return;
        $arrTpl["pagination"]=$pagination;
        $arrTpl["savedSearchRS"]=$savedSearchRS;
        $arrTpl["active"]=$this;
        $arrTpl["subActive"]='Search Companies';
        $arrTpl["exportForm"]=$exportForm;
        $arrTpl["pager"]=$searchPager;
        $arrTpl["rs"]=$rs;
        $arrTpl["isResultsMode"]=true;
        $arrTpl["wildCardCompanyName"]=$wildCardCompanyName;
        $arrTpl["wildCardString"]=$query;
        $arrTpl["wildCardKeyTechnologies"]=$wildCardKeyTechnologies;
        $arrTpl["mode"]=$mode;
        $arrTpl["total_records"]=count($rs);
        return $arrTpl;
    }

    /*
     * Called by handleRequest() to process loading the create attachment
     * modal dialog.
     */
    private function createAttachment()
    {
        /* Bail out if we don't have a valid joborder ID. */
        if (!$this->isRequiredIDValid('companyID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        $companyID = $_GET['companyID'];

        if (!eval(Hooks::get('CLIENTS_CREATE_ATTACHMENT'))) return;

        $arrTpl["isFinishedMode"]=false;
        $arrTpl["companyID"]=$companyID;
        return $arrTpl;
    }

    /*
     * Called by handleRequest() to process creating an attachment.
     */
    private function onCreateAttachment()
    {
        if ($this->_accessLevel < ACCESS_LEVEL_EDIT)
        {
            $this->listByView('Invalid user level for action.');
            return;
        }

        /* Bail out if we don't have a valid joborder ID. */
        if (!$this->isRequiredIDValid('companyID', $_POST))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid company ID.');
        }

        $companyID = $_POST['companyID'];

        if (!eval(Hooks::get('CLIENTS_ON_CREATE_ATTACHMENT_PRE'))) return;

        $attachmentCreator = new AttachmentCreator($this->_siteID);
        $attachmentCreator->createFromUpload(
            DATA_ITEM_COMPANY, $companyID, 'file', false, false
        );

        if ($attachmentCreator->isError())
        {
            CommonErrors::fatalModal(COMMONERROR_FILEERROR, $this, $attachmentCreator->getError());
        }

        if (!eval(Hooks::get('CLIENTS_ON_CREATE_ATTACHMENT_POST'))) return;

        $arrTpl["isFinishedMode"]=true;
        $arrTpl["companyID"]=$companyID;
        return $arrTpl;
    }

    /*
     * Called by handleRequest() to process deleting an attachment.
     */
    private function onDeleteAttachment()
    {
        if ($this->_accessLevel < ACCESS_LEVEL_DELETE)
        {
            $this->listByView('Invalid user level for action.');
            return;
        }

        /* Bail out if we don't have a valid attachment ID. */
        if (!$this->isRequiredIDValid('attachmentID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid attachment ID.');
        }

        /* Bail out if we don't have a valid joborder ID. */
        if (!$this->isRequiredIDValid('companyID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid company ID.');
        }

        $companyID  = $_GET['companyID'];
        $attachmentID = $_GET['attachmentID'];

        if (!eval(Hooks::get('CLIENTS_ON_DELETE_ATTACHMENT_PRE'))) return;

        $attachments = new Attachments($this->_siteID);
        $attachments->delete($attachmentID);

        if (!eval(Hooks::get('CLIENTS_ON_DELETE_ATTACHMENT_POST'))) return;
        header("Location:index.php?module=companies&action=show&companyID=" . $companyID);die();
    }


    
    private function _formatListByViewResults($resultSet)
    {
        if (empty($resultSet))
        {
            return $resultSet;
        }

        foreach ($resultSet as $rowIndex => $row)
        {
            /* Hot companies [can] have different title styles than normal
             * companies.
             */
            if ($resultSet[$rowIndex]['isHot'] == 1)
            {
                $resultSet[$rowIndex]['linkClass'] = 'jobLinkHot';
            }
            else
            {
                $resultSet[$rowIndex]['linkClass'] = 'jobLinkCold';
            }

            if (!empty($resultSet[$rowIndex]['ownerFirstName']))
            {
                $resultSet[$rowIndex]['ownerAbbrName'] = StringUtility::makeInitialName(
                    $resultSet[$rowIndex]['ownerFirstName'],
                    $resultSet[$rowIndex]['ownerLastName'],
                    false,
                    LAST_NAME_MAXLEN
                );
            }
            else
            {
                $resultSet[$rowIndex]['ownerAbbrName'] = 'None';
            }

            if ($resultSet[$rowIndex]['attachmentPresent'] == 1)
            {
                $resultSet[$rowIndex]['iconTag'] = '<img src="images/paperclip.gif" alt="" width="16" height="16" />';
            }
            else
            {
                $resultSet[$rowIndex]['iconTag'] = '&nbsp;';
            }

            /* Display nothing instead of zero's for Job Order Count on Companies
             * display page.
             */
            if ($resultSet[$rowIndex]['jobOrdersCount'] == 0)
            {
                $resultSet[$rowIndex]['jobOrdersCount'] = '&nbsp;';
            }
        }

        return $resultSet;
    }
}

?>
