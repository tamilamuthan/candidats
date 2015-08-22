<?php
$candidMapping=function ($record)
{//trace($record);
    $field="candidateMapping";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Candidate Mapping:","uitype"=>1,"sequence"=>24);
    $data="<select tabindex='17' class='mceEditor' name='candidate_mapping[]' id='candidate_mapping' multiple='multiple'>";
        
                $arrJoborderColumn=getColumnMeta(400,true);
                $arrCandidateColumn=getColumnMeta(100,true);
                $arrMatchingColumn=array();
                foreach($arrJoborderColumn as $column=>$tmp)
                {
                    if(isset($arrCandidateColumn[$column]))
                    {
                        $arrMatchingColumn[$column]=$tmp;
                    }
                }
                $arrCandidateMapping=array();
                if(isset($this->data['candidate_mapping']) && !empty($this->data['candidate_mapping']))
                {
                    $arrCandidateMapping=$this->data['candidate_mapping'];
                }
                $arrOption=array();
                if($arrMatchingColumn)
                foreach($arrMatchingColumn as $column=>$tmp)
                {
                    if($column=='notes' || $column=='is_admin_hidden') continue;
                    if(in_array($column, $arrCandidateMapping))
                    {
                        $arrOption[]='<option selected value="{$column}">{$column}</option>';
                    }
                    else
                    {
                        $arrOption[]='<option value="{$column}">{$column}</option>';
                    }
                }
                
                $data=$data.implode(',',$arrOption);
                $data=$data."</select>";
                $arrField["data"]=$data;
    return $arrField;
};
$title=function ($record)
{//trace($record);
    $field="title";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Title:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record['title']}' class='inputbox' style='width: 150px' />*";
    return $arrField;
};
$openavail=function ($record)
{//trace($record);
    $field="title";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Remaining Openings:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record['openings_available']}' class='inputbox' style='width: 150px' />*";
    return $arrField;
};
$hot=function ($record)
{//trace($record);
    $field="hot";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Hot:","uitype"=>1,"sequence"=>24);
    $valdata="";
    if ($record['is_hot'] == 1)
    {
        $valdata='checked';
    }
    $arrField["data"]="<input type='checkbox' tabindex='19' id='public' name='public' onchange='checkPublic(this);' onclick='checkPublic(this);' onkeydown='checkPublic(this);'{$valdata}/ >
            <img title='Checking this box indicates that the job order is 'hot', and shows up highlighted throughout the system.' src='images/information.gif' alt='' width='16' height='16' />";
	
    return $arrField;
};
$public=function ($record)
{//trace($record);
    $field="public";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Public:","uitype"=>1,"sequence"=>24);
    $valdata="";
    if ($record['public'] == 1)
    {
        $valdata='checked';
    }
    
                
    $arrField["data"]="<input type='checkbox' tabindex='19' id='public' name='public' onchange='checkPublic(this);' onclick='checkPublic(this);' onkeydown='checkPublic(this);'{$valdata}/ >
            <img title='Checking this box indicates that the job order is public. Job orders flaged as public will be able to be viewed by anonymous users.' src='images/information.gif' alt='' width='16' height='16' />";
	
    return $arrField;
};
$joborder_id=function ($record)
{//trace($record);
    $field="joborder_id";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Company Job ID:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record['client_job_id']}' class='inputbox' style='width: 150px' />*";
    return $arrField;
};
$city=function ($record)
{//trace($record);
    $field="city";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"City:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record["city"]}' class='inputbox' style='width: 150px' />";
    return $arrField;
};
$ownerFullName=function ($record)
{//trace($record);
	$database=DatabaseConnection::getInstance();
	$sql="SELECT user.last_name,user.first_name FROM `user` left join contact on user.user_id=contact.owner ";
	$ownername=$database->getAllAssoc($sql);//trace($ownername);
        $arrField=array();
        $arrField["data"]="<select>";
	foreach($ownername as $key=>$value)
	{//trace($value["last_name"].$value["first_name"]);
		$field="ownerFullName";
		$data="";
		
		$arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Owner:","uitype"=>1,"sequence"=>24);
		//$tempname[]=$value["last_name"].$value["first_name"];//trace($tempname);
		
                $arrField["data"].="<option value='edit'>{$value['last_name']}, {$value["first_name"]}</option>";
		
	}
       $arrField["data"].="</select>*";
    return $arrField;
};
$recriterFullName=function ($record)
{//trace($record);
	$database=DatabaseConnection::getInstance();
	$sql="SELECT user.last_name,user.first_name FROM `user` left join contact on user.user_id=contact.owner ";
	$ownername=$database->getAllAssoc($sql);//trace($ownername);
        $arrField=array();
        $arrField["data"]="<select>";
	foreach($ownername as $key=>$value)
	{//trace($value["last_name"].$value["first_name"]);
		$field="recriterFullName";
		$data="";
		
		$arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Recruiter:","uitype"=>1,"sequence"=>24);
		//$tempname[]=$value["last_name"].$value["first_name"];//trace($tempname);
		//$arrField["data"].="<option value='edit'>None</option>";
                $arrField["data"].="<option value='edit'>{$value['last_name']}, {$value["first_name"]}</option>";
		
	}
       $arrField["data"].="</select>*";
    return $arrField;
};
$State=function ($record)
{//trace($record);
    $field="State";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"State:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record["state"]}' class='inputbox' style='width: 150px' />";
    return $arrField;
};
$department=function ($record)
{//trace($record);
    $field="department";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Department:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<select id='departmentSelect' name='department' class='inputbox' style='width: 150px;' >
						
						<option value='nullline'>None</option>
						
						</select>";
     
                           
	return $arrField;
};
$status=function ($record)
{//trace($record);
    $field="status";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Status:","uitype"=>1,"sequence"=>24);
    if($record["status"]== 'OnHold' || $record['status'] == 'Full' || $record['status'] == 'Closed' || $record['status'] == 'Canceled')
    {
        $tempvalue="";
        if($record["status"]== 'OnHold')
        {
            $tempvalue="OnHold";
        }
        if($record["status"]== 'Full')
        {
            $tempvalue="Full";
        }
        if($record["status"]== 'Closed')
        {
            $tempvalue="Closed";
        }
        if($record["status"]== 'Canceled')
        {
            $tempvalue="Canceled";
        }
    $arrField["data"]="<select id='statusSelect' name='department' class='inputbox' style='width: 150px;' >
						
                        <option {$tempvalue}selected value='OnHold'>On Hold</option>
                        <option {$tempvalue}selected value='Full'>Full</option>
                        <option {$tempvalue}selected value='Closed'>Closed</option>
                        <option {$tempvalue}selected value='Canceled'>Canceled</option>
                  
						
    						</select>";
                        return $arrField;
    }
    else
    {
        $tvalue="";
        if ($record['status'] == 'Active')
        {
            $tvalue='Active';
        }
        if ($record['status'] == 'Upcoming')
        {
            $tvalue='Upcoming';
        }
        if ($record['status'] == 'Lead')
        {
            $tvalue='Lead';
        }
        if ($record['status'] == 'OnHold')
        {
            $tvalue='OnHold';
        }
        if ($record['status'] == 'Full')
        {
            $tvalue='Full';
        }
        if ($record['status'] == 'Closed')
        {
            $tvalue='Closed';
        }
        if ($record['status'] == 'Canceled')
        {
            $tvalue='Canceled';
        }
        $arrField["data"]="<select id='statusSelect' name='department' class='inputbox' style='width: 150px;' >
						
                        <option {$tvalue}selected value='Active'>Active</option>
                        <option {$tvalue}selected value='Upcoming'>Upcoming</option>
                        <option {$tvalue}selected value='Lead'>Prospective / Lead</option>
                        <option {$tvalue}selected value='OnHold'>On Hold</option>
                        <option {$tvalue}selected value='Full'>Full</option>
                        <option {$tvalue}selected value='Closed'>Closed</option>
                        <option {$tvalue}selected value='Canceled'>Canceled</option>
                  
						
    						</select>*";
                        return $arrField;
    }
     
                           
	
};
$type=function ($record)
{//trace($record);
    $field="type";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Type:","uitype"=>1,"sequence"=>24);
    if($record["type"]== 'H')
    {
        
        $arrField["data"]="<select id='statusSelect' name='department' class='inputbox' style='width: 150px;' >
                            <option value='H' selected='selected'>H (Hire)</option>
                            <option value='C2H'>C2H (Contract to Hire)</option>
                            <option value='C'>C (Contract)</option>
                            <option value='FL'>FL (Freelance)</option>
                            </select>";
        return $arrField;
    }
    if($record["type"]== 'C2H')
    {
        $arrField["data"]="<select id='statusSelect' name='department' class='inputbox' style='width: 150px;' >
                            <option value='H'>H (Hire)</option>
                            <option value='C2H' selected value='C2H'>C2H (Contract to Hire)</option>
                            <option value='C'>C (Contract)</option>
                            <option value='FL'>FL (Freelance)</option>
                            </select>";
        return $arrField;
    }
    if($record["type"]== 'C')
    {
        $arrField["data"]="<select id='statusSelect' name='department' class='inputbox' style='width: 150px;' >
                            <option value='H'>H (Hire)</option>
                            <option value='C2H'>C2H (Contract to Hire)</option>
                            <option value='C' selected value='C'>C (Contract)</option>
                            <option value='FL'>FL (Freelance)</option>
                            </select>";
        return $arrField;
    }
    if($record["type"]== 'FL')
    {
        $arrField["data"]="<select id='statusSelect' name='department' class='inputbox' style='width: 150px;' >
                            <option value='H'>H (Hire)</option>
                            <option value='C2H'>C2H (Contract to Hire)</option>
                            <option value='C'>C (Contract)</option>
                            <option value='FL' selected value='FL'>FL (Freelance)</option>
                            </select>";
        return $arrField;
    }
    
                           
	
};
$company=function ($record)
{//trace($record);
    $field="company";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Company:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='radio' name='companyName' id='companyName' tabindex='2' value={$record['name']}><input type='text' name='companyName' id='companyName' tabindex='2' value={$record['name']}>*";
     
                           
	return $arrField;
};
$description=function ($record)
{//trace($record);
    $field="description";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Description:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<textarea tabindex='20' class='mceEditor' name='description' id='description' rows='15' style='width: 500px;'>{$record['description']}</textarea>";
    return $arrField;
};
$internalnotes=function ($record)
{//trace($record);
    $field="internalnotes";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Internal Notes:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<textarea tabindex='21' class='mceEditor' name='description' id='description' rows='5' style='width: 500px;'>{$record['description']}</textarea>";
    return $arrField;
};
/*$candidatemapping=function ($record)
{//trace($record);
    $field="candidatemapping";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Candidate Mapping:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<select tabindex='17' class='mceEditor' name='candidate_mapping[]' id='candidate_mapping' multiple='multiple'>
        <?php 
                $arrJoborderColumn=getColumnMeta(400,true);
                $arrCandidateColumn=getColumnMeta(100,true);
                $arrMatchingColumn=array();
                foreach($arrJoborderColumn as $column=>$tmp)
                {
                    if(isset($arrCandidateColumn[$column]))
                    {
                        $arrMatchingColumn[$column]=$tmp;
                    }
                }
                $arrCandidateMapping=array();
                if(isset($this->data['candidate_mapping']) && !empty($this->data['candidate_mapping']))
                {
                    $arrCandidateMapping=$this->data['candidate_mapping'];
                }
                $arrOption=array();
                if($arrMatchingColumn)
                foreach($arrMatchingColumn as $column=>$tmp)
                {
                    if($column=='notes' || $column=='is_admin_hidden') continue;
                    if(in_array($column, $arrCandidateMapping))
                    {
                        $arrOption[]='<option selected value="{$column}">{$column}</option>';
                    }
                    else
                    {
                        $arrOption[]='<option value="{$column}">{$column}</option>';
                    }
                }
                
                echo implode(',',$arrOption);
                ?></select>"
     
                           
	return $arrField;
};
/*
$rate_max=function ($record)
{//trace($record);
    $field="rate_max";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Max Rate:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record['rate_max']}' class='inputbox' style='width: 150px' />*";
    return $arrField;
};
($typeDescription=function ($record)
{//trace($record);
    $field="typeDescription";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Type:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["typeDescription"];
    return $arrField;
};
$companyName=function ($record)
{//trace($record);
    $field="CompanyName";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Company Name:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<a href='index.php?m=companies&amp;a=show&amp;companyID={$record["company_id"]}'>{$record["companyName"]}</a>";
    //$arrField["data"]=$record["companyName"];
    return $arrField;
};
$joborder_id=function ($record)
{//trace($record);
    $field="joborder_id";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"CATS Job ID:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["joborder_id"];
    return $arrField;
};
$CompanyJobID=function ($record)
{//trace($record);
    $field="CompanyJobID";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Company Job ID:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["client_job_id"];
    return $arrField;
};
$department=function ($record)
{//trace($record);
    $field="department";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Department:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["department"];
    return $arrField;
};

$contactFullName=function ($record)
{//trace($record);
    $field="contactFullName";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Contact Name:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["contactFullName"];
    $arrField["data"]="<a href='index.php?m=contacts&amp;a=show&amp;contactID={$record["contact_id"]}'>{$record['contactFullName']}</a>";
    
                        
    return $arrField;
};
$contactWorkPhone=function ($record)
{//trace($record);
    $field="contactWorkPhone";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Contact Phone:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["contactWorkPhone"];
    return $arrField;
};
$contactEmail=function ($record)
{//trace($record);
    $field="contactEmail";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Contact Email:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["contactEmail"];
    $arrField["data"]="<a href='mailto:{$record["contactEmail"]}'>{$record['contactEmail']}</a>";
    return $arrField;
};
$cityAndState=function ($record)
{//trace($record);
    $field="cityAndState";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Location:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["cityAndState"];
    return $arrField;
};
$pipeline=function ($record)
{//trace($record);
    $field="pipeline";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Pipeline:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["pipeline"];
    return $arrField;
};
$submitted=function ($record)
{//trace($record);
    $field="submitted";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Submitted:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["submitted"];
    return $arrField;
};
$daysOld=function ($record)
{//trace($record);
    $field="daysOld";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Days Old:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["daysOld"];
    return $arrField;
};
$dateCreated=function ($record)
{//trace($record);
    $field="dateCreated";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Created:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["dateCreated"].' '.'('.$record["ownerFullName"].')';
    return $arrField;
};
$recruiterFullName=function ($record)
{//trace($record);
    $field="recruiterFullName";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Recruiter:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["recruiterFullName"];
    return $arrField;
};
$ownerFullName=function ($record)
{//trace($record);
    $field="ownerFullName";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Owner:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["ownerFullName"];
    return $arrField;
};
/*
$rate_max=function ($record)
{//trace($record);
    $field="rate_max";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Max Rate:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["rate_max"];
    return $arrField;
};
*/
?>
