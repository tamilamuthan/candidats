<?php
/*
 * CATS
 * Job Orders Module
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
 * $Id: JobOrdersUI.php 3810 2007-12-05 19:13:25Z brian $
 */

include_once('./lib/StringUtility.php');
include_once('./lib/ResultSetUtility.php');
include_once('./lib/DateUtility.php'); /* Depends on StringUtility. */
include_once('./lib/JobOrders.php');
include_once('./lib/Pipelines.php');
include_once('./lib/Attachments.php');
include_once('./lib/Companies.php');
include_once('./lib/Candidates.php');
include_once('./lib/ActivityEntries.php');
include_once('./lib/Export.php');
include_once('./lib/InfoString.php');
include_once('./lib/EmailTemplates.php');
include_once('./lib/FileUtility.php');
include_once('./lib/CareerPortal.php');
include_once('./lib/ExtraFields.php');
include_once('./lib/Graphs.php');
include_once('./lib/Questionnaire.php');
include_once('./lib/CommonErrors.php');
include_once('./lib/Search.php');
include_once('./lib/DocumentToText.php');
include_once(__DIR__.'/ClsVJobordersList.php');
include_once(__DIR__.'/ClsMJobordersList.php');

class JobOrdersUI extends UserInterface 
{

    /* Maximum number of characters of the job description to show without the
     * user clicking "[More]"
     */
    const DESCRIPTION_MAXLEN = 500;

    /* Maximum number of characters of the job notes to show without the user
     * clicking "[More]"
     */
    const NOTES_MAXLEN = 500;

    /* Maximum number of characters of the Job Order Title to show on the main
     * job order list view.
     */
    const TRUNCATE_JOBORDER_TITLE = 35;

