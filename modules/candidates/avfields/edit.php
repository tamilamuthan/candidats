<?php
$email1=function ($record)
{//trace($record);
    $field="email1";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"E-Mail:","uitype"=>1,"sequence"=>24);
    //"<a href="mailto:$record['email1']">$record['email1']</a>"
    
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record['email1']}' class='inputbox' style='width: 150px' />";
    //$arrField["data"]=$record['email1'];
    
    return $arrField;
};
$fname=function ($record)
{//trace($record);
    $field="firstName";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"First Name:","uitype"=>1,"sequence"=>24);
    $data1="<b>{$record['first_name']}</b>";
    $data2="<b>{$record['last_name']}</b>";
    //$linkButton=TemplateUtility::printSingleQuickActionMenu(DATA_ITEM_CONTACT, $record["contact_id"]);
    
	$arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record['first_name']}' class='inputbox' style='width: 150px' />*";
    
    
    return $arrField;
};
$lname=function ($record)
{//trace($record);
    $field="last_name";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Last Name:","uitype"=>1,"sequence"=>24);
    $data1="<b>{$record['first_name']}</b>";
    $data2="<b>{$record['last_name']}</b>";
    //$linkButton=TemplateUtility::printSingleQuickActionMenu(DATA_ITEM_CONTACT, $record["contact_id"]);
    
	$arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record['last_name']}' class='inputbox' style='width: 150px' />*";
    
    
    return $arrField;
};
$mname=function ($record)
{//trace($record);
    $field="middle_name";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Middle Name:","uitype"=>1,"sequence"=>24);
    $data1="<b>{$record['first_name']}</b>";
    $data2="<b>{$record['last_name']}</b>";
    //$linkButton=TemplateUtility::printSingleQuickActionMenu(DATA_ITEM_CONTACT, $record["contact_id"]);
    
	$arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record['middle_name']}' class='inputbox' style='width: 150px' />*";
    
    
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
$State=function ($record)
{//trace($record);
    $field="State";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"State:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record["state"]}' class='inputbox' style='width: 150px' />";
    return $arrField;
};
$postalcode=function ($record)
{//trace($record);
    $field="PostalCode";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Postal Code:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record["zip"]}' class='inputbox' style='width: 150px' />.<input type='button' class='button' onclick='CityState_populate('zip', 'ajaxIndicator');' value='Lookup' />.<img src='images/indicator2.gif' alt='AJAX' id='ajaxIndicator' style='vertical-align: middle; visibility: hidden; margin-left: 5px;' />";
    return $arrField;
};
$hotcompany=function ($record)
{//trace($record);
    $field="hotcompany";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Hot Candidate:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='checkbox' name='hotcompany' if({$record['is_hot']} == 1) checked />";
	
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
$miscnotes=function ($record)
{
//trace($record);
    $field="miscnotes";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Misc. Notes:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<textarea class='inputbox' name='notes' id='notes' rows='5' style='width: 400px' >{$record['notes']}</textarea>";
    return $arrField;
};
$gender=function ($record)
{//trace($record);
    $field="gender";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Gender:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<select id='genderSelect' name='gender' class='inputbox' style='width: 150px;' >
						<option value=''>----</option>
                                                <option value='m' if (strtolower({$record['eeo_gender']}) == 'm') echo('selected')>Male</option>
                                                <option value='f' if (strtolower({$record['eeo_gender']}) == 'f') echo('selected')>Female</option>
						</select>";
     
                           
	return $arrField;
};
$eeo_ethnic_type=function ($record)
{//trace($record);
    $field="eeo_ethnic_type";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Ethnic Background:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<select id='eeo_ethnic_typeSelect' name='eeo_ethnic_type' class='inputbox' style='width: 150px;' >
						<option value=''>----</option>
                                                <option value='1' if ({$record['eeo_ethnic_type_id']} == 1) echo('selected')>American Indian</option>
                                                <option value='2' if ({$record['eeo_ethnic_type_id']} == 2) echo('selected')>Asian or Pacific Islander</option>
                                                <option value='3' if ({$record['eeo_ethnic_type_id']} == 3) echo('selected')>Hispanic or Latino</option>
                                                <option value='4' if ({$record['eeo_ethnic_type_id']} == 4) echo('selected')>Non-Hispanic Black</option>
                                                <option value='5' if ({$record['eeo_ethnic_type_id']} == 5) echo('selected')>Non-Hispanic White</option>
						</select>";
     
                           
	return $arrField;
};
$VetranStatus=function ($record)
{//trace($record);
    $field="VetranStatus";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Vetran Status:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<select id='VetranStatus' name='VetranStatus' class='inputbox' style='width: 150px;' >
						<option value=''>----</option>
                                                <option value='1' if ({$record['eeo_veteran_type_id']} == 1) echo('selected')>No</option>
                                                <option value='2' if ({$record['eeo_veteran_type_id']} == 2) echo('selected')>Eligible Veteran</option>
                                                <option value='3' if ({$record['eeo_veteran_type_id']} == 3) echo('selected')>Disabled Veteran</option>
                                                <option value='4' if ({$record['eeo_veteran_type_id']} == 4) echo('selected')>Eligible and Disabled</option>
                                               
						</select>";
     
                           
	return $arrField;
};
$DisabilityStatus=function ($record)
{//trace($record);
    $field="DisabilityStatus";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Disability Status:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<select id='DisabilityStatus' name='VetranStatus' class='inputbox' style='width: 150px;' >
						<option value=''>----</option>
                                                <option value='No' if ({$record['eeo_disability_status']} == NO) echo('selected')>No</option>
                                                <option value='Yes' if ({$record['eeo_disability_status']} == YES) echo('selected')>YES</option>
                                                
						</select>";
     
                           
	return $arrField;
};
/*$Date=function ($record)
{//trace($record);
    $field="Date";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Date:","uitype"=>1,"sequence"=>24);
    if(!empty($record['dateAvailable']))
    {
        $valdate="<script type='text/javascript'>DateInput('dateAvailable', false, 'MM-DD-YY', '{$record["dateAvailableMDY"]}', -1);</script>";
    }
    else
    {
        $valdate="<script type='text/javascript'>DateInput('dateAvailable', false, 'MM-DD-YY', '', -1);</script>";
    }
    $arrField["data"]=$valdate;
    return $arrField;
};*/
?>