<?php
function createTreeFromList(array $array, $parent_id = 1) {
    $return = array();

    foreach ($array as $k => $v) {
        if ($v['parentid'] == $parent_id) 
        {
            unset($v["parentid"]);
            $return[$k] = $v; 
            $return[$k]['nodes'] = createTreeFromList($array, $v['id']);    
        }
    }
    $arrReturn=array();
    foreach($return as $ret)
    {
        $arrReturn[]=$ret;
    }
    return $arrReturn;
}
$db=  DatabaseConnection::getInstance();
$site_id=$_SESSION["CATS"]->getSiteID();
$query = "SELECT id,parentid,rolename as title FROM auieo_roles where site_id={$site_id} and parentid!=0";
$arrRowRoles = $db->getAllAssoc($query);
$tree=createTreeFromList($arrRowRoles);//trace($tree);
$jsontree=  json_encode($tree);
//echo $jsontree;exit;
include_once("modules/settings/ClsFieldsView.php");
$AUIEO_JSON_RENDER="";
$objFieldsView=new ClsFieldsView();
$arrRender=$objFieldsView->render();//trace($arrRender);

/**
 * build roles and profile array
 */
$query = "select * from auieo_profiles where site_id={$site_id}";
$arrRowProfile = $db->getAllAssoc($query);
$arrProfile=array();
foreach($arrRowProfile as $rw)
{
    $arrProfile[$rw["id"]]=$rw["profilename"];
}
$arrProfileRole=array();
$arrProfileRoleSelected=array();
$arrProfileRoleAvailable=array();
foreach($arrRowRoles as $i=>$arrRle)
{
    $arrProfileRoleSelected[$arrRle["id"]]=array();
    foreach($arrRowProfile as $j=>$rw)
    {
        $arrProfileRole[$arrRle["id"]][$j]=array("profileid"=>$rw["id"],"email"=>$rw["profilename"]);
        $sql="select * from auieo_roles2profiles where roleid={$arrRle["id"]} and profileid={$rw["id"]}";
        $arrAssoc=$db->getAllAssoc($sql);
        if(empty($arrAssoc))
        {
            $arrProfileRoleAvailable[$arrRle["id"]][]=array("profileid"=>$rw["id"],"email"=>$rw["profilename"]);
        }
        else
        {
            $arrProfileRoleSelected[$arrRle["id"]][]=array("profileid"=>$rw["id"],"email"=>$rw["profilename"]);
        }
    }
}
$AUIEO_JSON_PROFILE_ROLE_SELECTED = json_encode($arrProfileRoleSelected);
$AUIEO_JSON_PROFILE_ROLE_AVAILABLE = json_encode($arrProfileRoleAvailable);
$AUIEO_JSON_PROFILE_ROLE = json_encode($arrProfileRole);
$AUIEO_JSON_PROFILE_SELECT_INDEX = isset($arrRowRoles[0])?$arrRowRoles[0]["id"]:0;

//$arrRender=$objCandidateView->render();
$AUIEO_JSON_RENDER=  json_encode($arrRender);
$AUIEO_FIELD_MODULE = '<select id="module" onchange="loadFieldsModule();">
    <option value="100" '.(isset($_REQUEST["fieldmodule"]) && $_REQUEST["fieldmodule"]=="candidates"?"selected":"").'>Candidate</option>
    <option value="200" '.(isset($_REQUEST["fieldmodule"]) && $_REQUEST["fieldmodule"]=="companies"?"selected":"").'>Company</option>
    <option value="300" '.(isset($_REQUEST["fieldmodule"]) && $_REQUEST["fieldmodule"]=="contacts"?"selected":"").'>Contact</option>
    <option value="400" '.(isset($_REQUEST["fieldmodule"]) && $_REQUEST["fieldmodule"]=="joborders"?"selected":"").'>Joborder</option>
</select>';

//$tree = buildTree($rows);

$AUIEO_ROLES_DATA=$jsontree;/*'[{
      "id": 1,
      "title": "node1",
      "nodes": [
        {
          "id": 11,
          "title": "node1.1",
          "nodes": [
            {
              "id": 111,
              "title": "node1.1.1",
              "nodes": []
            }
          ]
        },
        {
          "id": 12,
          "title": "node1.2",
          "nodes": []
        }
      ],
    }, {
      "id": 2,
      "title": "node2",
      "nodes": [
        {
          "id": 21,
          "title": "node2.1",
          "nodes": []
        },
        {
          "id": 22,
          "title": "node2.2",
          "nodes": []
        }
      ],
    }, {
      "id": 3,
      "title": "node3",
      "nodes": [
        {
          "id": 31,
          "title": "node3.1",
          "nodes": []
        }
      ],
    }, {
      "id": 4,
      "title": "node4",
      "nodes": [
        {
          "id": 41,
          "title": "node4.1",
          "nodes": []
        }
      ],
    }]';*/
?>