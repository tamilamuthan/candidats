<?php
/**************************************************************************
 * Naanal PHP Framework, Simple, Efficient and Developer Friendly
 * Ver 3.0, Copyright (C) <2010>  <Tamil Amuthan. R>
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
class ClsAuieoSQLSelect
{
    private $database=null;
    private $from=null;
    private $field="";
    private $alias="";
    public function __construct(&$from,$field,$alias=false)
    {
        $this->from=$from;
        $this->field=$field;
        $this->alias=$alias;
    }
    public function setDatabase($database)
    {
        $this->database=$database;
    }
    public function getAlias()
    {
        return $this->alias;
    }
    public function render()
    {
        $render="";
        if(!is_null($this->database))
        {
            $render="`{$this->database}`.";
        }
        ///if it is custom field
        if(empty($this->from))
        {
            if($this->alias===false)
                $render=$render.$this->field;
            else
                $render=$render. "{$render}{$this->field} AS `{$this->alias}`";
        }
        else
        {
            $fieldPrefix=$this->from->getFieldPrefix();
            if($this->alias===false)
                $render=$render. "{$render}`{$fieldPrefix}`.`{$this->field}`";
            else
                $render=$render. "{$render}`{$fieldPrefix}`.`{$this->field}` AS `{$this->alias}`";
        }
        return $render;
    }
}
class ClsAuieoSQLWhere
{
    private $from=null;
    private $field="";
    private $whereData=array();
    private $data="";
    private $boolean="AND";
    private $condition="=";
    private $isGroup=false;
    private $isEnd=false;
    private $groupType="default";
    private $arrWhere=array();
    public function __construct(&$from,$field=false,$data=false)
    {
        $this->from=$from;
        $this->field=$field;
        $this->data=$data;
    }
    public function &addWhere(&$from=false,$field=false,$data=false)
    {
        if($from===false)$from=$this->from;
        $count=count($this->arrWhere);
        $this->arrWhere[$count]=new ClsAuieoSQLWhere($from);
        return $this->arrWhere[$count];
    }
    public function setWhereBoolean($boolean)
    {
        $this->arrWhere[]=$boolean;
    }
    public function isWhereGroup()
    {
        if($this->arrWhere) return true;
        return false;
    }
    /**
     * 
     * @param type $field
     * if $arrWhere is not empty(wheres as a group) this value will be ignored
     */
    public function setField($field)
    {
        $this->field=$field;
    }
    public function getField()
    {
        return $this->field;
    }
    /**
     * 
     * @param type $data
     * if $arrWhere is not empty(wheres as a group) this value will be ignored
     */
    public function setData($data)
    {
        $this->data=$data;
    }
    /**
     * values can be "start" or "end"
     * @param type $startOrEnd
     */
    public function setGroup($startOrEnd="start")
    {
        if($startOrEnd=="end")
        {
            $this->isEnd=true;
        }
        else
        {
            $this->isEnd=false;
        }
        $this->isGroup=true;
    }
    public function startGroup()
    {
        $this->groupType="start";
    }
    public function endGroup()
    {
        $this->groupType="end";
    }
    public function getGroupType()
    {
        return $this->groupType;
    }
    public function isGroupEnd()
    {
        return $this->isEnd;
    }
    public function isGroup()
    {
        return $this->isGroup;
    }
    public function setBoolean($boolean)
    {
        $this->boolean=$boolean;
    }
    public function getBoolean()
    {
        return $this->boolean;
    }
    public function addPrevWhere($where)
    {
        $this->whereData["boolean"]=$where->getBoolean();
    }
    public function setJoinBoolean($boolean)
    {
        $this->whereData["boolean"]=$boolean;
    }
    public function getJoinBoolean()
    {
        return isset($this->whereData["boolean"])?$this->whereData["boolean"]:"OR";
    }
    public function setCondition($condition)
    {
        $this->condition=$condition;
    }
    public function render()
    {
        if($this->arrWhere && is_array($this->arrWhere))
        {
            $arrSQL=array();
            $arrSQL[]="(";
            foreach($this->arrWhere as $where)
            {
                if(is_object($where))
                {
                    $arrSQL[]=$where->render();
                }
                else
                {
                    $arrSQL[]=$where;
                }
            }
            $arrSQL[]=")";
            $whereSql="";
            foreach($arrSQL as $sq)
            {
                if(empty($whereSql))
                {
                    if(is_array($sq))
                    {
                        $whereSql=$sq["where"];
                    }
                    else
                    {
                        $whereSql=$sq;
                    }
                }
                else
                {
                    if(is_array($sq))
                    {
                        $whereSql=$whereSql." ".$sq["where"];
                    }
                    else
                    {
                        $whereSql=$whereSql." ".$sq;
                    }
                }
            }//trace($whereSql,3);
            return array("where"=>$whereSql,"boolean"=>false);
        }
        else
        {
            ///if it is custom field
            $fld=$this->field;
            if(!empty($this->from))
            {
                $fieldPrefix=$this->from->getFieldPrefix();
                $fld="`{$fieldPrefix}`.`{$this->field}`";
            }
            $data=$this->data;

            $boolean="";
            $where="";
            if(!empty($this->whereData))
            {
                $boolean=$this->whereData["boolean"];
            }
            if($data===false)
            {
                return array("where"=>$this->field,"boolean"=>$boolean);
            }
            if(strtolower($this->condition)=="like")
            {
                $where="({$fld} LIKE '%{$data}%')";
            }
            else if(strtolower($this->condition)=="rlike")
            {
                $where="({$fld} RLIKE '{$data}')";
            }
            else if(strtolower($this->condition)=="rlikew")
            {
                $where="({$fld} RLIKE '[[:<:]]{$data}[[:>:]]')";
            }
            else if(strtolower($this->condition)=="startswith")
            {
                $where="({$fld} LIKE '{$data}%')";
            }
            else if(strtolower($this->condition)=="endswith")
            {
                $where="({$fld} LIKE '%{$data}')";
            }
            else if(strtolower($this->condition)=="between")
            {
                $arrBetween=explode(",",$data);
                $from=trim($arrBetween[0]);
                $to=trim($arrBetween[1]);
                $where="({$fld} BETWEEN {$from} AND {$to})";
            }
            else if(strtolower($this->condition)=="in")
            {
                $where="({$fld} IN ({$data}))";
            }
            else 
            {
                if($data=="NULL")
                {
                    $where="({$fld} {$this->condition} {$data})";
                }
                else
                {
                    $where="({$fld} {$this->condition} '{$data}')";
                }
            }
            return array("where"=>$where,"boolean"=>$boolean);
        }
    }
}
class ClsAuieoSQLHaving
{
    private $from=null;
    private $field="";
    private $havingData=array();
    private $data="";
    private $boolean="AND";
    private $condition="=";
    private $isGroup=false;
    private $isEnd=false;
    public function __construct(&$from,$field,$data=false)
    {
        $this->from=$from;
        $this->field=$field;
        $this->data=$data;
    }
    /**
     * values can be "start" or "end"
     * @param type $startOrEnd
     */
    public function setGroup($startOrEnd="start")
    {
        if($startOrEnd=="end")
        {
            $this->isEnd=true;
        }
        else
        {
            $this->isEnd=false;
        }
        $this->isGroup=true;
    }
    public function isGroupEnd()
    {
        return $this->isEnd;
    }
    public function isGroup()
    {
        return $this->isGroup;
    }
    public function setBoolean($boolean)
    {
        $this->boolean=$boolean;
    }
    public function getBoolean()
    {
        return $this->boolean;
    }
    public function addPrevWhere(ClsAuieoSQLHaving $having)
    {
        $this->whereData["boolean"]=$having->getBoolean();
    }
    public function setCondition($condition)
    {
        $this->condition=$condition;
    }
    public function render()
    {
        ///if it is custom field
        $fld=$this->field;
        if(!empty($this->from))
        {
            $fieldPrefix=$this->from->getFieldPrefix();
            $fld="`{$fieldPrefix}`.`{$this->field}`";
        }
        $data=$this->data;
        
        $boolean="";
        $having="";
        if(!empty($this->whereData))
        {
            $boolean=$this->whereData["boolean"];
        }
        if($data===false)
        {
            return array("having"=>$this->field,"boolean"=>$boolean);
        }
        if(strtolower($this->condition)=="like")
        {
            $having="({$fld} LIKE '%{$data}%')";
        }
        else if(strtolower($this->condition)=="rlike")
        {
            $having="({$fld} RLIKE '{$data}')";
        }
        else if(strtolower($this->condition)=="startswith")
        {
            $having="({$fld} LIKE '{$data}%')";
        }
        else if(strtolower($this->condition)=="endswith")
        {
            $having="({$fld} LIKE '%{$data}')";
        }
        else if(strtolower($this->condition)=="between")
        {
            $arrBetween=explode(",",$data);
            $from=trim($arrBetween[0]);
            $to=trim($arrBetween[1]);
            $having="({$fld} BETWEEN {$from} AND {$to})";
        }
        else if(strtolower($this->condition)=="in")
        {
            $having="({$fld} IN ({$data}))";
        }
        else 
        {
            $having="({$fld} {$this->condition} '{$data}')";
        }
        return array("having"=>$having,"boolean"=>$boolean);
    }
}
class ClsAuieoSQLFrom
{
    private $database=false;
    private $table=false;
    private $query=false;
    private $alias="";
    private $joinField="";
    private $joinFrom=null;
    private $joinType="LEFT JOIN";
    private $isJoin=false;
    private $isHidden=false;
    private $arrJoin=array();
    private $arrJoinField=array();
    private $arrErr=array();
    private $priority=999;
    private $name=false;
    
