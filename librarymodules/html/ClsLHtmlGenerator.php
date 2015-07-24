<?php
class ClsLHtmlGenerator
{
    private $arrObject=array();
    public function __construct()
    {
        
    }
    /**
     * 
     * @param type $name
     * @param type $cols - either number of columns or array of header col data
     * @param type $arrAttribute
     * @return type
     */
    public function &getTable($name,$cols,$arrAttribute = array() )
    {
        $id=isset($arrAttribute["id"])?$arrAttribute["id"]:null;
        $class=isset($arrAttribute["class"])?$arrAttribute["class"]:null;
        unset($arrAttribute["id"]);
        unset($arrAttribute["class"]);
        $this->arrObject[$name] = new ClsLHtmlTable($cols);
        return $this->arrObject[$name];
    }
    public static function generateSelect($name,$arrAttribute,$defaultValue="")
    {
        $select="<select name='{$name}'>
	<!--<option value='text'>text</option>-->";
	if($arrAttribute)
	foreach($arrAttribute as $key=>$value)
	{
		if($key==$defaultValue)
			$select=$select."<option selected='selected' value='".$key."'>".$value."</option>";
		else
			$select=$select."<option value='".$key."'>".$value."</option>";
	}
	$select=$select."</select>";
	return $select;
    }
    public function &getHtmlSelect($name,$arrAssoc)
    {
        $objHtmlSelect=new ClsLHtmlselect($name,$arrAssoc);
        return $objHtmlSelect;
    }
    public function &getHtmlTable($cols)
    {
        $objHtmlTable=new ClsLHtmltable($cols);
        return $objHtmlTable;
    }
    public function render()
    {
        
    }
}
?>
