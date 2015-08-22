<?php
$name=function ($record)
{//trace($record);
    $field="name";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Name:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<b>{$record["name"]}</b>";
    return $arrField;
};
$url=function ($record)
{//trace($record);
    $field="url";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Web Site:","uitype"=>1,"sequence"=>24);
    
                                        
   // $arrField["data"]="<a href='{$record['url']}' target='_blank'>{$record['url']}</a>";                               </a>";
    $arrField["data"]=$record["url"];
    return $arrField;
};
$address=function ($record)
{//trace($record);
    $field="address";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Address:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["address"].$record["googleMaps"];
    return $arrField;
};
/*$phone1=function ($record)
{//trace($record);
    $field="phone1";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Primary Phone:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["phone1"];
    return $arrField;
};

$phone2=function ($record)
{//trace($record);
    $field="phone2";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Secondary Phone:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["phone2"];
    return $arrField;
};

$fax_number=function ($record)
{//trace($record);
    $field="fax_number";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Fax Number:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["fax_number"];
    return $arrField;
};

$billingContact=function ($record)
{//trace($record);
    $field="billingContact";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Billing Contact:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["billingContact"];
    return $arrField;
};

$key_technologies=function ($record)
{//trace($record);
    $field="key_technologies";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Key Technologies:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["key_technologies"];
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
};*/
$ownerFullName=function ($record)
{//trace($record);
    $field="ownerFullName";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Owner:","uitype"=>1,"sequence"=>24);
    $arrField["data"]=$record["ownerFullName"];
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
