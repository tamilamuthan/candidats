<?php
class ClsContactsView
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
        $objSQL->addWhere($objFromCandidate, "data_item_type", 300);
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
        if($_REQUEST["a"]=="show")
        {
            $upcomingEvents=array();
        if($this->accessLevel >= ACCESS_LEVEL_EDIT)
        {
            $arrField=array();
            $arrField["definition"]=array("displaytype"=>1,"fieldname"=>"UpcomingEvents","fieldlabel"=>"UpcomingEvents:","uitype"=>1,"sequence"=>100);
            $arrField["data"]= "<a href='#' onclick='(index.php?m=contacts&amp;a=addActivityScheduleEvent&amp;contactID={$this->data['contact_id']}&amp;onlyScheduleEvent=true', 600, 200, null); return false;'>
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
        }
        
        $arrCalculateField=getAVFields(300, $record);
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
        {//trace($arrFieldSeq);
            foreach($arrField as $ind=>$fieldData)
            {//trace($fieldData);
                if($fieldData["displaytype"]<=0) continue;
                $fieldName=$fieldData["fieldname"];
                /*if($fieldData["fieldname"]=="email1")
                {
                    trace("=======");
                }*/
                //$k=  getAliasNameFromField($fieldName);
                //trace($this->data);
                //if(!isset($this->data["entered_by"])) continue;
                $v=$this->data[$fieldName];//trace($v);
                /**
                 * process hook
                 */
                $caption=getLangVar($fieldName,"contacts");
        
                $ret=$hookFunction($fieldName,$v,$this->data);//trace($this->data);
                if($ret)
                {
                    if($ret===true) 
                    {//trace("---");
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