<?php
class ClsAuieoView
{
    private $usersRS=array();
    private $groupRS=array();
    private $_siteID=0;
    private $id=0;
    protected $data=array();
    protected $moduleInfo=array();
    public function __construct($module)
    {
        $this->moduleInfo=getModuleInfo("modulename",$module);
        $this->_siteID=$_SESSION["CATS"]->getSiteID();
        $users = new Users($this->_siteID);
        $this->usersRS = $users->getSelectList();
        $this->groupRS = $users->getSelectGroupList();
    }
    public function _($value)
    {
        echo $value;
    }
     protected function processField($fieldData,$defaultValue,$additionalValue=0)
    {
         $fieldName=$fieldData["fieldname"];
        $isCustomDropdown=false;
        if($fieldData["uitype"]>10000)
        {
            $customDropdownID=$fieldData["uitype"]-10000;
            $sql="select * from auieo_dropdown where id={$customDropdownID}";
            $objDB=DatabaseConnection::getInstance();
            $record=$objDB->getAssoc($sql);
            if($record)
            {
                $isCustomDropdown=true;
                $sql="select * from auieo_dropdowndata where dropdown_id={$customDropdownID}";
                $records=$objDB->getAllAssoc($sql);
                if($records)
                {
                     $AUIEO_CAPTION=$fieldData["fieldlabel"];
                     ob_start();
                    ?>

                    <select id="<?php echo $fieldName; ?>" name="<?php echo $fieldName; ?>" class="inputbox" style="width: 150px;">
                        <?php foreach ($records as $record)
                            {
                                $selected="";
                                if($record["id"]==$defaultValue) $selected=" selected";
                                echo "<option value='{$record['id']}' {$selected}>{$record['data']}</option>";
                            } ?>
                    </select>
                    <?php
                    $AUIEO_DATA=ob_get_clean();
                }
                else {
                     $arrRenderSerialize[]="-None-";
                }
            }
        }
        if($isCustomDropdown===false)
        {
            $fieldInfo=getFieldInfoByUIType($fieldData["fieldinfo"]);
            if($fieldData["displaytype"]<=0) return false;
            $AUIEO_CAPTION=$fieldData["fieldlabel"];
            if($fieldInfo["uicontrol"]=="OWNER")
            {
                $AUIEO_DATA=$this->getOwnerUI($defaultValue,$additionalValue);
            }
            else if($fieldInfo["uicontrol"]=="CALENDAR")
            {
                $AUIEO_DATA=$this->getDateUI($fieldName,$defaultValue);
            }
            else
            {
                $AUIEO_DATA="<input name='{$fieldName}' type='textbox' value='{$defaultValue}' />";
            }
        }
        return array("caption"=>$AUIEO_CAPTION,"data"=>$AUIEO_DATA);
    }
    public function setID($id)
    {
        $this->id=$id;
        $sql="select * from auieo_projects where id={$id}";
        $this->data=  DatabaseConnection::getInstance()->getAssoc($sql);
    }
    /**
     * return only capital letter variables with AUIEO keyword
     * @param type $__AUIEO__TEMPLATE__FILE
     * @return type
     */
    private function &loadTemplateVars($__AUIEO__TEMPLATE__FILE)
    {
        if(!file_exists($__AUIEO__TEMPLATE__FILE)) return array();
        include $__AUIEO__TEMPLATE__FILE;
        $arrVar=get_defined_vars();
        unset($arrVar[$__AUIEO__TEMPLATE__FILE]);
        $arrVarNew=array();
        foreach($arrVar as $var=>$data)
        {
            $tmpVar = strtoupper($var);
            if(strpos($tmpVar, "AUIEO")===false) continue;
            if(isset($$tmpVar))
            {
                $arrVarNew[$tmpVar]=$data;
            }
        }
        return $arrVarNew;
    }
    public function getDateUI($fieldName,$fieldValue="")
    {
        $date="  <script>
  jQuery(function() {
    jQuery( '#{$fieldName}' ).datepicker({dateFormat: 'yy-mm-dd'});
  });
  </script>
<input type='text' name='{$fieldName}' id='{$fieldName}' value='{$fieldValue}' />  
";
        return $date;
    }
    public function getOwnerUI($ownerSelected=-1,$ownerType=0)
    {
        ob_start();
?>
<select id="assignedto" name="assignedto" class="inputbox" style="width: 150px;">
    <optgroup label="User">
    <?php foreach ($this->usersRS as $rowNumber => $usersData)
        {
            $selected="";
            if($ownerType<=0 && $ownerSelected>-1&&$usersData['userID']==$ownerSelected) $selected=" selected";
            echo "<option value='0:{$usersData['userID']}' {$selected}>{$usersData['lastName']}, {$usersData['firstName']}</option>";
        } ?>
    </optgroup>
    <optgroup label="Group">
<?php 
foreach ($this->groupRS as $rowNumber => $groupData)
{
    $selected="";
    if($ownerType>0 && $ownerSelected>-1&&$groupData['id']==$ownerSelected) $selected=" selected";
    echo "<option value='1:{$groupData['id']}' {$selected}>{$groupData['groupname']}</option>";
} 
?>
    </optgroup>
</select>
<?php
return ob_get_clean();
    }
    public function &processTemplate()
    {
        /**
        * for handing comment in html template. usage is {$_("This is comment")}
        */
       $_=function($comment)
       {
           return "";
       };
 
        /* Include the template, with output buffering on, and echo it. */
        $arrPathInfo=pathinfo($this->_templateFile);
        if($arrPathInfo["extension"]=="php" && (file_exists("{$arrPathInfo["dirname"]}/{$arrPathInfo["filename"]}.html") || file_exists("{$arrPathInfo["dirname"]}/{$arrPathInfo["filename"]}.htm")))
        {
           $arrTplVar=$this->loadTemplateVars($this->_templateFile);
           extract($arrTplVar);
            if(file_exists("{$arrPathInfo["dirname"]}/{$arrPathInfo["filename"]}.html"))
                $_AUIEO_TEMPLATE_CONTENT=file_get_contents("{$arrPathInfo["dirname"]}/{$arrPathInfo["filename"]}.html");
            else
                $_AUIEO_TEMPLATE_CONTENT=file_get_contents("{$arrPathInfo["dirname"]}/{$arrPathInfo["filename"]}.htm");
            try
            {
                ob_start();
                $AUIEO_MODULE_CONTENT="";
                eval('echo <<< EOT
        '.$_AUIEO_TEMPLATE_CONTENT.'
EOT;
');
                $html = ob_get_clean();
            }
            catch(Exception $e)
            {
                trace($e);
            }
        }
        else
        {
            ob_start();
            include($this->_templateFile);
            $html = ob_get_clean();
        }
        if (strpos($html, '<!-- NOSPACEFILTER -->') === false && strpos($html, 'textarea') === false)
        {
            $html = preg_replace('/^\s+/m', '', $html);
        }
        $html=isset($html)?$html:"";
        return $html;
    }
}