    /* Maximum number of characters of the company name to show on the job orders
     * list view.
     */
    const TRUNCATE_CLIENT_NAME = 28;


    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = true;
        $this->_moduleDirectory = 'joborders';
        $this->_moduleName = 'joborders';
        $this->_moduleTabText = 'Job Orders';
        $this->_subTabs = array(
            //'Add Job Order'     => CATSUtility::getIndexName() . '?m=joborders&a=add*al='.ACCESS_LEVEL_EDIT,
            'Add Job Order' => 'javascript:void(0);*js=showPopWin(\''.CATSUtility::getIndexName().'?m=joborders&a=addJobOrderPopup\', 400, 250, null);*al='.ACCESS_LEVEL_EDIT,
            'Search Job Orders' => CATSUtility::getIndexName() . '?m=joborders&a=search'
        );
    }


    public function render()
    {
        $action = $this->getAction();

        if (!eval(Hooks::get('JO_HANDLE_REQUEST'))) return;

        switch ($action)
        {
            case 'show':
                $this->show();
                break;

            case 'addJobOrderPopup':
                $this->addJobOrderPopup();
                break;

            case 'add':
                if ($this->isPostBack())
                {
                    $this->onAdd();
                }
                else
                {
                    $this->add();
                }

                break;

            case 'edit':
                if ($this->isPostBack())
                {
                    $this->onEdit();
                }
                else
                {
                    $this->edit();
                }

                break;

            case 'delete':
                $this->onDelete();
                break;
            
            case 'deleteSelected':
                $this->deleteSelected();
                break;

            case 'search':

                if ($this->isGetBack())
                {
                    $this->onSearch();
                }
                else
                {
                    $this->search();
                }

                break;

            /* Change candidate-joborder status. */
            case 'addActivityChangeStatus':
                if ($this->isPostBack())
                {
                    $this->onAddActivityChangeStatus();
                }
                else
                {
                    $this->addActivityChangeStatus();
                }

                break;

            /*
             * Search for a candidate (in the modal window) for which to
             * consider for this job order.
             */
            case 'considerCandidateSearch':

                if ($this->isPostBack())
                {
                    $this->onConsiderCandidateSearch();
                }
                else
                {
                    $this->considerCandidateSearch();
                }

                break;

            /*
             * Add candidate to pipeline after selecting a job order for which
             * to consider a candidate (in the modal window).
             */
            case 'addToPipeline':
                $this->onAddToPipeline();
                break;

            /*
             * Quick add candidate (in the modal window).
             */
            case 'addCandidateModal':
                if ($this->isPostBack())
                {
                    $this->onAddCandidateModal();
                }
                else
                {
                    $this->addCandidateModal();
                }

                break;

            /* Remove a candidate from a pipeline. */
            case 'removeFromPipeline':
                $this->onRemoveFromPipeline();
                break;

            /* Remove a candidate from a pipeline. */
            case 'removeFromPipeline':
                $this->onRemoveFromPipeline();
                break;

            /* Add an attachment */
            case 'createAttachment':

                if ($this->isPostBack())
                {
                    $this->onCreateAttachment();
                }
                else
                {
                    $this->createAttachment();
                }

                break;

            /* Delete an attachment */
            case 'deleteAttachment':
                $this->onDeleteAttachment();
                break;

            case 'setCandidateJobOrder':
                $this->setCandidateJobOrder();
                break;

            case 'administrativeHideShow':
                $this->administrativeHideShow();
                break;

            /* Main job orders page. */
            case 'listByView':
            default:
                $this->listByView();
                break;
        }
    }


    /*
     * Called by render() to process loading the list / main page.
     */
    public function listByView($errMessage = '')
    {
        $dataGridProperties = DataGrid::getRecentParamaters("joborders:JobOrdersListByViewDataGrid");

        /* If this is the first time we visited the datagrid this session, the recent paramaters will
         * be empty.  Fill in some default values. */
        if ($dataGridProperties == array())
        {
            $dataGridProperties = array('rangeStart'    => 0,
                                        'maxResults'    => 50,
                                        'filter'        => 'Status==Active / OnHold / Full',
                                        'filterVisible' => false);
        }

        $dataGrid = DataGrid::get("joborders:JobOrdersListByViewDataGrid", $dataGridProperties);

        $this->_template->assign('active', $this);
        $this->_template->assign('dataGrid', $dataGrid);
        $this->_template->assign('userID', $_SESSION['CATS']->getUserID());
        $this->_template->assign('errMessage', $errMessage);

        if (!eval(Hooks::get('JO_LIST_BY_VIEW'))) return;

        $jl = new JobOrders($this->_siteID);
        $this->_template->assign('totalJobOrders', $jl->getCount());

        $this->_template->display('./modules/joborders/JobOrders.php');
    }

    /*
     * Called by render() to process loading the details page.
     */
    public function show()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_GET))
        {
            /* FIXME: fatalPopup()? */
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        $jobOrderID = $_GET['jobOrderID'];


        $jobOrders = new JobOrders($this->_siteID);
        $data = $jobOrders->get($jobOrderID);

        /* Bail out if we got an empty result set. */
        if (empty($data))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'The specified job order ID could not be found.');
        }

        if ($data['is_admin_hidden'] == 1 && $this->_accessLevel < ACCESS_LEVEL_MULTI_SA)
        {
            $this->listByView('This Job Order is hidden - only a CATS Administrator can unlock the Job Order.');
            return;
        }

        /* We want to handle formatting the city and state here instead of in
         * the template.
         */
        $data['cityAndState'] = StringUtility::makeCityStateString(
            $data['city'], $data['state']
        );
        /**
         * if ownertype is group, override the user full name
         */
        if($data['ownertype']>0)
        {
            $sql="select * from auieo_groups where id={$data['owner']}";
            $objDB=DatabaseConnection::getInstance();
            $row=$objDB->getAssoc($sql);
            if($row)
            {
                $data["ownerFullName"]=$row["groupname"];
            }
        }

        $data['description'] = trim($data['description']);
        $data['notes'] = trim($data['notes']);

        /* Determine the Job Type Description */
        $data['typeDescription'] = $jobOrders->typeCodeToString($data['type']);

        /* Convert '00-00-00' dates to empty strings. */
        $data['startDate'] = DateUtility::fixZeroDate(
            $data['startDate']
        );

        /* Hot jobs [can] have different title styles than normal jobs. */
        if ($data['is_hot'] == 1)
        {
            $data['titleClass'] = 'jobTitleHot';
        }
        else
        {
            $data['titleClass'] = 'jobTitleCold';
        }

        if ($data['public'] == 1)
        {
            $data['public'] = '<img src="images/public.gif" height="16" '
                . 'width="16" title="This Job Order is marked as Public." />';
        }
        else
        {
            $data['public'] = '';
        }

        $attachments = new Attachments($this->_siteID);
        $attachmentsRS = $attachments->getAll(
            DATA_ITEM_JOBORDER, $jobOrderID
        );

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

        $careerPortalSettings = new CareerPortalSettings($this->_siteID);
        $careerPortalSettingsRS = $careerPortalSettings->getAll();

        if ($careerPortalSettingsRS['enabled'] == 1)
        {
            $careerPortalEnabled = true;
        }
        else
        {
            $careerPortalEnabled = false;
        }

        /* Add an MRU entry. */
        $_SESSION['CATS']->getMRU()->addEntry(
            DATA_ITEM_JOBORDER, $jobOrderID, $data['title']
        );

        if ($this->_accessLevel < ACCESS_LEVEL_DEMO)
        {
            $privledgedUser = false;
        }
        else
        {
            $privledgedUser = true;
        }

        /* Get extra fields. */
        $extraFieldRS = $jobOrders->extraFields->getValuesForShow($jobOrderID);

        $pipelineEntriesPerPage = $_SESSION['CATS']->getPipelineEntriesPerPage();

        $sessionCookie = $_SESSION['CATS']->getCookie();

        /* Get pipeline graph. */
        $graphs = new graphs();
        $pipelineGraph = $graphs->miniJobOrderPipeline(450, 250, array($jobOrderID));

        /* Get questionnaire information (if exists) */
        $questionnaireID = false;
        $questionnaireData = false;
        $careerPortalURL = false;
        $isPublic = false;



        if ($careerPortalEnabled && $data['public'])
        {
            $isPublic = true;
            if ($data['questionnaire_id'])
            {
                $questionnaire = new Questionnaire($this->_siteID);
                $q = $questionnaire->get($data['questionnaire_id']);
                if (is_array($q) && !empty($q))
                {
                    $questionnaireID = $q['questionnaireID'];
                    $questionnaireData = $q;
                }
            }
        }

        $careerPortalSettings = new CareerPortalSettings($this->_siteID);
        $cpSettings = $careerPortalSettings->getAll();
        if (intval($cpSettings['enabled']))
        {
            $careerPortalURL = CATSUtility::getAbsoluteURI() . 'careers/';
        }

        //$sql="select * from auieo_fields where site_id={$this->_siteID} and tablename='joborder' and presence!=1";
        //$db=DatabaseConnection::getInstance();
        //$arrAssoc=$db->getAllAssoc($sql);
        
        $adminHidden="";
        if ($data['is_admin_hidden'] == 1)
        {
            $adminHidden = "<p class='warning'>This Job Order is hidden.  Only CATS Administrators can view it or search for it.  To make it visible by the site users, click <a href='index.php?m=joborders&a=administrativeHideShow&jobOrderID={$jobOrderID}&state=0' style='font-weight:bold;'>Here.</a></p>";
        }
        
        $strFrozen="";
        if (isset($frozen))
        {
            $strFrozen = "<table style='font-weight:bold; border: 1px solid #000; background-color: #ffed1a; padding:5px; margin-bottom:7px;' width='100%' id='candidateAlreadyInSystemTable'>
                <tr>
                    <td class='tdVertical' style='width:100%;'>
                        This Job Order is {$this->data['status']} and can not be modified.
                       ";
            if ($this->accessLevel >= ACCESS_LEVEL_EDIT)
            {
                $strFrozen = $strFrozen . "
                           <a id='edit_link' href='index.php?m=joborders&a=edit&jobOrderID={$this->jobOrderID}'>
                               <img src='images/actions/edit.gif' width='16' height='16' class='absmiddle' alt='edit' border='0' />&nbsp;Edit
                           </a>
                           the Job Order to make it Active.&nbsp;&nbsp;
                       ";
            } 
            $strFrozen = $strFrozen . "
                    </td>
                </tr>
            </table>
        ";
        }
        $public="";
        if ($isPublic)
        {
            $public = "<div style='background-color: #E6EEFE; padding: 10px; margin: 5px 0 12px 0; border: 1px solid #728CC8;'>
            <b>This job order is public";
            if ($careerPortalURL === false)
            {
                $public = $public . ".</b>";
            }
            else
            {
                $public = $public . "
                and will be shown on your
                ";
                if ($_SESSION['CATS']->getAccessLevel() >= ACCESS_LEVEL_SA){ 
                    $public = $public . "
                    <a style='font-weight: bold;' href='{$careerPortalURL}'>Careers Website</a>.
                ";
                }
                else
                {
                    $public = $public . "
                    Careers Website.
                ";
                } 
                $public = $public . "</b>
            ";
            } 
            if ($questionnaireID !== false)
            {
                $public = $public . "<br />Applicants must complete the '<i>{$questionnaireData['title']}</i>' (<a href='index.php?m=settings&a=careerPortalQuestionnaire&questionnaireID={$questionnaireID}'>edit</a>) questionnaire when applying.";
            }
            else
            {
                $public = $public . "<br />You have not attached any ";
                if ($_SESSION['CATS']->getAccessLevel() >= ACCESS_LEVEL_SA)
                {
                    $public = $public . "<a href='index.php?m=settings&a=careerPortalSettings'>Questionnaires</a>.";
                }
                else
                {
                    $public = $public . "Questionnaires.";
                }
            }
            $public = $public . "</div>";
        }
        
        $ACCESS_LEVEL_EDIT_BUTTON="";
        if ($this->_accessLevel >= ACCESS_LEVEL_EDIT)
        { 
            $ACCESS_LEVEL_EDIT_BUTTON = "<a id='edit_link' href='index.php?m=joborders&a=edit&jobOrderID={$jobOrderID}'>
                <img src='images/actions/edit.gif' width='16' height='16' class='absmiddle' alt='edit' border='0' />&nbsp;Edit
            </a>";
        }
        $ACCESS_LEVEL_DELETE_BUTTON="";
        if ($this->_accessLevel >= ACCESS_LEVEL_DELETE)
        { 
            $ACCESS_LEVEL_DELETE_BUTTON = "<a id='delete_link' href='index.php?m=joborders&a=delete&jobOrderID={$jobOrderID}' onclick=\"javascript:return confirm('Delete this job order?');\">
                <img src='images/actions/delete.gif' width='16' height='16' class='absmiddle' alt='delete' border='0' />&nbsp;Delete
            </a>";
        }
        $ACCESS_LEVEL_MULTI_SA_BUTTON="";
        if ($this->_accessLevel >= ACCESS_LEVEL_MULTI_SA)
        {
            if ($data['is_admin_hidden'] == 1)
            {
                $ACCESS_LEVEL_MULTI_SA_BUTTON = "<a href='index.php?m=joborders&a=administrativeHideShow&jobOrderID={$jobOrderID}&state=0'>
                    <img src='images/resume_preview_inline.gif' width='16' height='16' class='absmiddle' alt='delete' border='0' />&nbsp;Administrative Show
                </a>";
            }
            else
            {
                $ACCESS_LEVEL_MULTI_SA_BUTTON = "<a href='index.php?m=joborders&a=administrativeHideShow&jobOrderID={$jobOrderID}&state=1'>
                <img src='images/resume_preview_inline.gif' width='16' height='16' class='absmiddle' alt='delete' border='0' />&nbsp;Administrative Hide
            </a>";
            }
        }
        $ACCESS_LEVEL_EDIT_CONSIDER="";
        if ($this->_accessLevel >= ACCESS_LEVEL_EDIT)
        { 
            $ACCESS_LEVEL_EDIT_CONSIDER = "<a href='#' onclick=\"showPopWin('index.php?m=joborders&a=considerCandidateSearch&jobOrderID={$jobOrderID}', 820, 550, null); return false;\">
                <img src='images/consider.gif' width='16' height='16' class='absmiddle' alt='add candidate' border='0' />&nbsp;Add Candidate to This Job Order Pipeline
            </a>";
        }

        $this->_template->assign('active', $this);
        $this->_template->assign('public', $public);
        $this->_template->assign('ACCESS_LEVEL_EDIT_BUTTON', $ACCESS_LEVEL_EDIT_BUTTON);
        $this->_template->assign('ACCESS_LEVEL_DELETE_BUTTON', $ACCESS_LEVEL_DELETE_BUTTON);
        $this->_template->assign('ACCESS_LEVEL_MULTI_SA_BUTTON', $ACCESS_LEVEL_MULTI_SA_BUTTON);
        $this->_template->assign('ACCESS_LEVEL_EDIT_CONSIDER', $ACCESS_LEVEL_EDIT_CONSIDER);
        $this->_template->assign('questionnaireID', $questionnaireID);
        $this->_template->assign('questionnaireData', $questionnaireData);
        $this->_template->assign('careerPortalURL', $careerPortalURL);
        $this->_template->assign('data', $data);
        $this->_template->assign('frozen', $strFrozen);
        $this->_template->assign('adminHidden',$adminHidden);
        $this->_template->assign('extraFieldRS', $extraFieldRS);
        $this->_template->assign('attachmentsRS', $attachmentsRS);
        $this->_template->assign('pipelineEntriesPerPage', $pipelineEntriesPerPage);
        $this->_template->assign('pipelineGraph', $pipelineGraph);
        $this->_template->assign('jobOrderID', $jobOrderID);
        $this->_template->assign('careerPortalEnabled', $careerPortalEnabled);
        $this->_template->assign('privledgedUser', $privledgedUser);
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());
        //$this->_template->assign('fields_detail',$arrAssoc);

        if (!eval(Hooks::get('JO_SHOW'))) return;
        /* Is this a popup? */
        if (isset($_GET['display']) && $_GET['display'] == 'popup')
        {
            $this->_template->display('./modules/joborders/show_popup.php');
            $isPopup = true;
        }
        else
        {
            $this->_template->display('./modules/joborders/Show.php');
            $isPopup = false;
        }
    }

    /*
     * Called by render() to render the add popup.
     */
    public function addJobOrderPopup()
    {
        $jobOrders = new JobOrders($this->_siteID);

        $rs = $jobOrders->getAll(JOBORDERS_STATUS_ACTIVEONHOLDFULL);

        $this->_template->assign('isModal', true);
        $this->_template->assign('rs', $rs);

        if (!eval(Hooks::get('JO_ADD_MODAL'))) return;

        $this->_template->display('./modules/joborders/AddModalPopup.php');
    }

    /*
     * Called by render() to process loading the add page.
     */
    public function add()
    {
        $users = new Users($this->_siteID);
        $usersRS = $users->getSelectList();
        $groupRS = $users->getSelectGroupList();
        $companies = new Companies($this->_siteID);
        $companiesRS = $companies->getSelectList();

        $jobOrders = new JobOrders($this->_siteID);

        /* Do we have any companies yet? */
        if (empty($companiesRS))
        {
            $noCompanies = true;
        }
        else
        {
            $noCompanies = false;
        }

        if (!$this->isRequiredIDValid('selected_company_id', $_GET))
        {
            $selectedCompanyID = false;
        }
        else
        {
            $selectedCompanyID = $_GET['selected_company_id'];
        }

        if ($_SESSION['CATS']->isHrMode())
        {
            $companies = new Companies($this->_siteID);
            $selectedCompanyID = $companies->getDefaultCompany();
        }

        /* Do we have a selected_company_id? */
        if ($selectedCompanyID === false)
        {
            $selectedCompanyContacts = array();
            $selectedCompanyLocation = array();
            $selectedDepartmentsString = '';

            $defaultCompanyID = $companies->getDefaultCompany();
            if ($defaultCompanyID !== false)
            {
                $defaultCompanyRS = $companies->get($defaultCompanyID);
            }
            else
            {
                $defaultCompanyRS = array();
            }

            $companyRS = array();
        }
        else
        {
            $selectedCompanyContacts = $companies->getContactsArray(
                $selectedCompanyID
            );
            $selectedCompanyLocation = $companies->getLocationArray(
                $selectedCompanyID
            );
            $departmentsRS = $companies->getDepartments($selectedCompanyID);
            $selectedDepartmentsString = ListEditor::getStringFromList(
                $departmentsRS, 'name'
            );

            $defaultCompanyID = false;
            $defaultCompanyRS = array();

            $companyRS = $companies->get($selectedCompanyID);
        }

        /* Should we prepopulate the blank JO with the contents of another JO? */
        if (isset($_GET['typeOfAdd']) &&
            $this->isRequiredIDValid('jobOrderID', $_GET) &&
            $_GET['typeOfAdd'] == 'existing')
        {
            $jobOrderID = $_GET['jobOrderID'];

            $jobOrderSourceRS = $jobOrders->get($jobOrderID);

            $jobOrderSourceExtraFields = $jobOrders->extraFields->getValuesForEdit($jobOrderID);

            $this->_template->assign('jobOrderSourceRS', $jobOrderSourceRS);
            $this->_template->assign('jobOrderSourceExtraFields', $jobOrderSourceExtraFields);
        }
        else
        {
            $this->_template->assign('jobOrderSourceRS', false);
            $this->_template->assign('jobOrderSourceExtraFields', false);
        }

        /* Get extra fields. */
        $extraFieldRS = $jobOrders->extraFields->getValuesForAdd();

        /* Get questionnaires to attach (if public) */
        $questionnaire = new Questionnaire($this->_siteID);
        $questionnaires = $questionnaire->getAll(false);

        $careerPortalSettings = new CareerPortalSettings($this->_siteID);
        $careerPortalSettingsRS = $careerPortalSettings->getAll();
        $careerPortalEnabled = intval($careerPortalSettingsRS['enabled']) ? true : false;

        $this->_template->assign('careerPortalEnabled', $careerPortalEnabled);
        $this->_template->assign('questionnaires', $questionnaires);
        $this->_template->assign('extraFieldRS', $extraFieldRS);
        $this->_template->assign('defaultCompanyID', $defaultCompanyID);
        $this->_template->assign('defaultCompanyRS', $defaultCompanyRS);
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Add Job Order');
        $this->_template->assign('usersRS', $usersRS);
        $this->_template->assign('groupRS', $groupRS);
        $this->_template->assign('userID', $this->_userID);
        $this->_template->assign('companiesRS', $companiesRS);
        $this->_template->assign('companyRS', $companyRS);
        $this->_template->assign('noCompanies', $noCompanies);
        $this->_template->assign('selectedCompanyID', $selectedCompanyID);
        $this->_template->assign('selectedCompanyContacts', $selectedCompanyContacts);
        $this->_template->assign('selectedCompanyLocation', $selectedCompanyLocation);
        $this->_template->assign('selectedDepartmentsString', $selectedDepartmentsString);
        $this->_template->assign('isHrMode', $_SESSION['CATS']->isHrMode());
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());

        if (!eval(Hooks::get('JO_ADD'))) return;
        if($noCompanies)
        {
            $this->_template->display('./modules/joborders/NoCompany.php');
        }
        else
        {
            $this->_template->display('./modules/joborders/Add.php');
        }
    }
    
    /*
     * Called by render() to process saving / submitting the add page.
     */
    public function onAdd()
    {
        if ($this->_accessLevel < ACCESS_LEVEL_EDIT)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
        }

        /* Bail out if we don't have a valid company ID. */
        if (!$this->isRequiredIDValid('companyID', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid company ID.');
        }

        /* Bail out if we don't have a valid recruiter user ID. */
        if (!$this->isRequiredIDValid('recruiter', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid recruiter user ID.');
        }

        /* Bail out if we don't have a valid number of openings. */
        if (!$this->isRequiredIDValid('openings', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Invalid number of openings.');
        }

        /* Bail out if we don't have a valid contact ID. */
        if (!$this->isOptionalIDValid('contactID', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid contact ID.');
        }

        if (isset($_POST['openings']) && !empty($_POST['openings']) &&
            !ctype_digit((string) $_POST['openings']))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Invalid number of openings.');
        }

        /* Bail out if we received an invalid start date; if not, go ahead and
         * convert the date to MySQL format.
         */
        $startDate = $this->getTrimmedInput('startDate', $_POST);
        if (!empty($startDate))
        {
            if (!DateUtility::validate('-', $startDate, DATE_FORMAT_MMDDYY))
            {
                CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Invalid start date.');
            }

            /* Convert start_date to something MySQL can understand. */
            $startDate = DateUtility::convert(
                '-', $startDate, DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD
            );
        }

        /* Hot job? */
        $isHot = $this->isChecked('isHot', $_POST);

        /* Public Job? */
        $isPublic = $this->isChecked('public', $_POST);

        /* If it is public, is a questionnaire attached? */
        $questionnaireID =
            // If a questionnaire is provided the field will be shown and it will != 'none'
            isset($_POST['questionnaire']) && !empty($_POST['questionnaire']) &&
            strcmp($_POST['questionnaire'], 'none') && $isPublic ?
            // The result will be an ID from the questionnaire table:
            intval($_POST['questionnaire']) :
            // If no questionnaire exists, boolean false
            false;

        $companyID   = $_POST['companyID'];
        $contactID   = $_POST['contactID'];
        $recruiter   = $_POST['recruiter'];
        $owner       = $_POST['owner'];
        $openings    = $_POST['openings'];
        $arrOwner=explode(":",$_POST['owner']);
        $owner       = isset($arrOwner[1])?trim($arrOwner[1]):0;
        $ownertype   = trim($arrOwner[0]);
        $title       = $this->getTrimmedInput('title', $_POST);
        $companyJobID = $this->getTrimmedInput('companyJobID', $_POST);
        $type        = $this->getTrimmedInput('type', $_POST);
        $city        = $this->getTrimmedInput('city', $_POST);
        $state       = $this->getTrimmedInput('state', $_POST);
        $duration    = $this->getTrimmedInput('duration', $_POST);
        $department  = $this->getTrimmedInput('department', $_POST);
        $maxRate     = $this->getTrimmedInput('maxRate', $_POST);
        $salary      = $this->getTrimmedInput('salary', $_POST);
        $description = $this->getTrimmedInput('description', $_POST);
        $notes       = $this->getTrimmedInput('notes', $_POST);
        $candidate_mapping=false;
        if(isset($_POST["candidate_mapping"]) && !empty($_POST["candidate_mapping"]))
            $candidate_mapping = $_POST["candidate_mapping"];

        /* Bail out if any of the required fields are empty. */
        if (empty($title) || empty($type) || empty($city) || empty($state))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
        }

        if (!eval(Hooks::get('JO_ON_ADD'))) return;

        $jobOrders = new JobOrders($this->_siteID);
        $jobOrderID = $jobOrders->add(
            $title, $companyID, $contactID, $description, $notes, $duration,
            $maxRate, $type, $isHot, $isPublic, $openings, $companyJobID,
            $salary, $city, $state, $startDate, $this->_userID, $recruiter,
            $owner, $department, $questionnaireID,$candidate_mapping,$ownertype
        );

        if ($jobOrderID <= 0)
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to add job order.');
        }

        /* Update extra fields. */
        $jobOrders->extraFields->setValuesOnEdit($jobOrderID);

        if (!eval(Hooks::get('JO_ON_ADD_POST'))) return;

        CATSUtility::transferRelativeURI(
            'm=joborders&a=show&jobOrderID=' . $jobOrderID
        );
    }

    /*
     * Called by render() to process loading the edit page.
     */
    public function edit()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        $jobOrderID = $_GET['jobOrderID'];


        $jobOrders = new JobOrders($this->_siteID);
        $data = $jobOrders->getForEditing($jobOrderID);
        if(isset($data["candidate_mapping"]) && !empty($data["candidate_mapping"]))
        {
            $data["candidate_mapping"]=  json_decode($data["candidate_mapping"]);
        }
        /* Bail out if we got an empty result set. */
        if (empty($data))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'The specified job order ID could not be found.');
        }

        $users = new Users($this->_siteID);
        $usersRS = $users->getSelectList();
        $groupRS = $users->getSelectGroupList();
        $companies = new Companies($this->_siteID);
        $companiesRS = $companies->getSelectList();
        $contactsRS = $companies->getContactsArray($data['company_id']);

        /* Add an MRU entry. */
        $_SESSION['CATS']->getMRU()->addEntry(
            DATA_ITEM_JOBORDER, $jobOrderID, $data['title']
        );

        $emailTemplates = new EmailTemplates($this->_siteID);
        $statusChangeTemplateRS = $emailTemplates->getByTag(
            'EMAIL_TEMPLATE_OWNERSHIPASSIGNJOBORDER'
        );
        if ($statusChangeTemplateRS['disabled'] == 1)
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

        $companies = new Companies($this->_siteID);
        $defaultCompanyID = $companies->getDefaultCompany();
        if ($defaultCompanyID !== false)
        {
            $defaultCompanyRS = $companies->get($defaultCompanyID);
        }
        else
        {
            $defaultCompanyRS = array();
        }

        /* Get departments. */
        $departmentsRS = $companies->getDepartments($data['company_id']);
        $departmentsString = ListEditor::getStringFromList($departmentsRS, 'name');

        /* Date format for DateInput()s. */
        if ($_SESSION['CATS']->isDateDMY())
        {
            $data['startDateMDY'] = DateUtility::convert(
                '-', $data['startDate'], DATE_FORMAT_DDMMYY, DATE_FORMAT_MMDDYY
            );
        }
        else
        {
            $data['startDateMDY'] = $data['startDate'];
        }

        /* Get extra fields. */
        $extraFieldRS = $jobOrders->extraFields->getValuesForEdit($jobOrderID);

        /* Check if career portal is enabled */
        $careerPortalSettings = new CareerPortalSettings($this->_siteID);
        $careerPortalSettingsRS = $careerPortalSettings->getAll();
        $careerPortalEnabled = intval($careerPortalSettingsRS['enabled']) ? true : false;

        /* Get questionnaire information (if exists) */
        $questionnaireID = false;
        $questionnaireData = false;
        $isPublic = false;
        $questionnaire = new Questionnaire($this->_siteID);

        $questionnaires = $questionnaire->getAll(false);

        if ($careerPortalEnabled && $data['public'])
        {
            $isPublic = true;
            if (isset($data['questionnaireID']) && $data['questionnaireID'])
            {
                $questionnaire = new Questionnaire($this->_siteID);
                $q = $questionnaire->get($data['questionnaireID']);
                if (is_array($q) && !empty($q))
                {
                    $questionnaireID = $q['questionnaireID'];
                    $questionnaireData = $q;
                }
            }
        }

        $this->_template->assign('extraFieldRS', $extraFieldRS);
        $this->_template->assign('careerPortalEnabled', $careerPortalEnabled);
        $this->_template->assign('questionnaireID', $questionnaireID);
        $this->_template->assign('questionnaireData', $questionnaireData);
        $this->_template->assign('questionnaires', $questionnaires);
        $this->_template->assign('isPublic', $isPublic);
        $this->_template->assign('defaultCompanyID', $defaultCompanyID);
        $this->_template->assign('defaultCompanyRS', $defaultCompanyRS);
        $this->_template->assign('canEmail', $canEmail);
        $this->_template->assign('emailTemplateDisabled', $emailTemplateDisabled);
        $this->_template->assign('active', $this);
        $this->_template->assign('data', $data);
        $this->_template->assign('usersRS', $usersRS);
        $this->_template->assign('groupRS', $groupRS);
        $this->_template->assign('companiesRS', $companiesRS);
        $this->_template->assign('departmentsRS', $departmentsRS);
        $this->_template->assign('departmentsString', $departmentsString);
        $this->_template->assign('contactsRS', $contactsRS);
        $this->_template->assign('jobOrderID', $jobOrderID);
        $this->_template->assign('isHrMode', $_SESSION['CATS']->isHrMode());
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());

        if (!eval(Hooks::get('JO_EDIT'))) return;

        $this->_template->display('./modules/joborders/Edit.php');
    }

    /*
     * Called by render() to process saving / submitting the edit page.
     */
    public function onEdit()
    {
        if ($this->_accessLevel < ACCESS_LEVEL_EDIT)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
        }

        $jobOrders = new JobOrders($this->_siteID);

        /* Bail out if we don't have a valid job order ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        $jobOrderID = $_POST['jobOrderID'];

        /* Bail out if we don't have a valid company ID. */
        if (!$this->isRequiredIDValid('companyID', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid company ID.');
        }

        /* Bail out if we don't have a valid contact ID. */
        if (!$this->isOptionalIDValid('contactID', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid contact ID.');
        }

        /* Bail out if we don't have a valid recruiter user ID. */
        if (!$this->isRequiredIDValid('recruiter', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid recruiter user ID.');
        }

        /* Bail out if we don't have a valid owner user ID. */
        if (!$this->isOptionalIDValid('owner', $_POST))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid owner user ID.');
        }

        /* Bail out if we received an invalid start date; if not, go ahead and
         * convert the date to MySQL format.
         */
        $startDate = $this->getTrimmedInput('startDate', $_POST);
        if (!empty($startDate))
        {
            if (!DateUtility::validate('-', $startDate, DATE_FORMAT_MMDDYY))
            {
                CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Invalid start date.');
                return;
            }

            /* Convert start_date to something MySQL can understand. */
            $startDate = DateUtility::convert(
                '-', $startDate, DATE_FORMAT_MMDDYY, DATE_FORMAT_YYYYMMDD
            );
        }

        /* Bail out if we received an invalid status. */
        /* FIXME: Check actual status codes. */
        if (!isset($_POST['status']) || empty($_POST['status']))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid status.');
        }

        if (isset($_POST['openings']) && !empty($_POST['openings']) &&
            !ctype_digit((string) $_POST['openings']))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Invalid number of openings.');
        }

        /* Hot job? */
        $isHot = $this->isChecked('isHot', $_POST);

        /* Public Job? */
        $public = $this->isChecked('public', $_POST);

        
        /* If it is public, is a questionnaire attached? */
        $questionnaireID =
            // If a questionnaire is provided the field will be shown and it will != 'none'
            isset($_POST['questionnaire']) && !empty($_POST['questionnaire']) &&
            strcmp($_POST['questionnaire'], 'none') && $public ?
            // The result will be an ID from the questionnaire table:
            intval($_POST['questionnaire']) :
            // If no questionnaire exists, boolean false
            false;

        $companyID         = $_POST['companyID'];
        $contactID         = $_POST['contactID'];
        $arrOwner=explode(":",$_POST['owner']);
        $owner       = isset($arrOwner[1])?trim($arrOwner[1]):0;
        $ownertype   = trim($arrOwner[0]);
        $recruiter         = $_POST['recruiter'];
        $openings          = $_POST['openings'];
        $openingsAvailable = $_POST['openingsAvailable'];

        /* Change ownership email? */
        if ($this->isChecked('ownershipChange', $_POST) && $owner > 0 && $ownertype<=0)
        {
            $jobOrderDetails = $jobOrders->get($jobOrderID);

            $users = new Users($this->_siteID);
            $ownerDetails = $users->get($_POST['owner']);

            if (!empty($ownerDetails))
            {
                $emailAddress = $ownerDetails['email'];

                /* Get the change status email template. */
                $emailTemplates = new EmailTemplates($this->_siteID);
                $statusChangeTemplateRS = $emailTemplates->getByTag(
                    'EMAIL_TEMPLATE_OWNERSHIPASSIGNJOBORDER'
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
                    '%JBODOWNER%',
                    '%JBODTITLE%',
                    '%JBODCLIENT%',
                    '%JBODID%',
                    '%JBODCATSURL%'
                );
                $replacementStrings = array(
                    $ownerDetails['fullName'],
                    $jobOrderDetails['title'],
                    $jobOrderDetails['companyName'],
                    $jobOrderID,
                    '<a href="http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) . '?m=joborders&a=show&jobOrderID=' . $jobOrderID . '">'.
                        'http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?')) . '?m=joborders&a=show&jobOrderID=' . $jobOrderID . '</a>'
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

        $title       = $this->getTrimmedInput('title', $_POST);
        $companyJobID = $this->getTrimmedInput('companyJobID', $_POST);
        $type        = $this->getTrimmedInput('type', $_POST);
        $city        = $this->getTrimmedInput('city', $_POST);
        $state       = $this->getTrimmedInput('state', $_POST);
        $status      = $this->getTrimmedInput('status', $_POST);
        $duration    = $this->getTrimmedInput('duration', $_POST);
        $department  = $this->getTrimmedInput('department', $_POST);
        $maxRate     = $this->getTrimmedInput('maxRate', $_POST);
        $salary      = $this->getTrimmedInput('salary', $_POST);
        $description = $this->getTrimmedInput('description', $_POST);
        $notes       = $this->getTrimmedInput('notes', $_POST);
        if(isset($_POST["candidate_mapping"]) && !empty($_POST["candidate_mapping"]))
            $candidate_mapping = $_POST["candidate_mapping"];
        else
            $candidate_mapping=false;
        /* Bail out if any of the required fields are empty. */
        if (empty($title) || empty($type) || empty($city) || empty($state))
        {
            CommonErrors::fatal(COMMONERROR_MISSINGFIELDS, $this, 'Required fields are missing.');
        }

        if (!eval(Hooks::get('JO_ON_EDIT_PRE'))) return;

        if (!$jobOrders->update($jobOrderID, $title, $companyJobID, $companyID, $contactID,
            $description, $notes, $duration, $maxRate, $type, $isHot,
            $openings, $openingsAvailable, $salary, $city, $state, $startDate, $status, $recruiter,
            $owner, $public, $email, $emailAddress, $department, $questionnaireID,$candidate_mapping,
            $ownertype))
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to update job order.');
        }

        /* Update extra fields. */
        $jobOrders->extraFields->setValuesOnEdit($jobOrderID);

        if (!eval(Hooks::get('JO_ON_EDIT_POST'))) return;

        CATSUtility::transferRelativeURI(
            'm=joborders&a=show&jobOrderID=' . $jobOrderID
        );
    }
    
    public function deleteSelected()
    {
        foreach($_REQUEST as $k=>$v)
        {
            if($v=="on")
            {
                $arrK=explode("_",$k);
                $id=$arrK[1];
                $JobOrders = new JobOrders($this->_siteID);
                $JobOrders->delete($id);
            }
        }
        header("Location:index.php?m={$_REQUEST["m"]}&a=search&getback=getback&mode={$_REQUEST["mode"]}&wildCardString={$_REQUEST["wildCardString"]}");exit;
    }

    /*
     * Called by render() to process deleting a job order.
     */
    public function onDelete()
    {
        if ($this->_accessLevel < ACCESS_LEVEL_DELETE)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
        }

        /* Bail out if we don't have a valid job order ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        $jobOrderID = $_GET['jobOrderID'];

        if (!eval(Hooks::get('JO_ON_DELETE_PRE'))) return;

        $joborders = new JobOrders($this->_siteID);
        $joborders->delete($jobOrderID);

        /* Delete the MRU entry if present. */
        $_SESSION['CATS']->getMRU()->removeEntry(
            DATA_ITEM_JOBORDER, $jobOrderID
        );

        if (!eval(Hooks::get('JO_ON_DELETE_POST'))) return;

        CATSUtility::transferRelativeURI('m=joborders&a=listByView');
    }

    /*
     * Called by render() to handle loading the "Add candidate to this
     * Job Order Pipeline" initial search page in the modal dialog.
     */
    public function considerCandidateSearch()
    {
        /* Bail out if we don't have a valid job order ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        $jobOrderID = $_GET['jobOrderID'];
        $objJoborder=new JobOrders($this->_siteID);
        $objJoborder->load($jobOrderID);
        $arrExtraFieldData=array();
        $arrExtraFieldDataTmp=$objJoborder->extraFields->getValues($jobOrderID);
        if($arrExtraFieldDataTmp);
        foreach($arrExtraFieldDataTmp as $arrData)
        {
            $arrExtraFieldData[$arrData["fieldName"]]=$arrData;
        }
        /* Execute the search. */
        $search = new SearchCandidates($this->_siteID);
        $arrMappingData=array();
        if($objJoborder->candidate_mapping)
        foreach($objJoborder->candidate_mapping as $mapping)
        {
            if(isset($arrExtraFieldData[$mapping]["value"]))
            {
                $arrMappingData[$mapping]=$arrExtraFieldData[$mapping]["value"];
            }
            else
            {
                $arrMappingData[$mapping]=$objJoborder->$mapping;
            }
        }
        $rs=$search->byJoborderMatching($arrMappingData);
        
        if (!eval(Hooks::get('JO_CONSIDER_CANDIDATE_SEARCH'))) return;

        $pipelines = new Pipelines($this->_siteID);
        $pipelinesRS = $pipelines->getJobOrderPipeline($jobOrderID);

        foreach ($rs as $rowIndex => $row)
        {
            if (ResultSetUtility::findRowByColumnValue($pipelinesRS,
                'candidateID', $row['candidateID']) !== false)
            {
                $rs[$rowIndex]['inPipeline'] = true;
            }
            else
            {
                $rs[$rowIndex]['inPipeline'] = false;
            }

            $rs[$rowIndex]['ownerAbbrName'] = StringUtility::makeInitialName(
                $row['ownerFirstName'],
                $row['ownerLastName'],
                false,
                LAST_NAME_MAXLEN
            );
        }

        $this->_template->assign('rs', $rs);
        $this->_template->assign('isFinishedMode', false);
        $this->_template->assign('isResultsMode', true);
        $this->_template->assign('jobOrderID', $jobOrderID);
        $this->_template->display('./modules/joborders/ConsiderSearchModal.tpl');
    }

    /*
     * Called by render() to handle processing an "Add candidate to
     * this Job Order Pipeline" search and displaying the results in the
     * modal dialog.
     */
    public function onConsiderCandidateSearch()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_POST))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        /* Bail out to prevent an error if the POST string doesn't even contain
         * a field named 'wildCardString' at all.
         */
        if (!isset($_POST['wildCardString']))
        {
            CommonErrors::fatal(COMMONERROR_WILDCARDSTRING, $this, 'No wild card string specified.');
        }

        $jobOrderID = $_POST['jobOrderID'];

        $query = $this->getTrimmedInput('wildCardString', $_POST);

        /* Get our current searching mode. */
        $mode = $this->getTrimmedInput('mode', $_POST);

        /* Execute the search. */
        $search = new SearchCandidates($this->_siteID);
        switch ($mode)
        {
            case 'searchByFullName':
                $rs = $search->byFullName($query, 'lastName', 'ASC');
                break;

            default:
                $this->listByView('Invalid search mode.');
                return;
                break;
        }

        $pipelines = new Pipelines($this->_siteID);
        $pipelinesRS = $pipelines->getJobOrderPipeline($jobOrderID);

        foreach ($rs as $rowIndex => $row)
        {
            if (ResultSetUtility::findRowByColumnValue($pipelinesRS,
                'candidateID', $row['candidateID']) !== false)
            {
                $rs[$rowIndex]['inPipeline'] = true;
            }
            else
            {
                $rs[$rowIndex]['inPipeline'] = false;
            }

            $rs[$rowIndex]['ownerAbbrName'] = StringUtility::makeInitialName(
                $row['ownerFirstName'],
                $row['ownerLastName'],
                false,
                LAST_NAME_MAXLEN
            );
        }

        $this->_template->assign('rs', $rs);
        $this->_template->assign('isFinishedMode', false);
        $this->_template->assign('isResultsMode', true);
        $this->_template->assign('jobOrderID', $jobOrderID);

        if (!eval(Hooks::get('JO_ON_CONSIDER_CANDIDATE_SEARCH'))) return;

        $this->_template->display('./modules/joborders/ConsiderSearchModal.tpl');
    }

    /*
     * Called by render() to process adding a candidate to the pipeline
     * in the modal dialog.
     */
    public function onAddToPipeline()
    {
        if ($this->_accessLevel < ACCESS_LEVEL_EDIT)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
        }

        /* Bail out if we don't have a valid job order ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('candidateID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
        }

        $jobOrderID  = $_GET['jobOrderID'];
        $candidateID = $_GET['candidateID'];

        if (!eval(Hooks::get('JO_ON_ADD_PIPELINE'))) return;

        $pipelines = new Pipelines($this->_siteID);
        if (!$pipelines->add($candidateID, $jobOrderID, $this->_userID))
        {
            CommonErrors::fatal(COMMONERROR_RECORDERROR, $this, 'Failed to add candidate to pipeline.');
        }

        $activityEntries = new ActivityEntries($this->_siteID);
        $activityID = $activityEntries->add(
            $candidateID,
            DATA_ITEM_CANDIDATE,
            400,
            'Added candidate to pipeline.',
            $this->_userID,
            $jobOrderID
        );

        $this->_template->assign('isFinishedMode', true);
        $this->_template->assign('jobOrderID', $jobOrderID);
        $this->_template->assign('candidateID', $candidateID);

        if (!eval(Hooks::get('JO_ON_ADD_PIPELINE_POST'))) return;

        $this->_template->display(
            './modules/joborders/ConsiderSearchModal.tpl'
        );
    }

    /*
     * Called by render() to handle loading the quick add candidate form
     * in the modal dialog.
     */
    public function addCandidateModal($contents = '', $fields = array())
    {
        /* Bail out if we don't have a valid job order ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        $jobOrderID = $_GET['jobOrderID'];

        $candidates = new Candidates($this->_siteID);

        /* Get possible sources. */
        $sourcesRS = $candidates->getPossibleSources();
        $sourcesString = ListEditor::getStringFromList($sourcesRS, 'name');

        /* Get extra fields. */
        $extraFieldRS = $candidates->extraFields->getValuesForAdd();

        $associatedAttachment = 0;
        $associatedAttachmentRS = array();

        $EEOSettings = new EEOSettings($this->_siteID);
        $EEOSettingsRS = $EEOSettings->getAll();

        if (is_array($parsingStatus = LicenseUtility::getParsingStatus()) &&
            isset($parsingStatus['parseLimit']))
        {
            $parsingStatus['parseLimit'] = $parsingStatus['parseLimit'] - 1;
        }

        $careerPortalSettings = new CareerPortalSettings($this->_siteID);
        $careerPortalSettingsRS = $careerPortalSettings->getAll();
        $careerPortalEnabled = intval($careerPortalSettingsRS['enabled']) ? true : false;

        /* Get questionnaires to attach (if public) */
        $questionnaire = new Questionnaire($this->_siteID);
        $questionnaires = $questionnaire->getAll(false);

        $this->_template->assign('careerPortalEnabled', $careerPortalEnabled);
        $this->_template->assign('questionnaires', $questionnaires);
        $this->_template->assign('contents', $contents);
        $this->_template->assign('isParsingEnabled', $tmp = LicenseUtility::isParsingEnabled());
        $this->_template->assign('parsingStatus', $parsingStatus);
        $this->_template->assign('extraFieldRS', $extraFieldRS);
        $this->_template->assign('sourcesRS', $sourcesRS);
        $this->_template->assign('isModal', true);
        $this->_template->assign('jobOrderID', $jobOrderID);
        $this->_template->assign('sourcesString', $sourcesString);
        $this->_template->assign('preassignedFields', $fields);
        $this->_template->assign('associatedAttachment', $associatedAttachment);
        $this->_template->assign('associatedAttachmentRS', $associatedAttachmentRS);
        $this->_template->assign('associatedTextResume', false);
        $this->_template->assign('associatedFileResume', false);
        $this->_template->assign('EEOSettingsRS', $EEOSettingsRS);

        if (!eval(Hooks::get('JO_ADD_CANDIDATE_MODAL'))) return;

        /* REMEMBER TO ALSO UPDATE CandidatesUI::add() IF APPLICABLE. */
        $this->_template->display('./modules/candidates/Add.php');
    }

    /*
     * Called by render() to handle processing the quick add candidate
     * form in the modal dialog.
     */
    public function onAddCandidateModal()
    {
        if ($this->_accessLevel < ACCESS_LEVEL_EDIT)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
        }

        /* Bail out if we don't have a valid job order ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_POST))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        $jobOrderID = $_POST['jobOrderID'];

        /* URI to transfer after candidate is successfully added. */
        $transferURI = sprintf(
            'm=candidates&a=addToPipeline&candidateID=%s&jobOrderID=%s',
            '__CANDIDATE_ID__',
            $jobOrderID
        );

        if (!eval(Hooks::get('JO_ON_ADD_CANDIDATE_MODAL'))) return;

        include_once('./modules/candidates/CandidatesUI.php');
        $candidatesUI = new CandidatesUI();

        if (is_array($mp = $candidatesUI->checkParsingFunctions()))
        {
            return $this->addCandidateModal($mp[0], $mp[1]);
        }

        $candidatesUI->publicAddCandidate(
            true, $transferURI, $this->_moduleDirectory
        );
    }

    public function addActivityChangeStatus()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('candidateID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
        }

        /* Bail out if we don't have a valid job order ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        $candidateID = $_GET['candidateID'];
        $jobOrderID  = $_GET['jobOrderID'];

        $candidates = new Candidates($this->_siteID);
        $candidateData = $candidates->get($candidateID);

        /* Bail out if we got an empty result set. */
        if (empty($candidateData))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'The specified candidate ID could not be found.');
        }

        $pipelines = new Pipelines($this->_siteID);
        $pipelineData = $pipelines->get($candidateID, $jobOrderID);

        /* Bail out if we got an empty result set. */
        if (empty($pipelineData))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'The specified pipeline entry could not be found.');
        }

        $statusRS = $pipelines->getStatusesForPicking();

        $selectedStatusID = $pipelineData['statusID'];

        /* Override default send email behavior with site specific send email behavior. */
        $mailerSettings = new MailerSettings($this->_siteID);
        $mailerSettingsRS = $mailerSettings->getAll();

        $candidateJoborderStatusSendsMessage = unserialize($mailerSettingsRS['candidateJoborderStatusSendsMessage']);

        foreach ($statusRS as $index => $status)
        {
            $statusRS[$index]['triggersEmail'] = $candidateJoborderStatusSendsMessage[$status['statusID']];
        }

        /* Get the change status email template. */
        $emailTemplates = new EmailTemplates($this->_siteID);
        $statusChangeTemplateRS = $emailTemplates->getByTag(
            'EMAIL_TEMPLATE_STATUSCHANGE'
        );
        if (empty($statusChangeTemplateRS) ||
            empty($statusChangeTemplateRS['textReplaced']))
        {
            $statusChangeTemplate = '';
            $emailDisabled = $statusChangeTemplateRS['disabled'];
        }
        else
        {
            $statusChangeTemplate = $statusChangeTemplateRS['textReplaced'];
            $emailDisabled = $statusChangeTemplateRS['disabled'];
        }

        /* Replace e-mail template variables. '%CANDSTATUS%', '%JBODTITLE%',
         * '%JBODCLIENT%' are replaced by JavaScript.
         */
        $stringsToFind = array(
            '%CANDOWNER%',
            '%CANDFIRSTNAME%',
            '%CANDFULLNAME%'
        );
        $replacementStrings = array(
            $candidateData['ownerFullName'],
            $candidateData['firstName'],
            $candidateData['firstName'] . ' ' . $candidateData['lastName']
        );
        $statusChangeTemplate = str_replace(
            $stringsToFind,
            $replacementStrings,
            $statusChangeTemplate
        );

        $calendar = new Calendar($this->_siteID);
        $calendarEventTypes = $calendar->getAllEventTypes();

        if (SystemUtility::isSchedulerEnabled() && !$_SESSION['CATS']->isDemo())
        {
            $allowEventReminders = true;
        }
        else
        {
            $allowEventReminders = false;
        }

        $this->_template->assign('candidateID', $candidateID);
        $this->_template->assign('pipelineData', $pipelineData);
        $this->_template->assign('statusRS', $statusRS);
        $this->_template->assign('selectedJobOrderID', $jobOrderID);
        $this->_template->assign('selectedStatusID', $selectedStatusID);
        $this->_template->assign('calendarEventTypes', $calendarEventTypes);
        $this->_template->assign('allowEventReminders', $allowEventReminders);
        $this->_template->assign('userEmail', $_SESSION['CATS']->getEmail());
        $this->_template->assign('onlyScheduleEvent', false);
        $this->_template->assign('statusChangeTemplate', $statusChangeTemplate);
        $this->_template->assign('emailDisabled', $emailDisabled);
        $this->_template->assign('isFinishedMode', false);
        $this->_template->assign('isJobOrdersMode', true);

        if (!eval(Hooks::get('JO_ADD_ACTIVITY_CHANGE_STATUS'))) return;

        $this->_template->display(
            './modules/candidates/AddActivityChangeStatusModal.tpl'
        );
    }

    public function onAddActivityChangeStatus()
    {
        if ($this->_accessLevel < ACCESS_LEVEL_EDIT)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
        }

        /* Bail out if we don't have a valid regarding job order ID. */
        if (!$this->isRequiredIDValid('regardingID', $_POST))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        $regardingID = $_POST['regardingID'];

        if (!eval(Hooks::get('JO_ON_ADD_ACTIVITY_CHANGE_STATUS'))) return;

        include_once('./modules/candidates/CandidatesUI.php');
        $candidatesUI = new CandidatesUI();
        $candidatesUI->publicAddActivityChangeStatus(
            true, $regardingID, $this->_moduleDirectory
        );exit;
    }

    /*
     * Called by render() to process removing a candidate from the
     * pipeline for  a job order.
     */
    public function onRemoveFromPipeline()
    {
        if ($this->_accessLevel < ACCESS_LEVEL_DELETE)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
        }

        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('candidateID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid candidate ID.');
        }

        /* Bail out if we don't have a valid job order ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid job order ID.');
        }

        $candidateID = $_GET['candidateID'];
        $jobOrderID  = $_GET['jobOrderID'];

        if (!eval(Hooks::get('JO_ON_REMOVE_PIPELINE'))) return;

        $pipelines = new Pipelines($this->_siteID);
        $pipelines->remove($candidateID, $jobOrderID);

        if (!eval(Hooks::get('JO_ON_REMOVE_PIPELINE_POST'))) return;

        CATSUtility::transferRelativeURI(
            'm=joborders&a=show&jobOrderID=' . $jobOrderID
        );
    }

    /*
     * Called by render() to process loading the search page.
     */
    public function search()
    {
        $savedSearches = new SavedSearches($this->_siteID);
        $savedSearchRS = $savedSearches->get(DATA_ITEM_JOBORDER);

        $this->_template->assign('savedSearchRS', $savedSearchRS);
        $this->_template->assign('wildCardString', '');
        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Search Job Orders');
        $this->_template->assign('isResultsMode', false);
        $this->_template->assign('wildCardString_companyName', '');
        $this->_template->assign('wildCardString_jobTitle', '');
        $this->_template->assign('mode', '');

        if (!eval(Hooks::get('JO_SEARCH'))) return;

        $this->_template->display('./modules/joborders/Search.php');
    }

    /*
     * Called by render() to process displaying the search results.
     */
    public function onSearch()
    {
        $query_jobTitle = '';
        $query_companyName = '';

        /* Bail out to prevent an error if the GET string doesn't even contain
         * a field named 'wildCardString' at all.
         */
        if (!isset($_GET['wildCardString']))
        {
            $this->listByView('No wild card string specified.');
            return;
        }

        $query = trim($_GET['wildCardString']);

        /* Set up sorting. */
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
            $sortBy = 'title';
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
            array('sortBy', 'sortDirection', 'page'), '&'
        );
        $searchPager->setSortByParameters($baseURL, $sortBy, $sortDirection);

        /* Get our current searching mode. */
        $mode = $this->getTrimmedInput('mode', $_GET);

        /* Execute the search. */
        $search = new SearchJobOrders($this->_siteID);
        switch ($mode)
        {
            case 'searchByJobTitle':
                $query_jobTitle = $query;
                $rs = $search->byTitle($query, $sortBy, $sortDirection, false);
                break;

            case 'searchByCompanyName':
                $query_companyName = $query;
                $rs = $search->byCompanyName($query, $sortBy, $sortDirection, false);
                break;

            default:
                $this->listByView('Invalid search mode.');
                return;
                break;
        }

        foreach ($rs as $rowIndex => $row)
        {
            /* Convert '00-00-00' dates to empty strings. */
            $rs[$rowIndex]['startDate'] = DateUtility::fixZeroDate(
                $row['startDate']
            );

            if ($row['isHot'] == 1)
            {
                $rs[$rowIndex]['linkClass'] = 'jobLinkHot';
            }
            else
            {
                $rs[$rowIndex]['linkClass'] = 'jobLinkCold';
            }

            $rs[$rowIndex]['recruiterAbbrName'] = StringUtility::makeInitialName(
                $row['recruiterFirstName'],
                $row['recruiterLastName'],
                false,
                LAST_NAME_MAXLEN
            );

            $rs[$rowIndex]['ownerAbbrName'] = StringUtility::makeInitialName(
                $row['ownerFirstName'],
                $row['ownerLastName'],
                false,
                LAST_NAME_MAXLEN
            );
        }

        /* Save the search. */
        $savedSearches = new SavedSearches($this->_siteID);
        $savedSearches->add(
            DATA_ITEM_JOBORDER,
            $query,
            $_SERVER['REQUEST_URI'],
            false
        );

        $savedSearchRS = $savedSearches->get(DATA_ITEM_JOBORDER);

        $query = urlencode(htmlspecialchars($query));

        $jobOderIDs = implode(',', ResultSetUtility::getColumnValues($rs, 'jobOrderID'));
        $exportForm = ExportUtility::getForm(
            DATA_ITEM_JOBORDER, $jobOderIDs, 29, 5
        );

        $this->_template->assign('active', $this);
        $this->_template->assign('subActive', 'Search Job Orders');
        $this->_template->assign('pager', $searchPager);
        $this->_template->assign('exportForm', $exportForm);

        $this->_template->assign('wildCardString', $query);
        $this->_template->assign('wildCardString_jobTitle', $query_jobTitle);
        $this->_template->assign('wildCardString_companyName', $query_companyName);
        $this->_template->assign('savedSearchRS', $savedSearchRS);
        $this->_template->assign('rs', $rs);
        $this->_template->assign('isResultsMode', true);
        $this->_template->assign('mode', $mode);

        if (!eval(Hooks::get('JO_ON_SEARCH'))) return;

        $this->_template->display('./modules/joborders/Search.php');
    }

    /*
     * Called by render() to process loading the create attachment
     * modal dialog.
     */
    public function createAttachment()
    {
        /* Bail out if we don't have a valid joborder ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid joborder ID.');
        }

        $jobOrderID = $_GET['jobOrderID'];

        $this->_template->assign('isFinishedMode', false);
        $this->_template->assign('jobOrderID', $jobOrderID);

        if (!eval(Hooks::get('JO_CREATE_ATTACHMENT'))) return;

        $this->_template->display(
            './modules/joborders/CreateAttachmentModal.php'
        );
    }

    /*
     * Called by render() to process creating an attachment.
     */
    public function onCreateAttachment()
    {
        if ($this->_accessLevel < ACCESS_LEVEL_EDIT)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
        }

        /* Bail out if we don't have a valid joborder ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_POST))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid joborder ID.');
        }

        $jobOrderID = $_POST['jobOrderID'];

        if (!eval(Hooks::get('JO_ON_CREATE_ATTACHMENT_PRE'))) return;

        $attachmentCreator = new AttachmentCreator($this->_siteID);
        $attachmentCreator->createFromUpload(
            DATA_ITEM_JOBORDER, $jobOrderID, 'file', false, false
        );

        if ($attachmentCreator->isError())
        {
            CommonErrors::fatalModal(COMMONERROR_FILEERROR, $this, $attachmentCreator->getError());
        }

        if (!eval(Hooks::get('JO_ON_CREATE_ATTACHMENT_POST'))) return;

        $this->_template->assign('isFinishedMode', true);
        $this->_template->assign('jobOrderID', $jobOrderID);

        $this->_template->display(
            './modules/joborders/CreateAttachmentModal.php'
        );
    }

    /*
     * Called by render() to process deleting an attachment.
     */
    public function onDeleteAttachment()
    {
        if ($this->_accessLevel < ACCESS_LEVEL_DELETE)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
        }

        /* Bail out if we don't have a valid attachment ID. */
        if (!$this->isRequiredIDValid('attachmentID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid attachment ID.');
        }

        /* Bail out if we don't have a valid joborder ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid Job Order ID.');
        }

        $jobOrderID  = $_GET['jobOrderID'];
        $attachmentID = $_GET['attachmentID'];

        if (!eval(Hooks::get('JO_ON_DELETE_ATTACHMENT_PRE'))) return;

        $attachments = new Attachments($this->_siteID);
        $attachments->delete($attachmentID);

        if (!eval(Hooks::get('JO_ON_DELETE_ATTACHMENT_POST'))) return;

        CATSUtility::transferRelativeURI(
            'm=joborders&a=show&jobOrderID=' . $jobOrderID
        );
    }

    //Only accessable by MSA users - hides this job order from everybody by
    // FIXME: Document me.
    public function administrativeHideShow()
    {
        if ($this->_accessLevel < ACCESS_LEVEL_MULTI_SA)
        {
            CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
        }

        /* Bail out if we don't have a valid joborder ID. */
        if (!$this->isRequiredIDValid('jobOrderID', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid Job Order ID.');
        }

        /* Bail out if we don't have a valid status ID. */
        if (!$this->isRequiredIDValid('state', $_GET, true))
        {
            CommonErrors::fatal(COMMONERROR_BADINDEX, $this, 'Invalid state ID.');
        }

        $jobOrderID = $_GET['jobOrderID'];

        // FIXME: Checkbox?
        (boolean) $state = $_GET['state'];

        $joborders = new JobOrders($this->_siteID);
        $joborders->administrativeHideShow($jobOrderID, $state);

        CATSUtility::transferRelativeURI('m=joborders&a=show&jobOrderID='.$jobOrderID);
    }

    /**
     * Formats SQL result set for display. This is factored out for code
     * clarity.
     *
     * @param array result set from listByView()
     * @return array formatted result set
     */
    private function _formatListByViewResults($resultSet)
    {
        if (empty($resultSet))
        {
            return $resultSet;
        }

        foreach ($resultSet as $rowIndex => $row)
        {
            /* Get info strings for popup titles */
            $resultSet[$rowIndex]['jobOrderInfo'] = InfoString::make(
                DATA_ITEM_JOBORDER,
                $resultSet[$rowIndex]['jobOrderID'],
                $this->_siteID
            );
            $resultSet[$rowIndex]['companyInfo'] = InfoString::make(
                DATA_ITEM_COMPANY,
                $resultSet[$rowIndex]['companyID'],
                $this->_siteID
            );

            /* Truncate job order title. */
            if (strlen($resultSet[$rowIndex]['title']) > self::TRUNCATE_JOBORDER_TITLE)
            {
                $resultSet[$rowIndex]['title'] = substr(
                    $resultSet[$rowIndex]['title'],
                    0,
                    self::TRUNCATE_JOBORDER_TITLE
                ) . "...";
            }

            /* Truncate company name. */
            if (strlen($resultSet[$rowIndex]['companyName']) > self::TRUNCATE_CLIENT_NAME)
            {
                $resultSet[$rowIndex]['companyName'] = substr(
                    $resultSet[$rowIndex]['companyName'],
                    0,
                    self::TRUNCATE_CLIENT_NAME
                ) . "...";
            }

            /* Convert '00-00-00' dates to empty strings. */
            $resultSet[$rowIndex]['startDate'] = DateUtility::fixZeroDate(
                $resultSet[$rowIndex]['startDate']
            );

            /* Hot jobs [can] have different title styles than normal
             * jobs.
             */
            if ($resultSet[$rowIndex]['isHot'] == 1)
            {
                $resultSet[$rowIndex]['linkClass'] = 'jobLinkHot';
            }
            else
            {
                $resultSet[$rowIndex]['linkClass'] = 'jobLinkCold';
            }

            $resultSet[$rowIndex]['recruiterAbbrName'] = StringUtility::makeInitialName(
                $resultSet[$rowIndex]['recruiterFirstName'],
                $resultSet[$rowIndex]['recruiterLastName'],
                false,
                LAST_NAME_MAXLEN
            );

            $resultSet[$rowIndex]['ownerAbbrName'] = StringUtility::makeInitialName(
                $resultSet[$rowIndex]['ownerFirstName'],
                $resultSet[$rowIndex]['ownerLastName'],
                false,
                LAST_NAME_MAXLEN
            );

            if ($resultSet[$rowIndex]['attachmentPresent'] == 1)
            {
                $resultSet[$rowIndex]['iconTag'] = '<img src="images/paperclip.gif" alt="" width="16" height="16" />';
            }
            else
            {
                $resultSet[$rowIndex]['iconTag'] = '&nbsp;';
            }
        }

        if (!eval(Hooks::get('JO_FORMAT_LIST_BY_VIEW_RESULTS'))) return;

        return $resultSet;
    }
}

?>
