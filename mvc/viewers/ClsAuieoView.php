<?php
class ClsAuieoView
{
    private $usersRS=array();
    private $groupRS=array();
    private $_siteID=0;
    private $id=0;
    protected $data=array();
    public function __construct()
    {
        $this->_siteID=$_SESSION["CATS"]->getSiteID();
        $users = new Users($this->_siteID);
        $this->usersRS = $users->getSelectList();
        $this->groupRS = $users->getSelectGroupList();
    }
    public function _($value)
    {
        echo $value;
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
<select id="owner" name="owner" class="inputbox" style="width: 150px;">
    <option value="-1">None</option>
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