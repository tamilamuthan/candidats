<?php
class ClsJoborderView
{
    private $data=null;
    public function __construct($data) 
    {
        $this->data=$data;
    }
    public function setVar($var,$data)
    {
        $this->$var=$data;
    }
    public function &render($hookFunction)
    {Logger::getLogger("AuieoATS")->info("inside render() under ClsJoborderView");
        $objSQL=new ClsAuieoSQL();
        $objFromCandidate=$objSQL->addFrom("auieo_fields");
        $objSQL->addWhere($objFromCandidate, "data_item_type", 400);
        $objSQL->addWhere($objFromCandidate, "site_id", $_SESSION['CATS']->getSiteID());
        $objSQL->addOrderBy("sequence", false);
        $sql=$objSQL->render();
        $db=  DatabaseConnection::getInstance();
        $arrFieldRecord=$db->getAllAssoc($sql);
        $arrRenderSerialize=array();
        $arrRender=array();
        /**
         * process fields from database
         */
        $record=$this->data;//trace($record);
        //for Company radio
        //trace($this->defaultCompanyID);
        $company=array();
        if($_REQUEST["a"]=="edit")
        {
            
        
            if ($this->defaultCompanyID !== false)
            {
                $arrField=array();
                $arrField["definition"]=array("displaytype"=>1,"fieldname"=>"Companyrtyrt7u","fieldlabel"=>"comp:","uitype"=>1,"sequence"=>100);
                if ($this->defaultCompanyID != $this->data['company_id'])
                {//trace("=========");
                    $value="checked";
                    $arrField["data"]="<input type='radio' name='typeCompany' {$value} onchange='document.getElementById('companyName').disabled = false; if (oldCompanyID != -1) document.getElementById('companyID').value = oldCompanyID;'>";
                    if($this->defaultCompanyID == $this->data['company_id'])
                    {
                        $val="disabled";
                        $arrField["data"]="<input type='text' name='companyName' id='companyName' tabindex='2' value='{$this->data['name']}' class='inputbox' style='width: 125px' onFocus='suggestListActivate('getCompanyNames', 'companyName', 'CompanyResults', 'companyID', 'ajaxTextEntryHover', 0, '{$this->sessionCookie}', 'helpShim');' {$val}/>";
                    }
                }
                else
                {//trace("===");
                    $val="disabled";
                    $arrField["data"]="<input type='text' name='companyName' id='companyName' tabindex='2' value='{$this->data['name']}' class='inputbox' style='width: 150px' onFocus='suggestListActivate('getCompanyNames', 'companyName', 'CompanyResults', 'companyID', 'ajaxTextEntryHover', 0, {$this->sessionCookie}, 'helpShim');' {$val}/>&nbsp;*";
                }

                $company[]=$arrField;  
            }
        
        //for attachment
            $contact=array();
            $arrField=array();
            $arrField["definition"]=array("displaytype"=>1,"fieldname"=>"Contact","fieldlabel"=>"Contact:","uitype"=>1,"sequence"=>100);
            $dataval="<select tabindex='3' id='contactID' name='contactID' class='inputbox' style='width: 150px;'>";   
            $dataval=$dataval."<option value='-1'>None</option>";
            foreach ($this->contactsRS as $rowNumber => $contactsData)
            {
            if ($this->data['contact_id'] == $contactsData['contactID'])
            {
                $dataval=$dataval."<option selected value='{$contactsData["contactID"]}'>{$contactsData['lastName']}, {$contactsData['firstName']}</option>";
            }
            else
            {
                $dataval=$dataval."<option value='{$contactsData['contactID']}'>{$contactsData['lastName']}, {$contactsData['firstName']}</option>";
            }
            $dataval=$dataval."</select>";
            $arrField["data"]=$dataval;
            }
            $contact[]=$arrField;
                foreach ($contact as $ind=>$fieldinfo)
                {//trace($fieldinfo);
                    $arrFieldRecord[]=$fieldinfo["definition"];
                    $this->data[$fieldinfo["definition"]["fieldname"]]=$fieldinfo["data"];//trace($fieldinfo["data"]);
                }
                foreach ($company as $ind=>$fieldinfo)
                {//trace($fieldinfo);
                    $arrFieldRecord[]=$fieldinfo["definition"];
                    $this->data[$fieldinfo["definition"]["fieldname"]]=$fieldinfo["data"];//trace($fieldinfo["data"]);
                }
        }    

        //trace($this->attachmentsRS);
        /*foreach ($this->attachmentsRS as $rowNumber => $attachmentsData)
        {//trace($attachmentsData);
            $arrField=array();
            $arrField["definition"]=array("displaytype"=>1,"fieldname"=>$this->EEOValues[$i]['fieldName'],"fieldlabel"=>"{$this->EEOValues[$i]['fieldName']}:","uitype"=>1,"sequence"=>100);
            //$arrField["data"]={$attachmentsData['retrievalLink']}."<img src={$attachmentsData['attachmentIcon']} alt='' width='16' height='16' border='0' />".{$attachmentsData['originalFilename']};  
            
        }*/
        
        /*if($this->accessLevel >= ACCESS_LEVEL_DELETE)
        {
            $arrField=array();
            $arrField["definition"]=array("displaytype"=>1,"fieldname"=>"UpcomingEvents","fieldlabel"=>"UpcomingEvents:","uitype"=>1,"sequence"=>100);
            $arrField["data"]= "<a href='index.php?m=joborders&amp;a=deleteAttachment&amp;jobOrderID{$this->data["joborder_id"]}&amp;attachmentID={$attachmentsData['attachmentID']}  title='Delete' onclick='javascript:return confirm('Delete this attachment?');'
                                        <img src='images/actions/delete.gif' width='16' height='16' border='0' />
                                    </a>";                                
            $attachment[]=$arrField;  
        }*/
        //for pipeline
        //trace($this->pipelineGraph);
        /*if($_REQUEST["a"]=="show")
        {
            $pipeline=array();    
            $arrField=array();
            $arrField["definition"]=array("displaytype"=>1,"fieldname"=>"pipeline","fieldlabel"=>"JobOrder Pipeline","uitype"=>1,"sequence"=>100);
            $arrField["data"]=$this->pipelineGraph;                                
            $pipeline[]=$arrField;  
            
            foreach ($pipeline as $ind=>$fieldinfo)
            {trace($fieldinfo);
                $arrFieldRecord[]=$fieldinfo["definition"];
                $this->data[$fieldinfo["definition"]["fieldname"]]=$fieldinfo["data"];//trace($fieldinfo["data"]);
            }
        
        }
        */
        
        //trace($record);
        
        
        $arrCalculateField=getAVFields(400, $record);
        foreach ($arrCalculateField as $ind=>$fieldinfo)
        {//trace($fieldinfo);
            $arrFieldRecord[]=$fieldinfo["definition"];
            $this->data[$fieldinfo["definition"]["fieldname"]]=$fieldinfo["data"];
        }//trace($arrCalculateField);
        /**
         * build sequence array
         */
        foreach($arrFieldRecord as $ind=>$fieldData)
        {
            $sequence=isset($fieldData["sequence"])?$fieldData["sequence"]:0;
            $arrFieldSeq[$sequence][]=$fieldData;
        }
        krsort($arrFieldSeq, SORT_NUMERIC);
                //trace($arrFieldSeq);
        foreach($arrFieldSeq as $sequence=>$arrField)
        {//trace($arrFieldSeq);
            foreach($arrField as $ind=>$fieldData)
            {
                if($fieldData["displaytype"]<=0) continue;//trace($fieldData);
				//if(!isset($this->data["entered_by"])) continue;
                $fieldName=$fieldData["fieldname"];//trace($fieldData);
                //$k=  getAliasNameFromField($fieldName);
                
               // if(!isset($this->data["entered_by"])) continue;trace($this->data);
                $v=$this->data[$fieldName];
				
                /**
                 * process hook
                 
				 if($fieldName=="experience")
				 {
					$caption=getLangVar($fieldName);//trace($caption);
				 }*/
                $caption=getLangVar($fieldName,"joborders");//trace($fieldName);
                $ret=$hookFunction($fieldName,$v,$this->data);//trace($fieldData);
                if($ret)
                {
                    if($ret===true) 
                    {//trace($fieldData);
                        $arrRenderSerialize[]=empty($caption)?$fieldData["fieldlabel"]:$caption;
                        $arrRenderSerialize[]=$v;
                    }
                    else if(is_string($ret))
                    {//trace("==========");
                        $html_template_content=$ret;
                        $arrRenderSerialize[]=empty($caption)?$fieldData["fieldlabel"]:$caption;
                        $arrRenderSerialize[]=$this->loadTemplate($html_template_content, $this->data);
                    }
                    else if(is_numeric($ret)) 
                    {//trace("==========");
                        $arrRenderSerialize[]=empty($caption)?$fieldData["fieldlabel"]:$caption;
                        $arrRenderSerialize[]=$ret;
                    }
                    else if(is_object($ret))
                    {//trace("==========");
                        $html_template_content=(string)$ret;
                        $arrRenderSerialize[]=empty($caption)?$fieldData["fieldlabel"]:$caption;
                        $arrRenderSerialize[]=$this->loadTemplate($html_template_content, $this->data);
                    }
                }
            }
        }
		//trace($this->data);
		//trace($arrRenderSerialize);
        $columnPrefix="col";
        $arrRenderView =  multi_dimension_array($arrRenderSerialize, 4, $columnPrefix);//trace($arrRenderView);
        return $arrRenderView;
    }
    public function loadTemplate($html_content,$arrTplVar=array())
    {
        if(!empty($arrTplVar))
        {
            extract($arrTplVar);
        }
        /**
        * for handing comment in html template. usage is {$_("This is comment")}
        */
       $_=function($comment)
       {
           return "";
       };
        ob_start();
        eval('echo <<< EOT
        '.$html_content.'
EOT;
');
        $html = ob_get_clean();
        return $html;
    }
}
?>