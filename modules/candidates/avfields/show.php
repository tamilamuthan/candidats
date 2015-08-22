<?php
/*$full_name=function ($record)
{
    $field="Name";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Name","uitype"=>1,"sequence"=>100);
    $arrField["data"]=$record["first_name"]." ".$record["last_name"];
    return $arrField;
};*/
/*$description=function ($record)
{//trace($record);
    $field="dateCreated";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Description:","uitype"=>1,"sequence"=>24);
    if($record["description"]!='')
    {
        $arrField["data"]=$record["description"];
    }
    
    return $arrField;
};
$description=function ($record)
{//trace($record);
    $field="dateCreated";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Description:","uitype"=>1,"sequence"=>24);
    if($record["description"]!='')
    {
        $arrField["data"]="<div id='shortDescription' style='overflow: auto; height:240px; border: #AAA 1px solid; padding:5px;'>{$record['description']}</div>";
    }
    
    return $arrField;
};
$intNotes=function ($record)
{//trace($record);
    $field="dateCreated";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Internal Notes:","uitype"=>1,"sequence"=>24);
    if($record["description"]!='')
    {
         $arrField["data"]="<div id='shortDescription' style='overflow: auto; height:240px; border: #AAA 1px solid; padding:5px;'>{$record['notes']}</div>";
    }
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
$email=function ($record)
{//trace($record);
    $field="email1";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Email:","uitype"=>1,"sequence"=>100);
    $arrField["data"]=$record["email1"];
    $arrField["data"]="<a href='mailto:{$record["email1"]}'>{$record['email1']}</a>";
    return $arrField;
};
$full_name=function ($record)
{//trace($record);
    $field="Name";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Name","uitype"=>1,"sequence"=>24);
    $data1="<b>{$record['first_name']}</b>";
    $data2="<b>{$record['middle_name']}</b>";
    $data3="<b>{$record['last_name']}</b>";
    
    $arrField["data"]=$data1.' '.$data2.' '.$data3;
    return $arrField;
};
$submitted=function ($record)
{
    $field="Submitted";
    $data="";
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Submitted","uitype"=>1,"sequence"=>5);
    $arrField["data"]=$record["submitted"];
    return $arrField;
};
$pipeline=function ($record)
{
    $arrField=array();
    $arrField["definition"]=array("displaytype"=>1,"fieldlabel"=>"Pipeline","uitype"=>1,"sequence"=>6);
    $arrField["data"]=$record["pipeline"];
    return $arrField;
    
}

?>