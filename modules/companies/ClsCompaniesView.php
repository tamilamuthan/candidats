<?php
class ClsCompaniesView
{
    private $data=null;
    public function __construct($data) 
    {
        $this->data=$data;//trace($data);
    }
    public function &render($hookFunction)
    {
        $objSQL=new ClsAuieoSQL();
        $objFromCandidate=$objSQL->addFrom("auieo_fields");
        $objSQL->addWhere($objFromCandidate, "data_item_type", 200);
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
        $arrCalculateField=getAVFields(200, $record);
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
                //$k=  getAliasNameFromField($fieldName);
                //trace($this->data);
                //if(!isset($this->data["entered_by"])) continue;
                //if(!isset($this->data["billing_contact"])) continue;
                if($fieldName=="billing_contact") continue;
                $v=$this->data[$fieldName];//trace($this->data);
                /**
                 * process hook
                 */
                $caption=getLangVar($fieldName,"companies");
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