    /**
     * if true, the joins(inner, left or right) of the current From Object will be used for connecting with parent From object
     * else the joins(inner, left or right) of the parent From Object will be used for connecting with Curent From object
     * @var type boolean
     */
    private $joinByCurrentFromObject=false;
    
    public function __construct($alias=false,$name=false,$database=false)
    {
        $this->alias=$alias;
        $this->name=$name;
        $this->database=false;
    }
    public function joinByCurrentFromObject()
    {
        $this->joinByCurrentFromObject=true;
    }
    public function setPriority($priority)
    {
        $this->priority=$priority;
    }
    public function getPriority()
    {
        return $this->priority;
    }
    public function setTable($table)
    {
        $this->table=$table;
    }
    public function setDatabase($database)
    {
        $this->database=$database;
    }
    public function getDatabase()
    {
        return $this->database;
    }
    public function setQuery($query)
    {
        $this->query=$query;
    }
    public function setHidden()
    {
        $this->isHidden=true;
    }
    public function isHidden()
    {
        return $this->isHidden;
    }
    public function isJoinExist($joinID)
    {
        if(isset($this->arrJoinField[$joinID]))
        {
            return true;
        }
        return false;
    }
    public function setAlias($alias)
    {
        if($this->alias!==false) trace("name already set as {$this->alias} for {$this->alias} object");
        $this->alias=$alias;
    }
    public function setName($name)
    {
        if($this->name!==false) trace("name already set as {$this->name} for {$this->table} object");
        $this->name=$name;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getTable()
    {
        return $this->table;
    }
    public function getAliasOrTable()
    {
        if($this->alias) return $this->alias;
        return $this->table;
    }
    public function getAlias()
    {
        return $this->alias;
    }
    public function getError()
    {
        return $this->arrErr;
    }
    
    public function getJoinFieldIndex($field)
    {
        foreach($this->arrJoinField as $ind=>$arrJoin)
        {
            if($arrJoin["field"]==$field)
            {
                return $ind;
            }
        }
        return null;
    }
    
    public function getJoinField($index=false)
    {
        if($index===false)
            return $this->arrJoinField;
        else
        {
            if(isset($this->arrJoinField[$index]))
            {
                return $this->arrJoinField[$index];
            }
            else
            {//trace($this->arrJoinField);
                $this->arrErr[]="Join index not exist";
                return false;
            }
        }
    }
    public function getJoinFieldRender()
    {
        if($this->alias===false) return "`{$this->table}`.`{$this->joinField}`";
        return "`{$this->alias}`.`{$this->joinField}`";
    }
    public function updateJoinType($field,$join_type)
    {
        if(!is_string($field))
        {
            $this->arrErr[]="field is not string";
            return false;
        }
        foreach($this->arrJoinField as $ind=>$arrJoin)
        {
            if($arrJoin["field"]==$field)
            {
                $this->arrJoinField[$ind]["join_type"]=$join_type;
                return $ind;
            }
        }
        $this->arrErr[]="field not found";
        return false;
    }
    public function addJoinField($field,$join_type="LEFT JOIN")
    {
        $this->joinField=$field;
        /**
         * alias - table alias
         */
        $this->arrJoinField[]=array("field"=>$field,"join_type"=>$join_type,"table"=>$this->table,"alias"=>$this->alias,"database"=>$this->database);
        $lastRecord=count($this->arrJoinField)-1;
        return $lastRecord;
    }
    public function getFieldPrefix()
    {
        if($this->alias===false) return $this->table;
        return $this->alias;
    }
    /**
     * since single table can be joined with many table with different fields, 
     * this function expects the join id generated from addJoinField function
     */
    public function setJoinWith(ClsAuieoSQLFrom &$from,$fromJoinID, $toJoinID, $boolean="AND")
    {
        if(!is_numeric($fromJoinID)) 
        {
            $this->arrErr[]="From Join ID(".print_r($fromJoinID,true).") is not a number";
            return false;
        }
        if(!is_numeric($toJoinID)) 
        {
            $this->arrErr[]="From Join ID(".print_r($toJoinID,true).") is not a number";
            return false;
        }
        if(!$from->isJoinExist($fromJoinID)) 
        {
            $this->arrErr[]="invalid join id({$fromJoinID}) in the from object";
            return false;
        }
        $this->arrJoin[]=array("joinFrom"=>$from->getJoinField($fromJoinID),"joinTo"=>$this->getJoinField($toJoinID),"boolean"=>$boolean);
        $this->isJoin=true;
        return true;
    }
    public function isJoin()
    {
        return $this->isJoin;
    }
    public function render($alias=false)
    {
        if($this->isHidden) return null;
        if($this->query!==false)
        {
            if($alias!==false) $this->alias=$alias;
            $render="({$this->query}) {$this->alias}";
        }
        else
        {
            if($alias!==false) $this->alias=$alias;
            $render="";
            if($this->database)
            {
                $render=$render."`{$this->database}`.";
            }
            if($this->alias===false)
            {
                $render=$render."`{$this->table}`";
            }
            else
            {
                $render=$render."`{$this->table}` AS {$this->alias}";
            }
        }

        if(empty($this->arrJoin)) return $render;
        $strjoin=false;
        foreach($this->arrJoin as $ind=>$join)
        {
            $arrFromJoin=$join["joinFrom"];
            $arrToJoin=$join["joinTo"];
            $fromField=$arrFromJoin["field"];
            $fromType=$arrFromJoin["join_type"];
            $fromDatabase=$arrFromJoin["database"];
            $toField=$arrToJoin["field"];
            $toType=$arrToJoin["join_type"];
            $toDatabase=$arrToJoin["database"];
//if($this->table=="auieo_smownerid") trace($this->joinByCurrentFromObject);
            $joinFromRender="";
            if($fromDatabase)
            {
                $joinFromRender="`{$fromDatabase}`.";
            }
            $joinToRender="";
            if($toDatabase)
            {
                $joinToRender="`{$toDatabase}`.";
            }
            if($arrFromJoin["alias"]===false)
                $joinFromRender="{$joinFromRender}`{$arrFromJoin["table"]}`.`{$arrFromJoin["field"]}`";
            else
                $joinFromRender="`{$arrFromJoin["alias"]}`.`{$arrFromJoin["field"]}`";
            if($arrToJoin["alias"]===false)
                $joinToRender="{$joinToRender}`{$arrToJoin["table"]}`.`{$arrToJoin["field"]}`";
            else
                $joinToRender="`{$arrToJoin["alias"]}`.`{$arrToJoin["field"]}`";
            if(empty($strjoin))
            {
                if($this->joinByCurrentFromObject)
                {
                    $strjoin="{$toType} {$render} ON {$joinFromRender}={$joinToRender}";
                }
                else
                {
                    $strjoin="{$fromType} {$render} ON {$joinFromRender}={$joinToRender}";
                }
            }
            else
            {
                $boolean=$join["boolean"];
                $strjoin=" {$strjoin} {$boolean} {$joinFromRender}={$joinToRender}";
            }
        }
        return $strjoin;
    }
}
class ClsAuieoSQL
{
    private $sqlType="SELECT";
    private $arrWhereNew=array();
    private $from=-1;
    private $length=20;
    private $arrValue=array();
    public $isDistinct=false;
    private $arrOrderBy=array();
    private $arrErr=array();
    
