<?php
class JoborderTemplate extends ModuleEmailTemplate
{
    public $candidates=null;
    public $joborders=null;
    public $companies=null;
    public $users=null;
    public $recruiter=null;
    private $_siteID;
    public function __construct($siteID)
    {
        $this->_siteID=$siteID;
        parent::__construct("joborders");
    }
    public function load($joborderID)
    {
        $this->joborders=new JobOrders($this->_siteID);
        $this->companies = new Companies($this->_siteID);
        $this->users=new Users($this->_siteID);
        $this->users->load($_SESSION["CATS"]->getUserID());

        $this->joborders->load($joborderID);
        $company_id=$this->joborders->company_id;
        $record_recruiter=$this->joborders->recruiter;
        $record_owner=$this->joborders->owner;
        
        $this->recruiter=new Users($this->_siteID);
        $this->recruiter->load($record_recruiter);
        
        $this->owner=new Users($this->_siteID);
        $this->owner->load($record_owner);
        
        if(!empty($company_id))
        {
            $this->companies->load($company_id);
        }
    }
}
?>