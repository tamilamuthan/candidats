<?php
class ClsCandidateView
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
    {
        $objSQL=new ClsAuieoSQL();
        $objFromCandidate=$objSQL->addFrom("auieo_fields");
        $objSQL->addWhere($objFromCandidate, "data_item_type", 100);
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
        
        $eeoField=array();
        /*
         * $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Email:","uitype"=>1,"sequence"=>100);
    $arrField["data"]=$record["email1"];
    $arrField["data"]
         */
    if($_REQUEST["a"]=="show")
    {
        if(isset($this->EEOSettingsRS['enabled']) && $this->EEOSettingsRS['enabled'] == 1)
        {
            for ($i = 0; $i < intval(count($this->EEOValues)/2); $i++)
            {
                $arrField=array();
                $arrField["definition"]=array("displaytype"=>1,"fieldname"=>$this->EEOValues[$i]['fieldName'],"fieldlabel"=>"{$this->EEOValues[$i]['fieldName']}:","uitype"=>1,"sequence"=>100);
                if($this->EEOSettingsRS['canSeeEEOInfo'])
                {
                    $arrField["data"]=$this->EEOValues[$i]['fieldValue'];
                }
                else
                { 
                    $arrField["data"]=="<i><a href='javascript:void(0);' title='Ask an administrator to see the EEO info, or have permission granted to see it.'>(Hidden)</a></i>";
                }
                $eeoField[]=$arrField;
            }

            for ($i = (intval(count($this->EEOValues))/2); $i < intval(count($this->EEOValues)); $i++)
            {
                $arrField=array();
                $arrField["definition"]=array("displaytype"=>1,"fieldname"=>$this->EEOValues[$i]['fieldName'],"fieldlabel"=>"{$this->EEOValues[$i]['fieldName']}:","uitype"=>1,"sequence"=>100);
                if($this->EEOSettingsRS['canSeeEEOInfo'])
                {
                    $arrField["data"]=$this->EEOValues[$i]['fieldValue'];
                }
                else
                { 
                    $arrField["data"]=="<i><a href='javascript:void(0);' title='Ask an administrator to see the EEO info, or have permission granted to see it.'>(Hidden)</a></i>";
                }
                $eeoField[]=$arrField;
            } 
        }
                
        //for handling upcoming events 
        $upcomingEvents=array();
        if($this->accessLevel >= ACCESS_LEVEL_EDIT)
        {
            $arrField=array();
            $arrField["definition"]=array("displaytype"=>1,"fieldname"=>"UpcomingEvents","fieldlabel"=>"UpcomingEvents:","uitype"=>1,"sequence"=>100);
            $arrField["data"]= "<a href='#' onclick='showPopWin(\"index.php?m=candidates&a=addActivityChangeStatus&candidateID={$this->candidateID}&jobOrderID=-1&onlyScheduleEvent=true\", 600, 350, null); return false;'>
                                        <img src='images/calendar_add.gif' width='16' height='16' border='0' alt='Schedule Event' class='absmiddle' />&nbsp;Schedule Event
                                    </a>";                                
            $upcomingEvents[]=$arrField;  
        }
        foreach ($this->calendarRS as $rowNumber => $calendarData)
        {//trace($this->calendarRS);
            $arrField=array();
            $arrField["definition"]=array("displaytype"=>1,"fieldname"=>"UpcomingEvents","fieldlabel"=>"UpcomingEvents:","uitype"=>1,"sequence"=>100);
            $arrField["data"]="<a href='index.php?m=calendar&view=DAYVIEW&month=<{$calendarData["month"]}&year=20{$calendarData["year"]}&day={$calendarData["day"]}&showEvent={$calendarData["eventID"]}'
                                <img src={$calendarData['typeImage']} alt='' border='0' />
                                {$calendarData['dateShow']}
                                {$calendarData['title']}
                                </a>";
                           
            $upcomingEvents[]=$arrField;                
        }
        foreach ($upcomingEvents as $ind=>$fieldinfo)
        {
            $arrFieldRecord[]=$fieldinfo["definition"];
            $this->data[$fieldinfo["definition"]["fieldname"]]=$fieldinfo["data"];
        }
        /*
        $attachments=array();
        if($this->accessLevel >= ACCESS_LEVEL_EDIT)
        {
            $arrField=array();
            $arrField["definition"]=array("displaytype"=>1,"fieldname"=>"UpcomingEvents","fieldlabel"=>"UpcomingEvents:","uitype"=>1,"sequence"=>100);
            $arrField["data"]= "<a href='#' onclick='(index.php?m=candidates&a=addActivityChangeStatus&candidateID={$this->candidateID}&jobOrderID=-1&onlyScheduleEvent=true, 600, 350, null); return false;'
                                        <img src='images/calendar_add.gif' width='16' height='16' border='0' alt='Schedule Event' class='absmiddle' />&nbsp;Schedule Event
                                    </a>";                                
            $attachments[]=$arrField;  
        }
        foreach ($this->attachmentsRS as $rowNumber => $attachmentsData)
        {
            if ($attachmentsData['isProfileImage'] != '1')
            {
            $arrField=array();
            $arrField["definition"]=array("displaytype"=>1,"fieldname"=>"attachments","fieldlabel"=>"Attachments:","uitype"=>1,"sequence"=>100);
            $arrField["data"]={$attachmentsData['retrievalLink']}<img src="{$attachmentsData['attachmentIcon']}" alt="" width="16" height="16" border="0" />{$attachmentsData['originalFilename']}
                           
            $attachments[]=$arrField;   
            }
        }
        foreach ($attachments as $ind=>$fieldinfo)
        {
            $arrFieldRecord[]=$fieldinfo["definition"];
            $this->data[$fieldinfo["definition"]["fieldname"]]=$fieldinfo["data"];
         */
        $notes=array();
        if ($this->isShortNotes)
        {//trace("======");
            $arrField=array();
            $arrField["definition"]=array("displaytype"=>1,"fieldname"=>"miscNotes","fieldlabel"=>"Misc. Notes:","uitype"=>1,"sequence"=>100);
            $arrField["data"]=$this->data['shortNotes']."<a href='#' class='moreText' onclick='toggleNotes(); return false;'>[More]</a>".$this->data['shortNotes']."<a href='#' class='moreText' onclick='toggleNotes(); return false;'>[Less]</a>";  
            $arrField["data"]="<div id='shortNotes' style='display:block;' class='data'>{$this->data['shortNotes']}<span class='moreText'>...</span><p><a href='#' class='moreText' onclick='toggleNotes(); return false;'>[More]</a></p></div><div id='shortNotesid' style='display:none;' class='data'>{$this->data['shortNotes']}<a href='#' class='moreText' onclick='toggleNotes(); return false;'>[Less]</a></div>";                   
            $notes[]=$arrField;
        }
        else
        {
            $arrField=array();
            $arrField["definition"]=array("displaytype"=>1,"fieldname"=>"miscNotes","fieldlabel"=>"Misc. Notes:","uitype"=>1,"sequence"=>100);
            $arrField["data"]=$this->data['shortNotes'];
            $notes[]=$arrField;
        }
        foreach ($notes as $ind=>$fieldinfo)
        {
            $arrFieldRecord[]=$fieldinfo["definition"];
            $this->data[$fieldinfo["definition"]["fieldname"]]=$fieldinfo["data"];
        }
        foreach ($eeoField as $ind=>$fieldinfo)
        {
            $arrFieldRecord[]=$fieldinfo["definition"];
            $this->data[$fieldinfo["definition"]["fieldname"]]=$fieldinfo["data"];
        }
    }    
        $arrCalculateField=getAVFields(100, $record);
        foreach ($arrCalculateField as $ind=>$fieldinfo)
        {
            $arrFieldRecord[]=$fieldinfo["definition"];
            $this->data[$fieldinfo["definition"]["fieldname"]]=$fieldinfo["data"];
        }
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
        {
            foreach($arrField as $ind=>$fieldData)
            {
                if($fieldData["displaytype"]<=0) continue;
                $fieldName=$fieldData["fieldname"];
                if(!isset($this->data[$fieldName])) continue;
                $v=$this->data[$fieldName];
                /**
                 * process hook
                 */
                $caption=getLangVar($fieldName,"candidates");
                $ret=$hookFunction($fieldName,$v,$this->data);//trace($this->data);
                if($ret)
                {
                    if($ret===true) 
                    {
                        $arrRenderSerialize[]=empty($caption)?$fieldData["fieldlabel"]:$caption;
                        $arrRenderSerialize[]=$v;
                    }
                    else if(is_string($ret))
                    {
                        $html_template_content=$ret;
                        $arrRenderSerialize[]=empty($caption)?$fieldData["fieldlabel"]:$caption;
                        $arrRenderSerialize[]=$this->loadTemplate($html_template_content, $this->data);
                    }
                    else if(is_numeric($ret)) 
                    {
                        $arrRenderSerialize[]=empty($caption)?$fieldData["fieldlabel"]:$caption;
                        $arrRenderSerialize[]=$ret;
                    }
                    else if(is_object($ret))
                    {
                        $html_template_content=(string)$ret;
                        $arrRenderSerialize[]=empty($caption)?$fieldData["fieldlabel"]:$caption;
                        $arrRenderSerialize[]=$this->loadTemplate($html_template_content, $this->data);
                    }
                }
            }
        }//trace($arrRenderSerialize);
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