<?php
$title=function ($record)
{//trace($record);
    $field="title";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Title:","uitype"=>1,"sequence"=>24);
    $arrField["data"]="<b>{$record['title']}</b>";
    return $arrField;
};
$typeDescription=function ($record)
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
