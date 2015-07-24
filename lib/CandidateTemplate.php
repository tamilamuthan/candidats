<?php
class CandidateTemplate extends ModuleEmailTemplate
{
    public $candidates=null;
    public $joborders=null;
    public $companies=null;
    public $users=null;
    private $_siteID;
    public function __construct($siteID)
    {
        $this->_siteID=$siteID;
        parent::__construct("candidates");
    }
    public function load($candidateID,$joborderID=false)
    {
        $this->candidates=new Candidates($this->_siteID);
        $this->candidates->load($candidateID);
        $this->joborders=new JobOrders($this->_siteID);
        $this->companies = new Companies($this->_siteID);
        $this->users=new Users($this->_siteID);
        $this->users->load($_SESSION["CATS"]->getUserID());
        if($joborderID!==false)
        {
            $this->joborders->load($joborderID);
            $company_id=$this->joborders->company_id;
            if(!empty($company_id))
            {
                $this->companies->load($company_id);
            }
        }
    }
}
?>