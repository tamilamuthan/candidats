<?php
if(isset($arrJS))
foreach($arrJS as $arrData)
{
    loadAppJS(dirname(__FILE__)."/".$arrData["source"],$arrData["destination"],-1);
}
if(isset($arrCSS))
foreach($arrCSS as $arrData)
{
    loadAppCSS(dirname(__FILE__)."/".$arrData["source"],$arrData["destination"],-1);
}
if(isset($arrImage))
foreach($arrImage as $arrData)
{
    loadAppImageFiles(dirname(__FILE__)."/".$arrData["source"],$arrData["destination"]);
}
?>