<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

class ClsNaanalFilter
{
    public $module="";
    protected function __construct()
    {
        
    }
    
    protected function getNaanalFilter($table,$actionUrl="index.php",$isForm=true)
    {
        $arr=array();
        /**
         * if we don't want any field, we can remove that field name
         */
        $arr["candidate"]["main"]=array("can_relocate","zip","city","state","source");
        $arr["candidate"]["extra"]=array("Visa Status","Security Clearance","Availability","Payment Type");
        $module=$_REQUEST["m"];
        $action=isset($_REQUEST["a"])?$_REQUEST["a"]:"";
        $objDatabase = DatabaseConnection::getInstance();
        $sql="SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".DATABASE_NAME."' AND TABLE_NAME = '$table'";
        $arrRow=$objDatabase->getAllAssoc($sql);
        $arrUniqueData=array();
        foreach($arrRow as $row)
        {
            if($row["COLUMN_KEY"]=="PRI" || $row["COLUMN_KEY"]=="MUL") continue;
            if(!isset($arr[$table])) continue;
            if(!in_array($row["COLUMN_NAME"],$arr[$table]["main"])) continue;
            $sql="select distinct `{$row["COLUMN_NAME"]}` from {$table}";
            $arrRecord=$objDatabase->getAllRow($sql);
            $arrData=array();
            if($arrRecord)
            foreach($arrRecord as $r)
            {
                if(method_exists($this, "on_unique_data_display"))
                {
                    $uniqueDataDisplay=$this->on_unique_data_display($row["COLUMN_NAME"],$r[0]);
                    if(is_null($uniqueDataDisplay)) $arrData[$r[0]]=$r[0];
                    else $arrData[$r[0]]=$uniqueDataDisplay;
                }
                else
                {
                    $arrData[$r[0]]=$r[0];
                }
            }
            /*$result=mysql_query($sql);
            $arrData=array();
            while($r=  mysql_fetch_row($result))
            {
                if(method_exists($this, "on_unique_data_display"))
                {
                    $uniqueDataDisplay=$this->on_unique_data_display($row["COLUMN_NAME"],$r[0]);
                    if(is_null($uniqueDataDisplay)) $arrData[$r[0]]=$r[0];
                    else $arrData[$r[0]]=$uniqueDataDisplay;
                }
                else
                {
                    $arrData[$r[0]]=$r[0];
                }
            }*/
            asort($arrData);
            $arrUniqueData[$row["COLUMN_NAME"]]=$arrData;
        }
        if(isset($arr[$table]["extra"]))
        foreach($arr[$table]["extra"] as $extField)
        {
            $sql="select extra_field_settings_id from extra_field_settings where field_name='{$extField}'";
            $records=$objDatabase->getAllRow($sql);
            $rEField=$records[0];
            $sql="select distinct `value` from extra_field where field_name='{$extField}'";
            $records=$objDatabase->getAllRow($sql);
            $arrData=array();
            if($records)
            foreach($records as $r)
            {
                if(method_exists($this, "on_unique_data_display"))
                {
                    $uniqueDataDisplay=$this->on_unique_data_display($extField,$r[0]);
                    if(is_null($uniqueDataDisplay)) $arrData[$r[0]]=$r[0];
                    else $arrData[$r[0]]=$uniqueDataDisplay;
                }
                else
                {
                    $arrData[$r[0]]=$r[0];
                }
            }
            /*
            $result=mysql_query($sql);
            $rEField=mysql_fetch_row($result);
            $sql="select distinct `value` from extra_field where field_name='{$extField}'";
            $result=mysql_query($sql);
            $arrData=array();
            while($r=  mysql_fetch_row($result))
            {
                if(method_exists($this, "on_unique_data_display"))
                {
                    $uniqueDataDisplay=$this->on_unique_data_display($extField,$r[0]);
                    if(is_null($uniqueDataDisplay)) $arrData[$r[0]]=$r[0];
                    else $arrData[$r[0]]=$uniqueDataDisplay;
                }
                else
                {
                    $arrData[$r[0]]=$r[0];
                }
            }*/
            asort($arrData);
            $arrUniqueData[$rEField[0]]=$arrData;
        }
        $arrOption=array();
        $formStart='';
        $formEnd="";
        if($isForm)
        {
            $formStart='<form action="'.$actionUrl.'">';
            $formEnd="</form>";
        }
        $json=  json_encode($arrUniqueData);
        $filterUI='
            <script>
function getFilterData(fldData)
{
    var availableTags = '.$json.';
    if(availableTags[fldData])
    return availableTags[fldData];
    else
    return [];
}       
function onFldfilterChange(obj)
{
    var tmpfilterdata=getFilterData(jQuery(obj).val());
    if(JSON.stringify(tmpfilterdata)!="[]")
    {
        var rel=jQuery(obj).attr("rel");
        var dd = jQuery(\'<select style="width:400px" id="fldfilterdata_\'+rel+\'" name="data[]" />\');
        jQuery(\'<option />\', {value: "", text: "- select -"}).appendTo(dd);
        jQuery.each(tmpfilterdata, function (index,data)
        {
            if(data=="")
            {
            }
            else
            {
                jQuery(\'<option />\', {value: index, text: data}).appendTo(dd);
            }
        });
    }
    else
    {
        var rel=jQuery(obj).attr("rel");
        dd = jQuery(\'<input style="width:400px" id="fldfilterdata_\'+rel+\'" type="text" value="" name="data[]">\');
    }
    jQuery("#fldFilterdataContainer_"+rel).empty().append(dd);
} 
/*function onFldfilterChange(obj)
{
    var tmpfilterdata=getFilterData(jQuery(obj).val());
    if(JSON.stringify(tmpfilterdata)!="[]")
    {
        var dd = jQuery(\'<select id="fldfilterdata" name="data[]" />\');
        jQuery(\'<option />\', {value: "", text: "- select -"}).appendTo(dd);
        jQuery.each(tmpfilterdata, function (index,data)
        {
            if(data=="")
            {
            }
            else
            {
                jQuery(\'<option />\', {value: data, text: data}).appendTo(dd);
            }
        });
    }
    else
    {
        dd = jQuery(\'<input id="fldfilterdata" type="text" value="" name="data[]">\');
    }
    jQuery("#fldFilterdataContainer").empty().append(dd);
} */

</script>

        <script language="javascript">
        function addNewRow(obj)
        {
            var objTr=jQuery(obj).parent().parent().parent().find("tr:nth-child(1)").clone();
            jQuery(objTr).find("input").each(function(i,e)
            {
                    jQuery(e).val("");  		
            }); 
            jQuery(objTr).find("td:last-child").append("<a onclick=\'jQuery(this).parent().parent().remove();\' href=\'#\'>Remove</a>");
            jQuery(objTr).appendTo(jQuery(obj).parent().parent().parent().append());  
            var objSelect=objTr.find("td:nth-child(1)").children();
        }
        function updateHiddenField()
        {
            var updateSearchMode=jQuery(\'#searchMode\').val();
            jQuery(\'#filterMode\').val(updateSearchMode);
            var updateFilterText=jQuery(\'#searchText\').val();
            jQuery(\'#filterText\').val(updateFilterText);
            return true;
        }
        </script>
        '.$formStart.'
        <fieldset><legend>Filter</legend>
                                <table id="filter" style="width:500px;">';
        
        /**
         * get the filter grouping data
         */
        $_siteID = $_SESSION['CATS']->getSiteID();;
        $_db = DatabaseConnection::getInstance();
        $sql="Select * from settings where setting='filtergrouping' and site_id='{$_siteID}'";
        $arrData=$_db->getAssoc($sql);
        $isFilterGrouping=false;
        if(isset($arrData["value"]) && $arrData["value"]>0)
        {
            $isFilterGrouping=true;
        }
        ///end
        
        $arrFilter=(isset($_REQUEST["fldfilter"]) && !empty($_REQUEST["fldfilter"]))?$_REQUEST["fldfilter"]:array();
        if(count($arrFilter)>0)
        {
            $arrDynamicOption=array();
            $arrData=$_REQUEST["data"];
            $arrCondition=$_REQUEST["condition"];
            $arrBoolean=$_REQUEST["boolean"];
            $arrGroup=$_REQUEST["boolean"];
            $isfirst=true;
            foreach($arrFilter as $ind=>$filter)
            {
                $remove="";
                if($isfirst)
                {
                    $isfirst=false;
                }
                else
                {
                    $remove='<a href="#" onclick="jQuery(this).parent().parent().remove();">Remove</a>';
                }
                ///generate filter dropdown
                $arrNewOption=array();
                $selectedFilter="";
                foreach($arrRow as $row)
                {
                    if($row["COLUMN_KEY"]=="PRI" || $row["COLUMN_KEY"]=="MUL") continue;
                    $selected="";
                    if($filter==$row["COLUMN_NAME"])
                    {
                        $selectedFilter=$filter;
                        $selected=" selected";
                    }
                    if(method_exists($this, "on_column_display"))
                    {
                        $ret=$this->on_column_display($row["COLUMN_NAME"]);
                        if(is_null($ret)) continue;
                        $arrNewOption[]="<option value='{$ret["value"]}'{$selected}>{$ret["display"]}</option>";
                        //$arrDynamicOption[]="<option value='{$ret["value"]}'>{$ret["display"]}</option>";
                        $arrDynamicOption[$ret["value"]]=$ret["display"];
                    }
                    else
                    {
                        $arrNewOption[]="<option value='{$row["COLUMN_NAME"]}'{$selected}>{$row["COLUMN_NAME"]}</option>";
                        //$arrDynamicOption[]="<option value='{$row["COLUMN_NAME"]}'>{$row["COLUMN_NAME"]}</option>";
                        $arrDynamicOption[$row["COLUMN_NAME"]]=$row["COLUMN_NAME"];
                    }
                }
                foreach($this->arrExtraField as $ky=>$valu)
                {
                    if(method_exists($this, "on_extra_column_display"))
                    {
                        $ret=$this->on_extra_column_display($ky);
                        if(is_null($ret)) 
                        {
                            continue;
                        }
                    }
                    $selected="";
                    if($filter==$ky)
                    {
                        $selectedFilter=$filter;
                        $selected=" selected";
                    }
                    $arrNewOption[]="<option value='{$ky}'{$selected}>{$valu}</option>";
                    //$arrDynamicOption[]="<option value='{$ky}'>{$valu}</option>";
                    $arrDynamicOption[$ky]=$valu;
                }
                
                ///fldfilterdata start
                $fldfilterdata='<input style="width:400px" type="text" id="fldfilterdata_'.$ind.'" name="data[]" value="'.($arrData[$ind]).'" />';
                if(isset($arrUniqueData[$selectedFilter]))
                {
                    $fldfilterdata="<select style='width:400px' type='text' id='fldfilterdata_{$ind}' name='data[]'>
                        <option value=''>- Select -</option>
                    ";
                    foreach($arrUniqueData[$selectedFilter] as $option)
                    {
                        $selected="";
                        if($option==$arrData[$ind])
                        {
                            $selected=" selected";
                        }
                        $fldfilterdata=$fldfilterdata."<option value='{$option}'{$selected}>{$option}</option>
                        ";
                    }
                    $fldfilterdata=$fldfilterdata."</select>";
                }
                ///end
                
                /*$filterData=isset($_REQUEST["data"][$ind])?$_REQUEST["data"][$ind]:"";
                if(isset($arrUniqueData[$filter]))
                {
                    $select="<select id='fldfilterdata' name='data[]' >
                        <option value=''>- Select -</option>";
                    foreach($arrUniqueData[$filter] as $optData)
                    {
                        if($filterData==$optData)
                            $select=$select."<option value='{$optData}' selected>$optData</option>";
                        else
                            $select=$select."<option value='{$optData}'>$optData</option>";
                    }
                    $fldFilterControl=$select."</select>";
                }
                else
                {
                    $fldFilterControl='<input type="text" id="fldfilterdata" name="data[]" value="'.($filterData).'" />';
                }*/
                $newOption=implode("", $arrNewOption);
                $filterUI=$filterUI.'
                <tr>
                    <td id="dynfldfilt_'.$ind.'">
                        <select id="fldfilter_'.$ind.'" rel="'.$ind.'" onchange="onFldfilterChange(this);" name="fldfilter[]">
                                <option value="">- Select -</option>
                                '.$newOption.'
                        </select>
                    </td>
                    <td id="dynfldcond_'.$ind.'">
                        <select id="fldcond_'.$ind.'" name="condition[]">
                                <option value="equals" '.($arrCondition[$ind]=="equals"?" selected":"").'>equals</option>
                                <option value="contains" '.($arrCondition[$ind]=="contains"?" selected":"").'>contains</option>
                        </select>
                    </td>
                    <td id="fldFilterdataContainer_'.$ind.'" style="width:400px">
                        '.$fldfilterdata.'
                    </td>
                    <td id="dynfldbool_'.$ind.'">
                        <select id="fldbool_'.$ind.'" name="boolean[]">
                                <option value="and" '.(($arrBoolean[$ind]=="and")?" selected":"").'>AND</option>
                                <option value="or" '.(($arrBoolean[$ind]=="or")?" selected":"").'>OR</option>
                        </select>
                    </td>';
                if($isFilterGrouping)
                {
                    $filterUI=$filterUI.'<td id="dynfldgroup_'.$ind.'">
                        <select id="fldgroup_'.$ind.'" name="group[]">
                                <option value="0" '.(($arrGroup[$ind]==0)?" selected":"").'>No</option>
                                <option value="1" '.(($arrGroup[$ind]==1)?" selected":"").'>Yes</option>
                        </select>
                    </td>';
                }
                if(empty($remove))
                {
                    $filterUI=$filterUI.'<td>
                        <a href="#" onclick="getDynamicRow();">Add&nbsp;New</a>&nbsp;
                    </td>
                </tr>';
                }
                else
                {
                    $filterUI=$filterUI.'<td>
                        <a href="#" onclick="getDynamicRow();">Add&nbsp;New</a>&nbsp;|&nbsp;'.$remove.'
                    </td>
                </tr>';
                }
                
                /*$filterUI=$filterUI.'
                <tr>
                    <td>
                        <select onchange="onFldfilterChange(this);" name="fldfilter[]">
                                <option value="">- Select -</option>
                                '.$newOption.'
                        </select>
                    </td>
                    <td>
                        <select name="condition[]">
                                <option value="equals" '.((isset($_REQUEST["condition"][$ind]) && $_REQUEST["condition"][$ind]=="equals")?" selected":"").'>equals</option>
                                <option value="contains" '.((isset($_REQUEST["condition"][$ind]) && $_REQUEST["condition"][$ind]=="contains")?" selected":"").'>contains</option>
                        </select>
                    </td>
                    <td id="fldFilterdataContainer">
                        '.$fldFilterControl.'
                    </td>
                    <td>
                        <select name="boolean[]">
                                <option value="and" '.((isset($_REQUEST["boolean"][$ind]) && $_REQUEST["boolean"][$ind]=="and")?" selected":"").'>AND</option>
                                <option value="or" '.((isset($_REQUEST["boolean"][$ind]) && $_REQUEST["boolean"][$ind]=="or")?" selected":"").'>OR</option>
                        </select>
                    </td>
                    <td>
                        <a href="#" onclick="jQuery(\'#dynamicrow tr\').clone().appendTo(jQuery(this).parent().parent().parent().append());">Add New</a> <br />
                        '.$remove.'
                    </td>
                </tr>';*/
            }
        }
        else
        {
            ///generate filter dropdown
            $arrNewOption=array();
            $arrDynamicOption=array();
            foreach($arrRow as $row)
            {
                if($row["COLUMN_KEY"]=="PRI" || $row["COLUMN_KEY"]=="MUL") continue;
                if(method_exists($this, "on_column_display"))
                {
                    $ret=$this->on_column_display($row["COLUMN_NAME"]);
                    if(is_null($ret)) continue;
                    $arrNewOption[]="<option value='{$ret["value"]}'>{$ret["display"]}</option>";
                    $arrDynamicOption[$ret["value"]]=$ret["display"];
                    //$arrDynamicOption[]="<option value='{$ret["value"]}'>{$ret["display"]}</option>";
                }
                else
                {
                    $arrNewOption[]="<option value='{$row["COLUMN_NAME"]}'>{$row["COLUMN_NAME"]}</option>";
                    $arrDynamicOption[$row["COLUMN_NAME"]]=$row["COLUMN_NAME"];
                    //$arrDynamicOption[]="<option value='{$row["COLUMN_NAME"]}'>{$row["COLUMN_NAME"]}</option>";
                }
            }
            foreach($this->arrExtraField as $ky=>$valu)
            {
                if(method_exists($this, "on_extra_column_display"))
                {
                    $ret=$this->on_extra_column_display($ky);
                    if(is_null($ret)) 
                    {
                        continue;
                    }
                }
                $selected="";
                if(isset($filter) && $filter==$ky)
                {
                    $selected=" selected";
                }
                $arrNewOption[]="<option value='{$ky}'{$selected}>{$valu}</option>";
                //$arrDynamicOption[]="<option value='{$ky}'>{$valu}</option>";
                $arrDynamicOption[$ky]=$valu;
            }
            $newOption=implode("", $arrNewOption);
            $filterUI=$filterUI.'
            <tr>
                <td id="dynfldfilt_0">
                    <select id="fldfilter_0" rel="0" onchange="onFldfilterChange(this);" name="fldfilter[]">
                            <option value="">- Select -</option>
                            '.$newOption.'
                    </select>
                </td>
                <td id="dynfldcond_0">
                    <select id="fldcond_0" name="condition[]">
                        <option value="equals">equals</option>
                        <option value="contains">contains</option>
                    </select>
                </td>
                <td id="fldFilterdataContainer_0" style="max-width:400px">
                    <input style="width:400px" type="text" id="fldfilterdata_0" name="data[]" value="" />
                </td>
                <td id="dynfldbool_0">
                    <select id="fldbool_0" name="boolean[]">
                        <option value="and">AND</option>
                        <option value="or">OR</option>
                    </select>
                </td>
                ';
            if($isFilterGrouping)
            {
                $filterUI=$filterUI.'<td id="dynfldgroup_0">
                <select id="fldgroup_0" name="group[]">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                </select>
                </td>';
            }
                $filterUI=$filterUI.'<td>
                    <a href="#" onclick="getDynamicRow();">Add&nbsp;New</a>
                </td>
            </tr>';
            /*$filterUI=$filterUI.'
            <tr>
                <td>
                    <select onchange="onFldfilterChange(this);" name="fldfilter[]">
                            <option value="">- Select -</option>
                            '.$newOption.'
                    </select>
                </td>
                <td>
                    <select name="condition[]">
                        <option value="equals">equals</option>
                        <option value="contains">contains</option>
                    </select>
                </td>
                <td id="fldFilterdataContainer">
                    <input type="text" id="fldfilterdata" name="data[]" value="" />
                </td>
                <td>
                    <select name="boolean[]">
                        <option value="and">AND</option>
                        <option value="or">OR</option>
                    </select>
                </td>
                <td>
                    <a href="#" onclick="jQuery(\'#dynamicrow tr\').clone().appendTo(jQuery(this).parent().parent().parent().append());">Add&nbsp;New</a>
                </td>
            </tr>';*/
        }
        $dynamicOption=implode("",$arrDynamicOption);
        $dynamicRow='<script type="text/javascript">
        function getDynamicRow(index)
        {
            if(!index) index=jQuery("#filter tr").size();
            var dynamicOption='.json_encode($arrDynamicOption).';
            var trDynRow=jQuery(\'<tr />\');
            
            var tdFldFilt=jQuery(\'<td id="dynfldfilt_\' + index + \'">\');
            var selectFldFilter=jQuery(\'<select id="fldfilter_\' + index + \'" name="fldfilter[]" rel="\'+index+\'" onchange="onFldfilterChange(this);">\');
            jQuery("<option />", {value: "", text: "- Select -"}).appendTo(selectFldFilter);
            for(var key in dynamicOption)
            {
                jQuery("<option />", {value: key, text: dynamicOption[key]}).appendTo(selectFldFilter);
            }
            tdFldFilt.append(selectFldFilter);
            trDynRow.append(tdFldFilt);
            
            var tdFldCond=jQuery(\'<td id="dynfldcond_\' + index + \'">\');
            var selectFldCondition=jQuery(\'<select id="fldcond_\' + index + \'" name="condition[]">\');
            jQuery("<option />", {value: "equals", text: "equals"}).appendTo(selectFldCondition);
            jQuery("<option />", {value: "contains", text: "contains"}).appendTo(selectFldCondition);
            tdFldCond.append(selectFldCondition);
            trDynRow.append(tdFldCond);
            
            var tdFldCont=jQuery(\'<td id="fldFilterdataContainer_\' + index + \'">\');
            jQuery(\'<input style="width:400px" type="text" id="fldfilterdata_\' + index + \'" name="data[]" value="" />\').appendTo(tdFldCont);
            trDynRow.append(tdFldCont);
            
            var tdFldBool=jQuery(\'<td id="dynfldbool_\' + index + \'">\');
            var selectFldBoolean=jQuery(\'<select id="fldbool_\' + index + \'" name="boolean[]">\');
            jQuery("<option />", {value: "and", text: "AND"}).appendTo(selectFldBoolean);
            jQuery("<option />", {value: "or", text: "OR"}).appendTo(selectFldBoolean);
            tdFldBool.append(selectFldBoolean);
            trDynRow.append(tdFldBool);
            ';
            if($isFilterGrouping)
            {
                $dynamicRow=$dynamicRow.'var tdFldGroup=jQuery(\'<td id="dynfldgroup_\' + index + \'">\');
                var selectFldGroup=jQuery(\'<select id="fldgroup_\' + index + \'" name="group[]">\');
                jQuery("<option />", {value: "0", text: "No"}).appendTo(selectFldGroup);
                jQuery("<option />", {value: "1", text: "Yes"}).appendTo(selectFldGroup);
                tdFldGroup.append(selectFldGroup);
                trDynRow.append(tdFldGroup);
                ';
            }
            $dynamicRow=$dynamicRow.'jQuery(\'<td><a href="#" onclick="getDynamicRow()">Add&nbsp;New</a>&nbsp;|&nbsp;<a href="#" onclick="jQuery(this).parent().parent().remove();">Remove</a></td>\').appendTo(trDynRow);
            trDynRow.appendTo(jQuery("#filter"));
        }
        </script>';
        /*$dynamicRow='<table style="display:none;" id="dynamicrow"><tr>
                <td>
                    <select name="fldfilter[]">
                            <option value="">- Select -</option>
                            '.$dynamicOption.'
                    </select>
                </td>
                <td>
                    <select name="condition[]">
                        <option value="equals">equals</option>
                        <option value="contains">contains</option>
                    </select>
                </td>
                <td id="fldFilterdataContainer">
                    <input type="text" id="fldfilterdata" name="data[]" value="" />
                </td>
                <td>
                    <select name="boolean[]">
                        <option value="and">AND</option>
                        <option value="or">OR</option>
                    </select>
                </td>
                <td>
                    <a href="#" onclick="jQuery(\'#dynamicrow tr\').clone().appendTo(jQuery(this).parent().parent().parent().append());">Add&nbsp;New</a>&nbsp;|&nbsp;<a href="#" onclick="jQuery(this).parent().parent().remove();">Remove</a>
                </td>
            </tr></table>';*/
        
        $filterUI=$filterUI.'
        </table>        
        <table><tr><td colspan="5" style="text-align:center"><center><input type="submit" onclick="javascript:updateHiddenField();" value="Filter" /></center></td></tr></table>
        </fieldset>    
<input type="hidden" name="m" value="'.$module.'" />
<input type="hidden" name="a" value="search" />
<input type="hidden" name="getback" value="getback" />
<input type="hidden" name="search'.ucfirst($module).'" value="search'.ucfirst($module).'" />
<input type="hidden" name="advancedSearchParser" value="" />
<input type="hidden" name="advancedSearchOn" value="0" />

<input type="hidden" id="filterMode" name="mode" value="'.(isset($_REQUEST["mode"])?$_REQUEST["mode"]:"").'" />
<input type="hidden" id="filterText" name="wildCardString" value="'.(isset($_REQUEST["wildCardString"])?$_REQUEST["wildCardString"]:"").'" />
'.$formEnd.$dynamicRow;
        return $filterUI;
    }
}
?>