<?php
class ClsLHtmlReader
{
    private $html=null;
    private $dom=null;
    public function __construct($html=null)
    {
        $this->html=$html;
        $this->dom=str_get_html($html);
    }
    public function &getResources($arrIgnore=false)
    {
        $arrResource=array();
        $arrLink=array();
        $arrObjLink=$this->dom->find("link");
        foreach($arrObjLink as $e)
        {
            if($arrIgnore && in_array($e->href, $arrIgnore)) continue;
            $arrLink[]=$e->href;
        }
        $arrScript=array();
        $arrObjScript=$this->dom->find("script");
        foreach($arrObjScript as $e)
        {
            if($arrIgnore && in_array($e->href, $arrIgnore)) continue;
            $basename=  basename($e->href);
            $arrBasename=explode(".",$basename);
            $arrScript[]=$e->href;
        }
        $arrImage=array();
        $arrObjImage=$this->dom->find("script");
        foreach($arrObjImage as $e)
        {
            if($arrIgnore && in_array($e->src, $arrIgnore)) continue;
            $arrImage[]=$e->src;
        }
        $arrResource["link"]=$arrLink;
        $arrResource["script"]=$arrScript;
        return $arrResource;
    }
    public function render()
    {
        return $this->dom->save();
    }
}
?>
