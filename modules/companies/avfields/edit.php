<?php
$name=function ($record)
{//trace($record);
    $field="name";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Name:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record["name"]}' class='inputbox' style='width: 150px' />*";
    return $arrField;
};
$url=function ($record)
{//trace($record);
    $field="url";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Web Site:","uitype"=>1,"sequence"=>24);
    
                                        
   // $arrField["data"]="<a href='{$record['url']}' target='_blank'>{$record['url']}</a>";                               </a>";
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record["url"]}' class='inputbox' style='width: 150px' />";
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
$phone1=function ($record)
{//trace($record);
    $field="phone1";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Primary Phone:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record["phone1"]}' class='inputbox' style='width: 150px' />";
    return $arrField;
};

$phone2=function ($record)
{//trace($record);
    $field="phone2";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Secondary Phone:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record["phone2"]}' class='inputbox' style='width: 150px' />";
    return $arrField;
};

$fax_number=function ($record)
{//trace($record);
    $field="fax_number";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Fax Number:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='text' name='firstName' id='firstName' value='{$record["fax_number"]}' class='inputbox' style='width: 150px' />";
    return $arrField;
};

$billingContact=function ($record)
{//trace($record);
    $field="billingContact";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Billing Contact:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<select id='billingContact' name='billingContact' class='inputbox' style='width: 150px;' >
						
						
						<option value='(none)' selected='selected'>None</option>
						
						</select>";
    return $arrField;
};

$key_technologies=function ($record)
{//trace($record);
    $field="key_technologies";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Key Technologies:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<textarea class='inputbox' name='notes' id='notes' rows='' cols='' style='width: 400px;'>{$record["key_technologies"]}</textarea>";
   
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
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<input type='checkbox' name='hotcompany' if({$record['is_hot']} == 1) checked /> Hot Company";
	
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
						
						</select>";
     
                           
	return $arrField;
};

/*$dateCreated=function ($record)
{//trace($record);
    $field="dateCreated";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Created:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["dateCreated"].' '.'('.$record["ownerFullName"].')';
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
/*$dateCreated=function ($record)
{//trace($record);
    $field="dateCreated";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Created:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["dateCreated"].' '.'('.$record["ownerFullName"].')';
    return $arrField;
};
/*$cityAndState=function ($record)
{//trace($record);
    $field="cityAndState";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Location:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["cityAndState"];
    return $arrField;
};
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
