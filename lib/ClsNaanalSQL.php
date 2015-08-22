<?php
/**************************************************************************
 * Naanal PHP Framework, Simple, Efficient and Developer Friendly
 * Ver 4.0, Copyright (C) <2010>  <Tamil Amuthan. R>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ************************************************************************/

class ClsNaanalSQL
{
    private $sqlType="SELECT";
    private $arrTable=array();
    private $arrTableNew=array();
    private $arrWhere=array();
    private $arrWhereNew=array();
    private $arrJoin=array();
    private $from=-1;
    private $length=20;
    private $arrField=array();
    private $arrFieldNew=array();
    private $arrValue=array();
    public $isDistinct=false;
    private $arrOrderBy=array();
    private $err=array();
    
    //private $objAuieoSQLFromClause=null;
    private $arrFrom=array();
    private $arrSelect=array();
    private $sql="";

    function __construct($sqlType="SELECT")
    {
        $this->sqlType=$sqlType;
        //$this->objAuieoSQLFromClause=new ClsAuieoSQLFromClause();
    }
    function setSQLType($sqlType)
    {
        $this->sqlType=$sqlType;
    }
    function setLimit($from,$length)
    {
        $this->from=$from;
        $this->length=$length;	
    }
    function addField($field,$table="")
    {
        $this->arrField[$field]=$table;
    }
    function addFieldNew($field,$table,$as=false)
    {
        $this->arrFieldNew[]=array("table"=>$table,"field"=>$field,"as"=>$as);
        $this->arrTableNew[$table]="";
    }
    function addCustomField($custom_field,$as=false)
    {
        $this->arrFieldNew[]=array("table"=>"AUIEO_SQL_CUSTOM_FIELD","field"=>$custom_field,"as"=>$as);
        $this->arrTableNew["AUIEO_SQL_CUSTOM_FIELD"]="";
    }
    function addFieldCreate($field,$type="int",$size=11)
    {
        $this->arrFieldCreate[$field]=array("type"=>$type,"size"=>$size);
    }
    function addValue($field,$value)
    {
        $this->arrValue[$field]=$value;
        $this->addField($field);
    }
    function addTable($table)
    {
        $this->arrTable[]=$table;
        if(!isset($this->arrTableNew[$table]))
            $this->arrTableNew[$table]=array();
    }
    function addWhere($field,$data,$condition="=",$boolean="AND")
    {
        $this->arrWhere[]=array("field"=>$field,"data"=>$data,"condition"=>$condition,"boolean"=>$boolean);
    }
    function addWhereNew($table,$field,$data,$condition="=",$boolean="AND")
    {
        $this->arrWhereNew[]=array("table"=>$table,"field"=>$field,"data"=>$data,"condition"=>$condition,"boolean"=>$boolean);
    }
    function addCustomWhere($custom_field,$data,$condition="=",$boolean="AND")
    {
        $this->arrWhereNew[]=array("table"=>false,"field"=>$custom_field,"data"=>$data,"condition"=>$condition,"boolean"=>$boolean);
    }
    public function addOrderBy($orderBy,$isAsc=true)
    {
        $this->arrOrderBy[]=array("field"=>$orderBy,"isAsc"=>$isAsc);
    }
    public function addJoin($from)
    {
        $this->arrJoin[]=$from;
    }
    public function addJoinAsArray($from)
    {
        $this->arrJoin=$from;
    }
    /**
     * if parameter is two dimensional array with first dimension as index based array and second dimension
     * as asssociative array, the paramerer is used directly.
     * else it will be converted to two dimensional array ans used it
     * @param $where
     */
    function addWhereAsArray($where)
    {
            if(!is_array($where)) trace("Array expected where found ".gettype($where));
            $arrWhere=array();
            if(!empty($where))
            {
                    if(!isset($where[0]))
                    {
                            $arrWhere[]=$where;
                    }
                    else 
                    {
                            $arrWhere=$where;
                    }
            }
            if($arrWhere)
            foreach($arrWhere as $ind=>$whr)
            {
                    if(!isset($whr["condition"]))
                    {
                            $whr["condition"]="=";
                    }
                    if(!isset($whr["boolean"]))
                    {
                            $whr["boolean"]="AND";
                    }
                    $this->arrWhere[]=$whr;
            }
    }
    function &render($isCount=false)
    {
        if(empty($this->sql))
        {
          if(empty($this->arrTable) && empty($this->arrTableNew))
          {
              $this->err[]="No Table Specified";
              $ret=false;
              return $ret;
          }
          if(strtolower($this->sqlType)=="create")
          {
              $sql="CREATE TABLE `{$this->arrTable[0]}` (";
              $arrSqlField=array();
              foreach($this->arrFieldCreate as $field=>$arrData)
              {
                  $size=$arrData["size"];
                  $type=$arrData["type"];
                  $arrSqlField="`{$field}` {$type}({$size}))";
              }
              $sql=$sql.")";
              return $sql;
          }
          if(strtolower($this->sqlType)=="insert" || strtolower($this->sqlType)=="replace" || strtolower($this->sqlType)=="insert ignore")
          {
              $fields="";
              $values="";
              if(empty($this->arrValue)) trace("No Field added for insert");
              foreach($this->arrValue as $k=>$v)
              {
                      $fields=$fields."`{$k}`,";
                      $values=$values."'".addslashes($v)."',";
              }
              $fields=trim($fields,",");
              $values=trim($values,",");
              $sql="{$this->sqlType} INTO {$this->arrTable[0]} ({$fields}) VALUES({$values})";
              return $sql;
          }
        }
        $where="";
        if($this->arrWhereNew)
        {
            foreach($this->arrWhereNew as $arr)
            {
                if($arr["table"]===false)
                {
                    
                }
                else
                {
                    
                }
                $fld=strpos($arr["field"], "(")?$arr["field"]:"`".$arr["field"]."`";
                if($arr["condition"]=="like")
                {
                    if($where=="")
                    {
                        $where="{$fld} LIKE '%{$arr["data"]}%'";
                    }
                    else
                    {
                        $where=$where." {$arr["boolean"]} {$fld} LIKE '%{$arr["data"]}%'";
                    }
                }
                else if($arr["condition"]=="startswith")
                {
                    if($where=="")
                    {
                        $where="{$fld} LIKE '{$arr["data"]}%'";
                    }
                    else
                    {
                        $where=$where." {$arr["boolean"]} {$fld} LIKE '{$arr["data"]}%'";
                    }
                }
                else if($arr["condition"]=="endswith")
                {
                    if($where=="")
                    {
                        $where="{$fld} LIKE '%{$arr["data"]}'";
                    }
                    else
                    {
                        $where=$where." {$arr["boolean"]} {$fld} LIKE '%{$arr["data"]}'";
                    }
                }
                else if($arr["condition"]=="between")
                {
                    $arrBetween=explode(",",$arr["data"]);
                    $from=trim($arrBetween[0]);
                    $to=trim($arrBetween[1]);
                    if($where=="")
                    {
                        $where="({$fld} BETWEEN {$from} AND {$to})";
                    }
                    else
                    {
                        $where=$where." {$arr["boolean"]} ({$fld} BETWEEN '{$from}' AND '{$to}')";
                    }
                }
                else if($arr["condition"]=="in")
                {
                    if($where=="")
                    {
                        $where="{$fld} IN ({$arr["data"]})";
                    }
                    else
                    {
                        $where=$where." {$arr["boolean"]} {$fld} IN ({$arr["data"]})";
                    }
                }
                else 
                {
                    if($where=="")
                    {
                        $where="{$fld}{$arr["condition"]}'{$arr["data"]}'";
                    }
                    else
                    {
                        $where=$where." {$arr["boolean"]} {$fld}{$arr["condition"]}'{$arr["data"]}'";
                    }
                }
            }
        }
        else if($this->arrWhere)
        {
            foreach($this->arrWhere as $arr)
            {
                $fld=strpos($arr["field"], "(")?$arr["field"]:"`".$arr["field"]."`";
                if($arr["condition"]=="like")
                {
                    if($where=="")
                    {
                        $where="{$fld} LIKE '%{$arr["data"]}%'";
                    }
                    else
                    {
                        $where=$where." {$arr["boolean"]} {$fld} LIKE '%{$arr["data"]}%'";
                    }
                }
                else if($arr["condition"]=="startswith")
                {
                    if($where=="")
                    {
                        $where="{$fld} LIKE '{$arr["data"]}%'";
                    }
                    else
                    {
                        $where=$where." {$arr["boolean"]} {$fld} LIKE '{$arr["data"]}%'";
                    }
                }
                else if($arr["condition"]=="endswith")
                {
                    if($where=="")
                    {
                        $where="{$fld} LIKE '%{$arr["data"]}'";
                    }
                    else
                    {
                        $where=$where." {$arr["boolean"]} {$fld} LIKE '%{$arr["data"]}'";
                    }
                }
                else if($arr["condition"]=="between")
                {
                    $arrBetween=explode(",",$arr["data"]);
                    $from=trim($arrBetween[0]);
                    $to=trim($arrBetween[1]);
                    if($where=="")
                    {
                        $where="({$fld} BETWEEN '{$from}' AND '{$to}')";
                    }
                    else
                    {
                        $where=$where." {$arr["boolean"]} ({$fld} BETWEEN '{$from}'' AND '{$to}')";
                    }
                }
                else if($arr["condition"]=="in")
                {
                    if($where=="")
                    {
                        $where="{$fld} IN ({$arr["data"]})";
                    }
                    else
                    {
                        $where=$where." {$arr["boolean"]} {$fld} IN ({$arr["data"]})";
                    }
                }
                else 
                {
                    if($where=="")
                    {
                        $where="{$fld}{$arr["condition"]}'{$arr["data"]}'";
                    }
                    else
                    {
                        $where=$where." {$arr["boolean"]} {$fld}{$arr["condition"]}'{$arr["data"]}'";
                    }
                }
            }
        }
        if(strtolower($this->sqlType)=="delete")
        {
            $sql="DELETE FROM {$this->arrTable[0]} WHERE ".$where;
            return $sql;
        }
        if(strtolower($this->sqlType)=="update")
        {
            $fields="";
            if(empty($this->arrValue)) trace("No Field added for update");
            foreach($this->arrValue as $k=>$v)
            {
                if(strpos($v, "("))
                {
                    $fields=$fields."`{$k}`='".addslashes($v)."',";
                }
                else
                {
                    $fields=$fields."`{$k}`='".addslashes($v)."',";
                }
            }
            $fields=trim($fields,",");
            if($where)
                $sql="UPDATE {$this->arrTable[0]} SET {$fields} WHERE ".$where;
            else 
                $sql="UPDATE {$this->arrTable[0]} SET {$fields}";
            return $sql;
        }
        if(empty($this->sql))
        {
            $strfield="*";
            if(!empty($this->arrFieldNew))
            {
                foreach($this->arrFieldNew as $ind=>$arrFieldInfo)
                {
                    $table=$arrFieldInfo["table"];
                    $field=$arrFieldInfo["field"];
                    $as=$arrFieldInfo["as"];
                    if($table!="AUIEO_SQL_CUSTOM_FIELD")
                    {
                        $field="`{$table}`.`{$field}`";
                    }
                    if($as!==false)
                    {
                        $myfield="{$field} AS `{$as}`";
                    }
                    else
                    {
                        $myfield=$field;
                    }
                    if($strfield=="*")
                    {
                        $strfield=$myfield;
                    }
                    else
                    {
                        $strfield=$strfield.",".$myfield;
                    }
                }
            }
            else if(!empty($this->arrField))
            {
                foreach($this->arrField as $field=>$table)
                {
                    $myfield=$field;
                    if(!empty($table))
                    {
                        $myfield=$table.".".$field;	
                    }
                    if($strfield=="*")
                    {
                        $strfield=$myfield;
                    }
                    else
                    {
                        $strfield=$strfield.",".$myfield;
                    }
                }
            }

            $arrTable=$this->arrTable;
            if(!empty($this->arrTableNew))
            {
                $arrTableNew=$this->arrTableNew;
                if(isset($arrTableNew["AUIEO_SQL_CUSTOM_FIELD"])) unset($arrTableNew["AUIEO_SQL_CUSTOM_FIELD"]);
                $arrTable=  array_keys($arrTableNew);
            }
            if($this->arrJoin)
            {
                $arrJoin=array();
                $arrTableJoin=$arrTable;
                $firstTable=array_shift($arrTableJoin);
                $prevTable=$firstTable;
                foreach($arrTableJoin as $tableJoin)
                {
                    foreach($this->arrJoin as $joinInfo)
                    {
                        if($joinInfo["table1"]==$prevTable && $joinInfo["table2"]==$tableJoin)
                        {
                            $arrJoin[]=" {$joinInfo["type"]} `{$joinInfo["table2"]}` ON `{$joinInfo["table1"]}`.`{$joinInfo["table1_join_field"]}`=`{$joinInfo["table2"]}`.`{$joinInfo["table2_join_field"]}`";
                        }
                    }
                    $prevTable=$joinInfo["table2"];
                    if(isset($arrTableNew[$joinInfo["table1"]])) unset($arrTableNew[$joinInfo["table1"]]);
                    if(isset($arrTableNew[$joinInfo["table2"]])) unset($arrTableNew[$joinInfo["table2"]]);
                }
                $fromTable=empty($arrTableNew)?"":"`".implode("`,`",$arrTableNew)."`";
                $comma="";
                if(!empty($fromTable))
                {
                    $comma=",";
                }
                $join=empty($arrJoin)?"":"{$comma}\n{$firstTable} ".implode("\n",$arrJoin);
                $distinct=$this->isDistinct?" distinct":"";
                if($isCount)
                        $sql=$this->sqlType.$distinct." count(*) as count from {$fromTable}".$join.(empty($where)?" ":" WHERE ".$where);
                else
                        $sql=$this->sqlType.$distinct." ".$strfield." from {$fromTable}".$join.(empty($where)?" ":" WHERE ".$where);
            }
            else 
            {
                $join="";
                if($this->arrJoin)
                {
                    $join=" ";
                    foreach($this->arrJoin as $arr)
                    {
                        $join=$join.$arr["type"]." join {$arr["table"]} on {$this->arrTable[0]}.{$arr["onSource"]}={$arr["table"]}.{$arr["onDestination"]}";
                    }
                }
                $distinct=$this->isDistinct?" distinct":"";
                if($isCount)
                        $sql=$this->sqlType.$distinct." count(*) as count from `".implode("`,`",$arrTable)."`".$join.(empty($where)?" ":" WHERE ".$where);
                else
                        $sql=$this->sqlType.$distinct." ".$strfield." from `".implode("`,`",$arrTable)."`".$join.(empty($where)?" ":" WHERE ".$where);
            }
        }
        else
        {
            $sql=$this->sql.(empty($where)?" ":" WHERE ".$where);
        }
        if($this->arrOrderBy)
        {
            $sql=$sql." ORDER BY";
            foreach($this->arrOrderBy as $ind=>$arrOrder)
            {
                if($ind===0)
                {
                    if($arrOrder["isAsc"]===false)
                    {
                        $sql=$sql." `{$arrOrder["field"]}` DESC";
                    }
                    else
                    {
                        $sql=$sql." `{$arrOrder["field"]}` ASC";
                    }
                }
                else
                {
                    if($arrOrder["isAsc"]===false)
                    {
                        $sql=$sql.", `{$arrOrder["field"]}` DESC";
                    }
                    else
                    {
                        $sql=$sql.", `{$arrOrder["field"]}` ASC";
                    }
                }
            }
        }
        if($this->from>=0)
        {
            $sql=$sql." LIMIT {$this->from},{$this->length}";
        }
        return $sql;
    }
    function addSql($sql)
    {
        $this->sql=$sql;
    }
    function loadSql($sqlFile)
    {
        static $handle=null;
        static $filesize=0;
        $buffer=false;
        if(is_null($handle))
        {
            $handle=fopen($sqlFile, "r");
            $filesize=  filesize($sqlFile);
        }
        $c=false;
        $c2=false;
        $c3=false;
        $c4=false;
        $isComment=false;
        ///if the pointer not in end of file
        while (!feof($handle)) 
        {
            $c=fread($handle, 1);
            if($c=="-" && !feof($handle))
            {
                $c2=fread($handle, 1);
                ///if the last two character is "--" then ignore the line.
                if($c2=="-" && !feof($handle))
                {
                    while(!feof($handle))
                    {
                        $c3=fread($handle, 1);
                        if($c3=="\n")
                        {
                            $isComment=true;
                            break;
                        }
                        //$buffer[]=$c3;
                    }
                }
            }
            else if($c=="/" && !feof($handle))
            {
                $c2=fread($handle, 1);
                ///if the last two character is "--" then ignore the line.
                if($c2=="*" && !feof($handle))
                {
                    while(!feof($handle))
                    {
                        $c3=fread($handle, 1);
                        if($c3=="*" && !feof($handle))
                        {
                            $c4=fread($handle, 1);
                            if($c4=="/")
                            {
                                $isComment=true;
                                break;
                            }
                        }
                        //$buffer[]=$c3;
                    }
                }
            }
            else if($c=="'")
            {
                $buffer=$buffer.$c;
                while(!feof($handle))
                {
                    $c3=fread($handle, 1);
                    if($c3=="\\" && !feof($handle))
                    {
                        $buffer=$buffer.$c3;
                        $c5=fread($handle, 1);
                        $buffer=$buffer.$c5;
                    }
                    else if($c3=="'")
                    {
                        //$buffer[]=$c3;
                        break;
                    }
                    $buffer=$buffer.$c3;
                }
                //$tmp=$buffer[count($buffer)-10].$buffer[count($buffer)-9].$buffer[count($buffer)-8].$buffer[count($buffer)-7].$buffer[count($buffer)-6].$buffer[count($buffer)-5].$buffer[count($buffer)-4].$buffer[count($buffer)-3].$buffer[count($buffer)-2].$buffer[count($buffer)-1];
                //trace("===",29141);
            }
            else if($c=='"')
            {
                $buffer=$buffer.$c;
                while(!feof($handle))
                {
                    $c3=fread($handle, 1);
                    if($c3=="\\" && !feof($handle))
                    {
                        $buffer=$buffer.$c3;
                        $c5=fread($handle, 1);
                        $buffer=$buffer.$c5;
                    }
                    else if($c3=='"')
                    {
                        //$buffer[]=$c3;
                        break;
                    }
                    $buffer=$buffer.$c3;
                }
            }
            else if($c=="\\" && !feof($handle))
            {
                $buffer=$buffer.$c;
                $c2=fread($handle, 1);
                $buffer=$buffer.$c2;
            }
            else if($c==";")
            {
                //$strBuffer=implode("",$buffer);
                $trim=trim($buffer);
                if(!empty($trim)) return $trim;
                continue;
            }
            if($isComment)
            {
                $isComment=false;
                continue;
            }
            if($c4=="/")
            {
                
            }
            else if($c3=="\n")
            {
                
            }
            else if($c2=="-")
            {
                
            }
            else if($c2=="*")
            {
                
            }
            $buffer=$buffer.$c;
        }
        return $buffer;
    }
}
?>