<?php
class ContactTemplate extends ModuleEmailTemplate
{
    public $contacts=null;
    public $companies=null;
    private $_siteID;
    public function __construct($siteID)
    {
        $this->_siteID=$siteID;
        parent::__construct("contacts");
    }
    public function load($contactID)
    {
        $this->companies = new Companies($this->_siteID);
        $this->contacts=new Contacts($this->_siteID);
        $this->contacts->load($contactID);
        $company_id=$this->contacts->company_id;
        if(!empty($company_id))
        {
            $this->companies->load($company_id);
        }
    }
}
?>