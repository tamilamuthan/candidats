<?php
class ClsFieldsView
{
    private $data=null;
    public function __construct($data=false) 
    {
        $this->data=$data;
    }
    public function getFields($data_item_type_id)
    {
        $arrFieldRecord=getModuleFields($data_item_type_id,true);

        $arrRenderSerialize=array();
        /**
         * build sequence array
         */
        $arrFieldSeq=array();
        foreach($arrFieldRecord as $ind=>$fieldData)
        {
            $sequence=isset($fieldData["sequence"])?$fieldData["sequence"]:0;
            $arrFieldSeq[$sequence][]=$fieldData;
        }
        ksort($arrFieldSeq, SORT_NUMERIC);//trace($arrFieldSeq,2);
                //trace($arrFieldSeq);
        foreach($arrFieldSeq as $sequence=>$arrField)
        {
            foreach($arrField as $ind=>$fieldData)
            {
                //if($fieldData["displaytype"]<=0) continue;
                $fieldName=$fieldData["fieldname"];
                /**
                 * process hook
                 */
                $caption=getLangVar($fieldName);
                $arrRenderSerialize[]=empty($caption)?$fieldName:$caption;
                /**
                 * check whether calculated field or database field
                 */
                ///if it is database field
                if(isset($fieldData["id"]))
                {
                    if($fieldData["displaytype"]<=0)
                    {
                        $arrRenderSerialize[]="<input type='checkbox' name='field_hide[{$fieldData["id"]}]' onclick='javascript:updateField(\"{$moduleData["module"]}\",\"{$fieldName}\",this);' />";
                    }
                    else
                    {
                        $arrRenderSerialize[]="<input type='checkbox' checked name='field_hide[{$fieldData["id"]}]' onclick='javascript:updateField(\"{$moduleData["module"]}\",\"{$fieldName}\",this);' />";
                    }
                    if($fieldData["readonly"]<=0)
                    {
                        $arrRenderSerialize[]="<input type='checkbox' name='field_readonly[{$fieldData["id"]}]' onclick='javascript:updateFieldReadonly(\"{$moduleData["module"]}\",\"{$fieldName}\",this);' />";
                    }
                    else
                    {
                        $arrRenderSerialize[]="<input type='checkbox' checked name='field_readonly[{$fieldData["id"]}]' onclick='javascript:updateFieldReadonly(\"{$moduleData["module"]}\",\"{$fieldName}\",this);' />";
                    }
                    $arrRenderSerialize[]="<a href='index.php?m=settings&modulename={$moduleData["module"]}&a=moveUp&field_id={$fieldData["id"]}'>Up</a>";
                    $arrRenderSerialize[]="<a href='index.php?m=settings&modulename={$moduleData["module"]}&a=moveDown&field_id={$fieldData["id"]}'>Down</a>";
                }
                else
                {
                    if(!isset($fieldData["displaytype"]) || $fieldData["displaytype"]<=0)
                    {
                        $arrRenderSerialize[]="<input type='checkbox' disabled />";
                    }
                    else
                    {
                        $arrRenderSerialize[]="<input type='checkbox' checked disabled />";
                    }
                    if(!isset($fieldData["readyonly"]) || $fieldData["readyonly"]<=0)
                    {
                        $arrRenderSerialize[]="<input type='checkbox' disabled />";
                    }
                    else
                    {
                        $arrRenderSerialize[]="<input type='checkbox' checked disabled />";
                    }
                    $arrRenderSerialize[]="<input type='button' value='up' disabled />";
                    $arrRenderSerialize[]="<input type='button' value='down' disabled />";
                }
            }
        }
        $columnPrefix="col";
        $arrRenderView =  multi_dimension_array($arrRenderSerialize, 5, $columnPrefix);
        $arrRender[$data_item_type_id]=$arrRenderView;
    }
    public function &render($hookFunction=false)
    {
        //$data_item_type_id=100;
        $arrModule=getModules();
        $arrRender=array();
        foreach($arrModule as $data_item_type_id=>$moduleData)
        {
            $arrFieldRecord=getModuleFields($data_item_type_id,true);
            
            $arrRenderSerialize=array();
            /**
             * build sequence array
             */
            $arrFieldSeq=array();
            foreach($arrFieldRecord as $ind=>$fieldData)
            {
                $sequence=isset($fieldData["sequence"])?$fieldData["sequence"]:0;
                $arrFieldSeq[$sequence][]=$fieldData;
            }
            ksort($arrFieldSeq, SORT_NUMERIC);//trace($arrFieldSeq,2);
                    //trace($arrFieldSeq);
            foreach($arrFieldSeq as $sequence=>$arrField)
            {
                foreach($arrField as $ind=>$fieldData)
                {
                    //if($fieldData["displaytype"]<=0) continue;
                    $fieldName=$fieldData["fieldname"];
                    /**
                     * process hook
                     */
                    $caption=getLangVar($fieldName);
                    $arrRenderSerialize[]=empty($caption)?$fieldName:$caption;
                    /**
                     * check whether calculated field or database field
                     */
                    ///if it is database field
                    if(isset($fieldData["id"]))
                    {
                        if($fieldData["displaytype"]<=0)
                        {
                            $arrRenderSerialize[]="<input type='checkbox' name='field_hide[{$fieldData["id"]}]' onclick='javascript:updateField(\"{$moduleData["module"]}\",\"{$fieldName}\",this);' />";
                        }
                        else
                        {
                            $arrRenderSerialize[]="<input type='checkbox' checked name='field_hide[{$fieldData["id"]}]' onclick='javascript:updateField(\"{$moduleData["module"]}\",\"{$fieldName}\",this);' />";
                        }
                        if($fieldData["readonly"]<=0)
                        {
                            $arrRenderSerialize[]="<input type='checkbox' name='field_readonly[{$fieldData["id"]}]' onclick='javascript:updateFieldReadonly(\"{$moduleData["module"]}\",\"{$fieldName}\",this);' />";
                        }
                        else
                        {
                            $arrRenderSerialize[]="<input type='checkbox' checked name='field_readonly[{$fieldData["id"]}]' onclick='javascript:updateFieldReadonly(\"{$moduleData["module"]}\",\"{$fieldName}\",this);' />";
                        }
                        $arrRenderSerialize[]="<a href='index.php?m=settings&modulename={$moduleData["module"]}&a=moveUp&field_id={$fieldData["id"]}'>Up</a>";
                        $arrRenderSerialize[]="<a href='index.php?m=settings&modulename={$moduleData["module"]}&a=moveDown&field_id={$fieldData["id"]}'>Down</a>";
                        if($fieldData["is_extra"]>0)
                        {
                            $arrRenderSerialize[]="<a href='index.php?m=settings&modulename={$moduleData["module"]}&a=delete&field_id={$fieldData["id"]}'>Delete</a>";
                        }
                        else
                        {
                            $arrRenderSerialize[]="-";
                        }
                    }
                    else
                    {
                        if(!isset($fieldData["displaytype"]) || $fieldData["displaytype"]<=0)
                        {
                            $arrRenderSerialize[]="<input type='checkbox' disabled />";
                        }
                        else
                        {
                            $arrRenderSerialize[]="<input type='checkbox' checked disabled />";
                        }
                        if(!isset($fieldData["readyonly"]) || $fieldData["readyonly"]<=0)
                        {
                            $arrRenderSerialize[]="<input type='checkbox' disabled />";
                        }
                        else
                        {
                            $arrRenderSerialize[]="<input type='checkbox' checked disabled />";
                        }
                        $arrRenderSerialize[]="<input type='button' value='up' disabled />";
                        $arrRenderSerialize[]="<input type='button' value='down' disabled />";
                        $arrRenderSerialize[]="<input type='button' value='delete' disabled />";
                    }
                }
            }
            $columnPrefix="col";
            $arrRenderView =  multi_dimension_array($arrRenderSerialize, 6, $columnPrefix);
            $arrRender[$data_item_type_id]=$arrRenderView;
        }
        return $arrRender;
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