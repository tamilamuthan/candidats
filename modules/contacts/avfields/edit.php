<?php
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
$email2=function ($record)
{//trace($record);
    $field="email2";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"2nd E-Mail:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record['email2']}' class='inputbox' style='width: 150px' />";
    return $arrField;
};
$companyName=function ($record)
{//trace($record);
    $field="CompanyName";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Company:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record["companyName"]}' class='inputbox' style='width: 150px' />*.<input type='checkbox' name='hotcompany'> Internal Contact";
    
    return $arrField;
};

$department=function ($record)
{//trace($record);
    $field="department";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Department:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<select id='departmentSelect' name='department' class='inputbox' style='width: 150px;' >
						<option value='edit'>(Edit Departments)</option>
						<option value='nullline'>-------------------------------</option>
						if ({$record['company_department_id']} == 0)
						<option value='(none)' selected='selected'>(None)</option>
						else
						<option value='(none)'>(None)</option>
						</select>";
     
                           
	return $arrField;
};
$title=function ($record)
{//trace($record);
    $field="title";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Title:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record["title"]}' class='inputbox' style='width: 150px' />*";
    return $arrField;
};
$reportsToTitle=function ($record)
{//trace($record);
    $field="reportsToTitle";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Reports To:","uitype"=>1,"sequence"=>24);
	
    $arrField["data"]="<select id='reportsTo' name='reportsTo' class='inputbox' style='width: 150px;' >
						
						if ({$record['reports_to']} == -1)
						<option value='(none)' selected='selected'>(None)</option>
						else
						<option value='(none)'>(None)</option>
						</select>";
    return $arrField;
};
$address=function ($record)
{//trace($record);
    $field="address";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Address:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<textarea name='firstName' id='firstName'  class='inputbox' style='width: 150px' >{$record['address']}</textarea>";
    return $arrField;
};
$phone_other=function ($record)
{//trace($record);
    $field="phone_other";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Other Phone:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record["phone_other"]}' class='inputbox' style='width: 150px' />";
    return $arrField;
};
$phone_cell=function ($record)
{//trace($record);
    $field="phone_cell";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Cell Phone:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record["phone_cell"]}' class='inputbox' style='width: 150px' />";
    return $arrField;
};

$phone_work=function ($record)
{//trace($record);
    $field="phone_work";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Work Phone:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record["phone_work"]}' class='inputbox' style='width: 150px' />";
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
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Hot Contact:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='checkbox' name='hotcompany' if({$record['is_hot']} == 1) checked />";
	
    return $arrField;
};
$leftcompany=function ($record)
{//trace($record);
    $field="leftcompany";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Left Company:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='checkbox' name='hotcompany' if ({$record['left_company']} == 1) checked />";
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
/*
$dateCreated=function ($record)
{//trace($record);
    $field="dateCreated";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Created:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["date_created"].' '.'('.$record["ownerFullName"].')';
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
$reportsToTitle=function ($record)
{//trace($record);
    $field="reportsToTitle";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Reports To:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["reportsToTitle"];
    return $arrField;
};
$email1=function ($record)
{//trace($record);
    $field="email1";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"E-Mail:","uitype"=>1,"sequence"=>24);
    //"<a href="mailto:$record['email1']">$record['email1']</a>"
    
    $arrField["data"]="<a href='mailto:{$record["email1"]}'>{$record['email1']}</a>";
    //$arrField["data"]=$record['email1'];
    
    return $arrField;
};
/*

/*$email1=function ($record)
{//trace($record);
    $field="email1";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"E-Mail:","uitype"=>1,"sequence"=>24);
    //"<a href="mailto:$record['email1']">$record['email1']</a>"
    
    //$arrField["data"]="<a href='mailto:{$record["email1"]}'>$record['email1']</a>";
    $arrField["data"]=$record['email1'];
    
    return $arrField;
};
*/


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
};*/
?>