    private $arrFrom=array();
    private $arrSelect=array();
    private $arrWhere=array();
    private $arrHaving=array();
    
    private $groupBy=array();
    
    /**
     * if true, the joins(inner, left or right) of the current From Object will be used for connecting with parent From object
     * else the joins(inner, left or right) of the parent From Object will be used for connecting with Curent From object
     * @var type boolean
     */
    private $joinByCurrentFromObject=false;
    
    private $version=1;

    function __construct($sqlType="SELECT")
    {
        $this->sqlType=$sqlType;
    }
    function setSQLType($sqlType)
    {
        $this->sqlType=$sqlType;
    }
    public function setDistinct()
    {
        $this->isDistinct=true;
    }

    public function joinByCurrentFromObject()
    {
        $this->joinByCurrentFromObject=true;
    }
    public function setVersion($version)
    {
        $this->version=$version;
    }
    public static function getInstance($sqlType="SELECT")
    {
        return new ClsAuieoSQL($sqlType);
    }
    public function setWhereBoolean($boolean)
    {
        $this->arrWhere[]=$boolean;
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
    function &addSelect(&$from,$field,$alias=false)
    {
        $this->arrSelect[]=new ClsAuieoSQLSelect($from,$field,$alias);
        return $this->arrSelect[count($this->arrSelect)-1];
    }
    function &addSelectCustom($field,$alias=false)
    {
        $from=false;
        $this->arrSelect[]=new ClsAuieoSQLSelect($from,$field,$alias);
        return $this->arrSelect[count($this->arrSelect)-1];
    }
    function insertSelectCustom($pos,$field,$alias=false)
    {
        $from=false;
        $tmp=new ClsAuieoSQLSelect($from,$field,$alias);
        array_splice($this->arrSelect, $pos, 0, array($tmp));
    }
    function getPrevWhereObjectIndex($index)
    {
        do
        {
            $index=$index-1;
            if($index===-1) return false;
            $obj=$this->arrWhere[$index];
            if(is_object($obj)) return $index;
        } while (true);
    }
    function clearWhere()
    {
        $this->arrWhere=array();
    }
    function &addWhere(&$from,$field,$data=false)
    {
        $this->arrWhere[]=new ClsAuieoSQLWhere($from,$field,$data);
        if(count($this->arrWhere)>=2)
        {
            $prevIndex=$this->getPrevWhereObjectIndex(count($this->arrWhere)-1);
            if($prevIndex!==false)
            $this->arrWhere[count($this->arrWhere)-1]->addPrevWhere($this->arrWhere[$prevIndex]);
        }
        return $this->arrWhere[count($this->arrWhere)-1];
    }
    function setWhereObject(&$objWhere)
    {
        $this->arrWhere[]=$objWhere;
    }
    function setWhereGroupStart()
    {
        $this->arrWhere[]="(";
        return true;
    }
    function setWhereGroupEnd()
    {
        $this->arrWhere[]=")";
        return true;
    }
    /*function &addHaving(&$from,$field,$data=false)
    {
        $this->arrHaving[]=new ClsAuieoSQLHaving($from,$field,$data);
        if(count($this->arrHaving)>=2)
        {
            $this->arrHaving[count($this->arrWhere)-1]->addPrevHaving($this->arrHaving[count($this->arrHaving)-2]);
        }
        return $this->arrHaving[count($this->arrHaving)-1];
    }*/
    function &addHaving(&$from,$field,$data=false)
    {
        $this->arrHaving[]=new ClsAuieoSQLHaving($from,$field,$data);
        if(count($this->arrHaving)>=2)
        {
            $prevIndex=$this->getPrevHavingObjectIndex(count($this->arrHaving)-1);
            if($prevIndex!==false)
            $this->arrHaving[count($this->arrHaving)-1]->addPrevHaving($this->arrHaving[$prevIndex]);
        }
        return $this->arrHaving[count($this->arrHaving)-1];
    }
    function addGroupBy(&$from,$field)
    {
        $this->groupBy[]=array("from"=>$from,"field"=>$field);
    }
    public function &getNewDisconnectedFrom()
    {
        $from=new ClsAuieoSQLFrom($alias);
        $from->setTable($table);
        return $from;
    }
    public function getFromObjectByObject(&$objSQLFrom)
    {
        if($this->arrFrom)
        foreach($this->arrFrom as $ind=>$from)
        {
            if($objSQLFrom===$from) return $from;
        }
        return false;
    }
    public function &getFromObject($table=false,$joinField=false,$alias=false,$createIfNotExist=false)
    {
        if($this->arrFrom)
        foreach($this->arrFrom as $ind=>$from)
        {
            $from_table=$from->getTable();
            if($table==$from_table)
            {
                if($alias===false)
                {
                    if($joinField===false)
                    {
                        return $this->arrFrom[$ind];
                    }
                    else
                    {
                        $joinIndex=$this->arrFrom[$ind]->getJoinFieldIndex($joinField);
                        if(!is_null($joinIndex))
                        {
                            return $this->arrFrom[$ind];
                        }
                    }
                }
                else
                {
                    if($this->arrFrom[$ind]->getAlias()==$alias)
                    {
                        if($joinField===false)
                        {
                            return $this->arrFrom[$ind];
                        }
                        else
                        {
                            $joinIndex=$this->arrFrom[$ind]->getJoinFieldIndex($joinField);
                            if(!is_null($joinIndex))
                            {
                                return $this->arrFrom[$ind];
                            }
                        }
                    }
                }
            }
        }
        //$from=new ClsAuieoSQLFrom();
        $from=null;
        if($createIfNotExist)
        {
            if($alias===false)
            {
                $from=$this->addFrom($table);
            }
            else
            {
                $from=$this->addFrom($table,$alias);
            }
            if($joinField!==false)
            {
                $from->addJoinField($joinField);
            }
        }
        return $from;
    }
    public function getSelectObjectPositionByAlias($alias)
    {
        if($this->arrSelect)
        {
            foreach($this->arrSelect as $ind=>$objSelect)
            {
                if($objSelect->getAlias()==$alias)
                {
                    return $ind;
                }
            }
        }
        return 0;
    }
    /**
     * 
     * @param type $name - name of the from object
     */
    public function &getFromObjectByName($name)
    {
        if($this->arrFrom)
        foreach($this->arrFrom as $ind=>$from)
        {
            $name_of_the_object=$from->getName();
            if($name==$name_of_the_object)
            {
                return $this->arrFrom[$ind];
            }
        }
        $tmp=null;
        return $tmp;
    }
    public function &getFromObjectByTableName($table,$createIfNotExist=false)
    {
        if($this->arrFrom)
        foreach($this->arrFrom as $ind=>$from)
        {
            $from_table=$from->getTable();
            if($table==$from_table)
            {
                return $this->arrFrom[$ind];
            }
        }
        $from=null;
        if($createIfNotExist)
        {
            $from=$this->addFrom($table);
        }
        return $from;
    }
    public function &getFromObjectByTableAlias($alias,$table=false,$createIfNotExist=false)
    {
        if($this->arrFrom)
        foreach($this->arrFrom as $ind=>$from)
        {
            $from_alias=$from->getAlias();
            if($alias==$from_alias)
            {
                if($table===false)
                {
                    return $this->arrFrom[$ind];
                }
                if($table==$from->getTable())
                {
                    return $this->arrFrom[$ind];
                }
            }
        }
        $from=null;
        if($table!==false && $createIfNotExist===true)
        {
            $from=$this->addFrom($table,$alias);
        }
        return $from;
    }
    public function renderSelect()
    {
        $arrSelect=array();
        if(empty($this->arrSelect)) return "*";
        foreach($this->arrSelect as $objSelect)
        {
            $arrSelect[]=$objSelect->render();
        }
        return implode(",",$arrSelect);
    }
    public function processGroupWhere($startIndex=0, $level=0)
    {
        $isGroupStarted=false;
        $arrWhere=array();
        $isLevelEnd=false;
        for($index=$startIndex;$index<count($this->arrWhere);$index++)
        {
            $objWhere=$this->arrWhere[$index];
            if(is_object($objWhere))
            {
                $arrWhere[]=$objWhere->render(); 
            }
            else if($objWhere=="(")
            {
                $arrData=$this->processGroupWhere($index+1, $level+1);
                $endIndex=$arrData["endIndex"];
                $arrWhere[]=array("where"=>$arrData["render"],"boolean"=>$arrData["boolean"],"level"=>$arrData["level"]);
                $index=$endIndex;
                continue;
            }
            else if($objWhere==")")
            {
                $isLevelEnd=true;
                break;
            }
            else
            {
                die("unexpected where");
            }
        }
        $arr=array();
        $firstBoolean=false;
        $firstWhere=false;
        $where="";

        foreach($arrWhere as $ind=>$awhr)
        {
            if($firstBoolean===false) $firstBoolean=$awhr["boolean"];
            if($ind===0)
            {
                $where=$where."(".$awhr["where"];
            }
            else
            {
                $where=$where." {$awhr["boolean"]} ".$awhr["where"];
            }
        }
        $where=$where.")";
        return array("endIndex"=>$index,"render"=>$where, "boolean"=>$firstBoolean, "level"=>$level);
    }
    public function renderWhereNew() 
    {
        $arrWhere=array();
        if(empty($this->arrWhere)) return false;
        $firstBoolean=false;
        $where="";
        for($ind=0;$ind<count($this->arrWhere);$ind++)// as $ind=>$objWhere)
        {
            $objWhere=$this->arrWhere[$ind];
            if($objWhere=="(")
            {
                $arrWhere=$this->processGroupWhere($ind+1);
                $ind=$arrWhere["endIndex"];
                $awhr["where"]=$arrWhere["render"];
                $awhr["boolean"]=$arrWhere["boolean"];
            }
            else
            {
                $awhr=$objWhere->render();
            }
            if($firstBoolean===false) $firstBoolean=$awhr["boolean"];
            if($ind===0)
            {
                $where=$where." {$firstBoolean} (".$awhr["where"];
            }
            else
            {
                $where=$where." {$awhr["boolean"]} ".$awhr["where"];
            }
        }
        return (" WHERE ".$where);
        //$arrWhere=$this->processGroupWhere();
        $groupInd=0;
        $isGroupStarted=false;
        foreach($this->arrWhere as $ind=>$objWhere)
        {
            if($isGroupStarted)
            {
                if($objWhere->getGroupType()=="end")
                {
                    /**
                     * adding new group
                     */
                    $groupInd=$groupInd+1;
                    $isGroupStarted=false;
                }
                else if($objWhere->getGroupType()=="end")
                {
                    
                }
            }
            else
            {
                if($objWhere->getGroupType()=="start")
                {
                    /**
                     * adding new group
                     */
                    $this->processGroupWhere($ind);
                    $groupInd=$groupInd+1;
                    $isGroupStarted=true;
                }
            }
            $arrWhere[$groupInd][]=$objWhere->render();
        }
        $arr=array();
        $firstBoolean=false;
        $firstWhere=false;
        $where="";
        foreach($arrWhere as $groupInd=>$arrWhr)
        {
            foreach($arrWhr as $ind=>$awhr)
            {
                if($firstBoolean===false) $firstBoolean=$awhr["boolean"];
                if($ind===0)
                {
                    $where=$where." {$firstBoolean} (".$awhr["where"];
                }
                else
                {
                    $where=$where." {$awhr["boolean"]} ".$awhr["where"];
                }
            }
            $where=$where.")";
            $firstBoolean=false;
        }
        return " WHERE ".$where;
    }
    public function renderWhere()
    {
        if($this->version===2) return $this->renderWhereNew();
        $arrWhere=array();
        if(empty($this->arrWhere)) return false;
        $groupInd=0;
        $isGroupStarted=false;
        /*foreach($this->arrWhere as $objWhere)
        {
            $where=$objWhere->render();
        }
        return " WHERE ".$where;*/
        foreach($this->arrWhere as $objWhere)
        {
            if($isGroupStarted)
            {
                if(!$objWhere->isGroup())
                {
                    /**
                     * adding new group
                     */
                    $groupInd=$groupInd+1;
                    $isGroupStarted=false;
                }
                else
                {
                    /**
                     * if two or more groups coming contineous, the group has to be ended with setGroup("end"). The end element also included
                     * to the group. If the group end without end setting, it means the current element is out of group. so we should not include 
                     * it in group
                     */
                    if($objWhere->isGroupEnd())
                    {
                        /**
                         * be in the same group
                         */
                        $isGroupStarted=false;
                    }
                }
            }
            else
            {
                if($objWhere->isGroup())
                {
                    /**
                     * adding new group
                     */
                    $groupInd=$groupInd+1;
                    $isGroupStarted=true;
                }
            }
            $arrWhere[$groupInd][]=$objWhere->render();
        }
        $arr=array();
        $firstBoolean=false;
        $firstWhere=false;
        $where="";
        foreach($arrWhere as $groupInd=>$arrWhr)
        {
            foreach($arrWhr as $ind=>$awhr)
            {
                if($firstBoolean===false) $firstBoolean=$awhr["boolean"];
                if($ind===0)
                {
                    if($firstBoolean===false)
                    {
                        $where=$where." (".$awhr["where"];
                    }
                    else
                    {
                        $where=$where." {$firstBoolean} (".$awhr["where"];
                    }
                }
                else
                {
                    $where=$where." {$awhr["boolean"]} ".$awhr["where"];
                }
            }
            $where=$where.")";
            $firstBoolean=false;
        }
        return " WHERE ".$where;
    }
    public function renderHaving()
    {
        if($this->version===2) return $this->renderHavingNew();
        $arrWhere=array();
        if(empty($this->arrHaving)) return false;
        $groupInd=0;
        $isGroupStarted=false;
        /*foreach($this->arrWhere as $objWhere)
        {
            $where=$objWhere->render();
        }
        return " WHERE ".$where;*/
        foreach($this->arrHaving as $objWhere)
        {
            if($isGroupStarted)
            {
                if(!$objWhere->isGroup())
                {
                    /**
                     * adding new group
                     */
                    $groupInd=$groupInd+1;
                    $isGroupStarted=false;
                }
                else
                {
                    /**
                     * if two or more groups coming contineous, the group has to be ended with setGroup("end"). The end element also included
                     * to the group. If the group end without end setting, it means the current element is out of group. so we should not include 
                     * it in group
                     */
                    if($objWhere->isGroupEnd())
                    {
                        /**
                         * be in the same group
                         */
                        $isGroupStarted=false;
                    }
                }
            }
            else
            {
                if($objWhere->isGroup())
                {
                    /**
                     * adding new group
                     */
                    $groupInd=$groupInd+1;
                    $isGroupStarted=true;
                }
            }
            $arrWhere[$groupInd][]=$objWhere->render();
        }
        $arr=array();
        $firstBoolean=false;
        $firstWhere=false;
        $where="";
        foreach($arrWhere as $groupInd=>$arrWhr)
        {
            foreach($arrWhr as $ind=>$awhr)
            {
                if($firstBoolean===false) $firstBoolean=$awhr["boolean"];
                if($ind===0)
                {
                    if($firstBoolean===false)
                    {
                        $where=$where." (".$awhr["having"];
                    }
                    else
                    {
                        $where=$where." {$firstBoolean} (".$awhr["having"];
                    }
                }
                else
                {
                    $where=$where." {$awhr["boolean"]} ".$awhr["having"];
                }
            }
            $where=$where.")";
            $firstBoolean=false;
        }
        return " HAVING ".$where;
    }
    /**
     * 
     * @param type $table - from table name
     * @param type $alias - alias for the table used in the query
     * @param type $priority - priority of the table. those with low in number will get high prority
     * @param type $name - the referance name for the table. by using the reference a from object can be retrieved
     * @return type
     */
    function &addFrom($table=false,$alias=false,$priority=999,$name=false)
    {
        $this->arrFrom[]=new ClsAuieoSQLFrom($alias,$name);
        $this->arrFrom[count($this->arrFrom)-1]->setTable($table);
        $this->arrFrom[count($this->arrFrom)-1]->setPriority($priority);
        if($this->joinByCurrentFromObject)
        {
            $this->arrFrom[count($this->arrFrom)-1]->joinByCurrentFromObject();
        }
        return $this->arrFrom[count($this->arrFrom)-1];
    }
    function &addCustomFrom($sql,$alias=false,$priority=999,$name=false)
    {
        $this->arrFrom[]=new ClsAuieoSQLFrom($alias);
        $this->arrFrom[count($this->arrFrom)-1]->setQuery($sql);
        $this->arrFrom[count($this->arrFrom)-1]->setPriority($priority);
        if($this->joinByCurrentFromObject)
        {
            $this->arrFrom[count($this->arrFrom)-1]->joinByCurrentFromObject();
        }
        return $this->arrFrom[count($this->arrFrom)-1];
    }
    function getDefaultTable()
    {
        return isset($this->arrFrom[0])?$this->arrFrom[0]->getTable():false;
    }
    function &getDefaultFrom()
    {
        $obj = isset($this->arrFrom[0])?$this->arrFrom[0]:null;
        return $obj;
    }
    function &addQuery($query,$alias)
    {
        $this->arrFrom[]=new ClsAuieoSQLFrom($alias);
        $this->arrFrom[count($this->arrFrom)-1]->setQuery($query);
        return $this->arrFrom[count($this->arrFrom)-1];
    }
    public function renderFrom()
    {
        $arrFrom=array();
        foreach($this->arrFrom as $from)
        {
            $arrFrom[$from->getPriority()][]=$from;
        }
        ksort($arrFrom,SORT_NUMERIC);
        
        $render="";
        foreach($arrFrom as $arrF)
        {
            foreach($arrF as $from)
            {
                if($from->isHidden()) continue;
                if($from->isJoin())
                {
                    if(empty($render))
                    {
                        $render=$from->render();
                    }
                    else
                    {
                        $render=$render." ".$from->render();
                    }
                }
                else
                {
                    if(empty($render))
                    {
                        $render=$from->render();
                    }
                    else
                    {
                        $render=$render.",".$from->render();
                    }
                }
            }
        }
        return $render;
    }
    public function &getLastFromObject()
    {
        $objFrom=$this->arrFrom[count($this->arrFrom)-1];
        return $objFrom;
    }
    public function addJoinToPrevious($join_field, $join_type)
    {
        if(empty($this->arrFrom) && count($this->arrFrom)<2) die("No from object exist to join");
        if($this->arrFrom[count($this->arrFrom)-2]->isHidden())
        {
            $firstFromJoinField=$this->arrFrom[count($this->arrFrom)-3]->getJoinField();
        }
        else
        {
            $firstFromJoinField=$this->arrFrom[count($this->arrFrom)-2]->getJoinField();
        }
        if(empty($firstFromJoinField))  die("first from object does not has join field");
        $this->arrFrom[count($this->arrFrom)-1]->addJoin($this->arrFrom[count($this->arrFrom)-2],$join_field, $join_type);
    }
    /*public function addJoinTo(ClsAuieoSQLFrom &$from,$join_field, $join_type)
    {
        if(empty($this->arrFrom) && count($this->arrFrom)<2) die("No from object exist to join");
        $firstFromJoinField=$from->getJoinField();
        if(empty($firstFromJoinField))  die("first from object does not has join field");
        $this->arrFrom[count($this->arrFrom)-1]->setJoinWith($from,$join_field, $join_type);
    }*/
    function addFieldCreate($field,$type="int",$size=11)
    {
        $this->arrFieldCreate[$field]=array("type"=>$type,"size"=>$size);
    }
    function addValue($field,$value)
    {
        $this->arrValue[$field]=$value;
        $this->addField($field);
    }
    function addCustomWhere($custom_field,$data,$condition="=",$boolean="AND")
    {
        $this->arrWhere[]=array("table"=>false,"field"=>$custom_field,"data"=>$data,"condition"=>$condition,"boolean"=>$boolean);
    }
    /**
     * add where data structure.
     * The format is array("table"=>$table,"field"=>$field,"data"=>$data,"condition"=>$condition,"boolean"=>$boolen)
     */
    function &addWhereDS($arrWhereDS)
    {
        $from=false;
        if(!isset($arrWhereDS["table"]))
        {
            $from=$this->getDefaultFrom();
        }
        else
        {
            $from=$this->getFromObject($table);
        }
        $objWhere=null;
        if(isset($arrWhereDS["data"]))
        {
            $objWhere=$this->addWhere($from, $arrWhereDS["field"],$arrWhereDS["data"]);
        }
        else
        {
            $objWhere=$this->addWhere($from, $arrWhereDS["field"]);
        }
        if(isset($arrWhereDS["condition"]))
            $objWhere->setCondition($arrWhereDS["condition"]);
        if(isset($arrWhereDS["boolean"]))
            $objWhere->setBoolean($arrWhereDS["boolean"]);
        return $objWhere;
    }
    public function addOrderBy($orderBy,$isAsc=true)
    {
        $this->arrOrderBy[]=array("field"=>$orderBy,"isAsc"=>$isAsc);
    }
    function &render($isCount=false)
    {
        if(empty($this->arrFrom))
        {
            $this->arrErr[]="No Table Specified";
            $temp=false;
            return $temp;
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
        if(strtolower($this->sqlType)=="insert")
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
            $fromClause=$this->renderFrom();
            $sql="INSERT INTO {$fromClause} ({$fields}) VALUES({$values})";
            return $sql;
        }
        $where=$this->renderWhere();
        if(strtolower($this->sqlType)=="delete")
        {
            $sql="DELETE FROM {$this->arrTable[0]} ".$where;
            return $sql;
        }
        if(strtolower($this->sqlType)=="update")
        {
            $fields="";
            if(empty($this->arrValue)) trace("No Field added for insert");
            foreach($this->arrValue as $k=>$v)
            {
                $fields=$fields."`{$k}`='".addslashes($v)."',";
            }
            $fields=trim($fields,",");
            $fromClause=$this->renderFrom();
            if($where)
                $sql="UPDATE {$fromClause} SET {$fields} ".$where;
            else 
                $sql="UPDATE {$fromClause} SET {$fields}";
            return $sql;
        }

        $selectClause=$this->renderSelect();
        $fromClause=$this->renderFrom();
        if($isCount)
        {
            $sql = "SELECT count(*) AS count FROM {$fromClause}{$where}";
            return $sql;
        }
        else
        {
            $distinct="";
            if($this->isDistinct) $distinct="distinct ";
            $sql="SELECT {$distinct}{$selectClause} FROM {$fromClause}{$where}";
        }
        if($this->groupBy)
        {
            $sql=$sql." GROUP BY ";
            $arrGroupField=array();
            foreach($this->groupBy as $arrGroupBy)
            {
                if($arrGroupBy["from"]->getDatabase())
                {
                    $arrGroupField[]="`".$arrGroupBy["from"]->getDatabase()."`.`".$arrGroupBy["from"]->getAliasOrTable()."`.{$arrGroupBy["field"]}";
                }
                else
                {
                    $arrGroupField[]="`".$arrGroupBy["from"]->getAliasOrTable()."`.{$arrGroupBy["field"]}";
                }
            }
            $sql=$sql.implode(",",$arrGroupField);
            $having=$this->renderHaving();
            if($having)
                $sql=$sql." ".$having;
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
        addLog($sql);
        return $sql;
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