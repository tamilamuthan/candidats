<?php
$name=function ($record)
{//trace($record);
    $field="naME";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Name:","uitype"=>1,"sequence"=>24);
    $data1="<b>{$record['first_name']}</b>";
    $data2="<b>{$record['last_name']}</b>";
    //$linkButton=TemplateUtility::printSingleQuickActionMenu(DATA_ITEM_CONTACT, $record["contact_id"]);
    $arrField["data"]=$data1.' '.$data2."<a id='vCard' href='index.php?m=contacts&amp;a=downloadVCard&amp;contactID={$record["contact_id"]}'>
                                            <img src='images/vcard.gif' class='absmiddle' alt='vCard' border='0' />
                                        </a>";
    
    
    return $arrField;
};
$companyName=function ($record)
{//trace($record);
    $field="CompanyName";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Company Name:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<a href='index.php?m=companies&amp;a=show&amp;companyID={$record['company_id']}'><b>{$record["companyName"]}</b></a>";
    //$arrField["data"]=$record["companyName"];
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
$dateCreated=function ($record)
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
/*$address=function ($record)
{//trace($record);
    $field="address";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Address:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["address"];
    return $arrField;
};
$phone_other=function ($record)
{//trace($record);
    $field="phone_other";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Other Phone:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["phone_other"];
    return $arrField;
};
$phone_cell=function ($record)
{//trace($record);
    $field="phone_cell";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Cell Phone:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["phone_cell"];
    return $arrField;
};

$phone_work=function ($record)
{//trace($record);
    $field="phone_work";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Work Phone:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["phone_work"];
    return $arrField;
};

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
$email2=function ($record)
{//trace($record);
    $field="email2";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"2nd E-Mail:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["email2"];
    return $arrField;
};*/


/*$title=function ($record)
{//trace($record);
    $field="title";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Title:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["title"];
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
