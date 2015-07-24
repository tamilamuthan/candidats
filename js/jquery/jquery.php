<?php
foreach($arrJS as $arrData)
{
    loadAppJS(dirname(__FILE__)."/".$arrData["source"],$arrData["destination"],-1);
}
foreach($arrCSS as $arrData)
{
    loadAppCSS(dirname(__FILE__)."/".$arrData["source"],$arrData["destination"],-1);
}
foreach($arrImage as $arrData)
{
    loadAppImageFiles(dirname(__FILE__)."/".$arrData["source"],$arrData["destination"]);
}
?>