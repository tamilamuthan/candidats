<?php
class ClsContactsFilter extends ClsNaanalFilter
{
    protected $arrExtraField=array();
    public function __construct()
    {
        $companies = new Contacts($_SESSION['CATS']->getSiteID());
        $arr = $companies->extraFields->getSettings();
        foreach($arr as $arrFieldData)
        {
            $this->arrExtraField[$arrFieldData["extraFieldSettingsID"]]=$arrFieldData["fieldName"];
        }
        parent::__construct();
    }
    public static function getInstance() 
    {
        $obj=new ClsContactsFilter();
        return $obj;
    }
    protected function on_column_display($field)
    {
        if($field=="middle_name" || $field=="address" || $field=="notes" || $field=="current_employer" || $field=="email1" || $field=="email2" || $field=="web_site" || $field=="import_id" || $field=="is_hot" || $field=="eeo_ethnic_type_id" || $field=="eeo_veteran_type_id" || $field=="eeo_disability_status" || $field=="eeo_gender" || $field=="is_active" || $field=="is_admin_hidden" || $field=="best_time_to_call") return null;
        $arrField=explode("_",$field);
        $display="";
        foreach($arrField as $fieldpart)
        {
            if($display=="")
            {
                $display=$display.ucfirst($fieldpart);
            }
            else
            {
                $display=$display." ".ucfirst($fieldpart);
            }
        }
        return array("value"=>$field,"display"=>$display);
    }
    public function getFilter()
    {
        return $this->getNaanalFilter("contact","index.php");
    }
}
?>