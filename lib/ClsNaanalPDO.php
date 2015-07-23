<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

///this class is created assuming the Named Placeholders of prepared statement is used. index based parameters in the sql statement will not work
class ClsNaanalPDO extends PDO
{
    protected $statement=null; 
    public $error=null;
    public $arrLastSql=array();
    private $dbname="";
    public $name="";
    public $sql="";
    public $arrParameter=array();
    
    protected $arrError=array();
    protected $stopOnError=false;
    
    public static $instanceCount=0;
    public static $pdo=array();
    public static $instanceMax=10;
    public static $arrStaticError=array();
    ///temporary row holder when using query_result method
    public static $arrFetchRow=array();
    private $rowCount=0;

    public $dieOnError=false;
    /**
    * possible sqlserver values are mysql, sybase, mssql
    */
    public function __construct($host,$user,$pass,$dbname=null,$sqlserver="mysql") 
    {
        try 
        {
            if(strtolower($sqlserver)=="mysql")
            {
                $dsn="mysql:host=$host";
                if($dbname)
                {
                    $this->dbname=$dbname;
                    $dsn="mysql:host=$host;dbname=$dbname";
                }
                parent::__construct($dsn, $user, $pass);
            }
            else if(strtolower($sqlserver)=="sqlite")
            {
                $dsn="sqlite:{$dbname}";
                parent::__construct($dsn);
            }
            else
            {
                die ("Unknown DSN. Please set config.php");
            }
            Logger::getLogger("AuieoATS")->info("dsn is {$dsn}");
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->setStopOnError();
            $this->fetchmode = PDO::FETCH_ASSOC;
        }   
        catch(PDOException $e)
        {
            die($e); 
        }
    }
    public function resetFetchRow()
    {
        self::$arrFetchRow=array();
    }
    function query_result(&$result, $row, $col=0)
    {
        if(!isset(self::$arrFetchRow[$row]))
        {
            if (!is_object($result))
                trace("result is not an object");
            $result->Move($row);
            self::$arrFetchRow[$row] = $result->FetchRow();
        }
        if(!isset(self::$arrFetchRow[$row]))
        {
            return null;
        }
        return self::$arrFetchRow[$row][$col];
        //$this->change_key_case($result->FetchRow());
        //$this->println($rowdata);
        //Commented strip_selected_tags and added to_html function for HTML tags vulnerability
        /*if($col == 'fieldlabel') $coldata = $rowdata[$col];
        else $coldata = isset($rowdata[$col])?$rowdata[$col]:null;
        return $coldata;*/
    }
    // Function to get particular row from the query result
	function query_result_rowdata(&$result, $row=0) {
        if (!is_object($result))
                throw new Exception("result is not an object");
        $result->Move($row);
        $rowdata = $this->change_key_case($result->FetchRow());
        if($rowdata)
                foreach($rowdata as $col => $coldata) {
                if($col != 'fieldlabel')
                        $rowdata[$col] = to_html($coldata);
        }
        return $rowdata;
    }
    function change_key_case($arr) {
		return is_array($arr)?array_change_key_case($arr):$arr;
	}
    /*public function query_result(&$PDOStatement,$row,$column)
    {
        if(!isset(self::$arrFetchRow[$row]))
        {
            self::$arrFetchRow[]=$PDOStatement->fetch();
        }
        if(!isset(self::$arrFetchRow[$row]))
        {
            die("Unexpected result");
        }
        return self::$arrFetchRow[$row][$column];
    }*/
    public function &query($query, $ignoreErrors = false)
    {
        self::$arrFetchRow=array();
        try
        {
            $this->statement=$this->prepare($query);
            $success=$this->statement->execute();
        }
        catch(Exception $e)
        {
            trace("PDO Query execution failed prematurely. Error: ".print_r($e));
        }
        if($this->statement===false)
        {
            return $this->statement;
        }
        $this->rowCount=$this->statement->rowCount();
        return $this->statement;
    }
    public function &pquery($query,$params=array())
    {
        //if(isset($params[1]) && $params[0]==20)
        //{
        if(!empty($params))
        {
            $param = $this->flatten_array($params);
        }
        else
        {
            $param=$params;
        }
        /*}
        else
        {
            $param=$params;
        }*/
        self::$arrFetchRow=array();
        $arrSplit=preg_split('/([\s]?[\?]+[\s]?)/', $query);
        if(count($arrSplit)>1)
        {
            $this->statement=$this->prepare($query);
            if($this->statement===false) return false;
            if($param)
            {
                $success=$this->statement->execute($param);
            }
            else
                $success=$this->statement->execute();
            if($success===false) return false;
            $this->rowCount=$this->statement->rowCount();
            $trimquery=trim($query);
            $upperquery=  strtoupper($trimquery);
            if(strpos($upperquery, "SELECT")===0)
            {
                $objStatement = new ClsAuieoResultset($this->statement);
                return $objStatement;
            }
            else
            {
                return $this->statement;
            }
        }
        else
        {
            $this->statement=$this->query($query);
            if($this->statement===false) return false;
            $this->rowCount=$this->statement->rowCount();
            $objStatement = new ClsAuieoResultset($this->statement);
            return $objStatement;
        }
    }
    public function fetch_array(&$result=false)
    {
        if(!is_object($result) || $result->EOF) {
			return false;
		}
		$arr = $result->FetchRow();
		$v_arr=array_values($arr);
		for($ti=0;$ti<count($v_arr);$ti++)
		{
			$arr[$ti]=$v_arr[$ti];
		}
		if(is_array($arr))
			$arr = array_map('to_html', $arr);
		return $this->change_key_case($arr);
    }
    function fetchByAssoc(&$result, $rowNum = -1, $encode=true)
    {
        if($result->EOF) {
                $this->arrError[]="ADODB fetchByAssoc return null";
                return NULL;
        }
        if(isset($result) && $rowNum < 0) {
                $row = $this->change_key_case($result->GetRowAssoc(false));
                $result->MoveNext();
                if($encode&& is_array($row))
                        return array_map('to_html', $row);
                return $row;
        }

        if($this->getRowCount($result) > $rowNum) {
                $result->Move($rowNum);
        }
        $this->lastmysqlrow = $rowNum;
        $row = $this->change_key_case($result->GetRowAssoc(false));
        $result->MoveNext();
        $this->println($row);

        if($encode&& is_array($row))
                return array_map('to_html', $row);
        return $row;
    }
    /** * MetaColumns: Retrieve information about a table's columns * @param table String name of table to find out about * @return Array of ADODB_PDO_FieldData objects */ 
    public function MetaColumns($table) 
    {
        $select = $this->DoQuery('SELECT * FROM '.$table.' limit 0,1');

        $total_column = $select->columnCount();

        for ($counter = 0; $counter < $total_column; $counter ++) {
            $meta = $select->getColumnMeta($counter);
            $column[] = $meta;
        }
        return $column;
    }
    /** * DoQuery: Private helper function for Get* * @param sql String query to execute * @param vars Array of variables to bind [optional] * @return PDOStatement object of results, or false on fail */
    ///DoQuery can execute batch sqls
    private function DoQuery($sql, $vars=null)
    {
        $arrSql=array();
        if(is_array($sql))
        {
            foreach($sql as $s)
            {
                $this->arrLastSQL[]=$s;
                $arrSql[]=trim($s);
            }
        }
        else
        {
            $this->arrLastSQL[]=$sql;
            $arrSql[]=trim($sql);
        }
        $arrSt=array();
        //$this->_db->beginTransaction();
        foreach($arrSql as $sql)
        {
            $isSelectQuery=false;
            $isInsertQuery=false;

            $pos=stripos($sql, 'select');
            if($pos!==0)
            {
                $pos=stripos($sql, 'show');
            }

            if($pos===0)
            {
                    $isSelectQuery=true;
            }

            $pos=stripos($sql, 'insert');
            if($pos===0)
            {
                    $isInsertQuery=true;
            }
            $this->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $st = $this->prepare($sql);
            if($isSelectQuery) $st->setFetchMode($this->fetchmode);
            if(is_null($vars))
            {
                $vars=array();
            }
            else if (!is_array($vars)) {
                    $vars = array($vars);
            }
            try
            {
                if($vars) $success=$st->execute($vars);
                else $success=$st->execute();
            }
            catch(Exception $e)
            {
                trace($e);
            }
            if(!$success) 
            {
                $erroMessage=$this->ErrorMsg();
                //$this->_db->rollBack();
                trace("Error occured while executing $sql and rolled back. Error Msg:".$erroMessage);
            }
            if($isSelectQuery)
            {
                $arrSt[]=$st;
            }
            else if($isInsertQuery)
            {
                $arrSt[]=$this->_db->lastInsertId();
            }
            else
            {
                $arrSt[]=$st;
            }
        }
        //$this->_db->commit();
        $this->statement_error=$st->errorInfo();
        return count($arrSt)>1?$arrSt:$arrSt[0];
    }
    
    public function fetch_row()
    {
        return $this->statement->fetch(PDO::FETCH_NUM);
    }
    public function fetch_assoc()
    {
        return $this->statement->fetch(PDO::FETCH_ASSOC);
    }
    public function num_rows()
    {
        return $this->rowCount;
    }
    function setStopOnError()
    {
        $this->stopOnError=true;
    }
    function getDatabase()
    {
        return $this->dbname;
    }
    function __destruct()
    {
        if(!is_null($this->statement)) $this->statement=null;
    }
    //To get a function name with respect to the database type which escapes strings in given text
    public function sql_escape_string($str)
    {
            //	if($this->isMySql())
            $result_data = addslashes($str);
            //	elseif($this->isPostgres())
            //		$result_data = pg_escape_string($str);
            return $result_data;
    }
    /**
     * Flatten the composite array into single value.
     * Example:
     * $input = array(10, 20, array(30, 40), array('key1' => '50', 'key2'=>array(60), 70));
     * returns array(10, 20, 30, 40, 50, 60, 70);
     */
    function flatten_array($input, $output=null) {
            if(empty($input)) return null;
            $isFlattenNeeded=false;
            foreach($input as $value)
            {
                if(is_array($value))
                {
                    $isFlattenNeeded=true;
                }
            }
            if($isFlattenNeeded===false)
            {
                return $input;
            }
            if($output == null) $output = array();//trace($input,2);
            foreach($input as $value) {
                if(empty($value)) continue;
                    if(is_array($value)) {
                            $output = $this->flatten_array($value, $output);
                    } else {
                            array_push($output, $value);
                    }
            }
            return $output;
    }
    function setDieOnError($value){
		$this->dieOnError = $value;
    }
    public static function &getInstance($isDynamicDatabase=false)
    {
        if($isDynamicDatabase)
        {
            if($db=getDynamicDatabase())
                return self::getNamedInstance($db);
            else
            {
                $ret=false;
                return $ret;
            }
        }
        else
        {
            return self::getNamedInstance(getDefaultDatabase());
        }
    }
    
    /**
    * if $dbname is false, the pdo will be returned without databse
    * if $dbname is "" the database name from config will be used
    * else the $dbname is used and if any instance exist with the name, it will be overwritten
    * To get old stored instance, the $dbname must be ""
    */
    public static function &getModuleInstance($name="default",$dbname="",$dbuser="",$dbpass="", $sqlserver="mysql", $host="localhost")
    {
        return self::getNamedInstance($name,$dbname,$dbuser,$dbpass, $sqlserver, $host);
    }

    /**
    * if $dbname is false, the pdo will be returned without databse
    * if $dbname is "" the database name from config will be used
    * else the $dbname is used and if any instance exist with the name, it will be overwritten
    * To get old stored instance, the $dbname must be ""
    */
    public static function &getNamedInstance($name="default",$dbname="",$dbuser="",$dbpass="", $sqlserver="mysql", $host="localhost")
    {
        static $arrPDOName=array();
        if(!isset($arrPDOName[$name]) && $dbname!=="")
        {
            if(isset(self::$pdo[$dbname]))
            {
                $arrPDOName[$name]=$dbname;
                self::$pdo[$dbname]->name=$name;
                return self::$pdo[$dbname];
            }
        }
        if(isset($arrPDOName[$name]))
        {
            if(isset(self::$pdo[$arrPDOName[$name]]))
            {
                return self::$pdo[$arrPDOName[$name]];
            }
            else
            {
                trace("unexpected PDO instance requested");
            }
        }
        if($dbname==="")
        {
            if($name=="client")
            {
                if(function_exists("getDynamicDatabase") &&  getDynamicDatabase())
                {
                    $dbname=getDynamicDatabase();
                }
                else
                {
                    $dbname=getAppConfig("DATABASE_NAME");
                }
            }
            else
            {
                $dbname=getAppConfig("DATABASE_NAME");
            }
        }
        if(isset(self::$pdo[$dbname]))
        {
                $arrPDOName[$name]=$dbname;
                return self::$pdo[$dbname];
        }
        if($dbuser==="")
        {
            $dbuser=getAppConfig("DATABASE_USER");
        }
        if($dbpass==="")
        {
            $dbpass=  function_exists("getAppConfig")?getAppConfig("DATABASE_PASSWORD"):"";
        }
        
        self::$pdo[$dbname]=new ClsNaanalPDO($host,$dbuser,$dbpass,$dbname,$sqlserver);
        self::$pdo[$dbname]->name=$name;
        $arrPDOName[$name]=$dbname;
        self::$instanceCount++;
        return self::$pdo[$dbname];
    }

    function &getQueryObject($class)
    {
        $stmt=$this->query($this->sql);
        if(empty($this->arrParameter))
        {
            $stmt->setFetchMode(PDO::FETCH_CLASS, $class);
            $arrObject=$stmt->fetchAll();
            return $arrObject;
        }
    }
    function queryAndFetchRow($sql,$param=false)
    {
        if($param===false)
            $obj=$this->query($sql);
        else
            $obj=$this->pquery($sql, $param);
        if($obj===false) return false;
        return $this->fetch_assoc();
    }
    function queryAndFetchRowIfSingle($sql,$param=false)
    {
        if($param===false)
            $obj=$this->query($sql);
        else
            $obj=$this->pquery($sql, $param);
        if($obj===false) return false;
        if($this->rowCount>1) return false;
        return $this->fetch_assoc();
    }
    function queryAndFetchRows($sql,$param=false)
    {
        if($param===false)
            $this->query($sql);
        else
            $this->pquery($sql, $param);
        return $this->getAllRow();
    }
    public function getRecords(ClsAuieoSQL &$objSQL)
    {
        $this->setQuery($objSQL->render());
        return $this->getAllAssoc();
    }
    public function getRecordCount(ClsAuieoSQL &$objSQL)
    {
        $this->setQuery($objSQL->render(true));
        $arrRow=$this->getAllAssoc();
        return $arrRow[0]["count"];
    }
    function setQuery($sql,$arrTmpParam=null)
    {
        if(empty($sql))
        {
            $this->arrError[]=array("message"=>"Unexpected empty sql: {$sql}","errorInfo"=>$this->errorInfo(),"errorCode"=>$this->errorCode(),"statementErrorInfo"=>$this->statement->errorInfo(),"statementErrorCode"=>$this->statement->errorCode(),"sql"=>$sql);
            if($this->stopOnError) trace($this->arrError);
            return false;
        }
        $objParam=$arrTmpParam;
        $this->arrLastSql[]=$sql;
        $this->sql=$sql;
        Logger::getLogger("AuieoATS")->info($sql);
        try
        {
            if(empty($arrTmpParam)) 
            {        
                $this->statement=$this->query($sql);
                if($this->statement!==false) return true;
                else if(empty($this->statement))
                {
                    trace("PDO query returns unexpected empty instead of expected FALSE or statement object");
                }
                else 
                {
                    $this->arrError[]=array("message"=>"Statement Object Empty","errorInfo"=>$this->errorInfo(),"errorCode"=>$this->errorCode(),"sql"=>$sql);
                    if($this->stopOnError) trace($this->arrError);
                    return false;
                }
            }
            else 
            {
                if(is_object($arrTmpParam)) $arrTmpParam=$arrTmpParam->getArray();
                if(!is_array($arrTmpParam)) 
                {
                    $this->error=new PDOException("Array Expected");
                    $this->arrError[]=array("message"=>"Array Expected for \$arrTmpParam","data"=>$arrTmpParam);
                    if($this->stopOnError) trace($this->arrError);
                    return false;
                }
                $matches=array();
                ///getting all the named placeholders
                preg_match_all("/:[a-zA-Z0-9_]*/", $sql, $matches);
                $arrAttribute=$matches[0];
                ///this variable is used to store the necessary object attribute and filtering remaining
                $arrParam=null;
                foreach($arrAttribute as $ind=>$match)
                {
                    $match=trim($match,":");
                    $arrParam[$match]=$arrTmpParam[$match];
                }    
                $this->statement=$this->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                //trace($arrParam);
                $this->statement->execute($arrParam);
                return true;
            }
        }
        catch(PDOException $e)
        {trace($e);
            if(empty($this->statement))
            {
                $this->arrError[]=array("message"=>"Exception Occured for Database:({$this->dbname})","errorInfo"=>$this->errorInfo(),"errorCode"=>$this->errorCode(),"sql"=>$sql, "exception"=>$e);
            }
            else
            {
                $this->arrError[]=array("message"=>"Exception Occured for Database:({$this->dbname})","errorInfo"=>$this->errorInfo(),"errorCode"=>$this->errorCode(),"statementErrorInfo"=>$this->statement->errorInfo(),"statementErrorCode"=>$this->statement->errorCode(),"sql"=>$sql, "exception"=>$e);
            }
            if($this->stopOnError) trace($this->arrError);
            return false;
        }				
    }	
    function &getObject($class=false,$arrParam=null)
    {
        if($class===false)
        {
            if($obj=$this->statement->fetchObject()) 
            {
                return $obj; 
            }
        }
        if(!class_exists($class))
        {
            $this->arrError[]=array("message"=>"Unexpected empty statement");
            if($this->stopOnError) trace($this->arrError);
            return false;
        }
        $obj=null;
        if(is_null($this->statement)) 
        {
            $this->arrError[]=array("message"=>"Unexpected empty statement","errorInfo"=>$this->errorInfo(),"errorCode"=>$this->errorCode());
            if($this->stopOnError) trace($this->arrError);
            $obj=false;
            return $obj;
        }
        try
        {   
            if(is_null($arrParam))
            {
                if($obj=$this->statement->fetchObject($class)) 
                {
                    return $obj; 
                }
            }
            else 
            {
                if(empty($this->statement))
                {
                    $this->arrError[]=array("message"=>"Unexpected empty statement","errorInfo"=>$this->errorInfo(),"errorCode"=>$this->errorCode());
                    if($this->stopOnError) trace($this->arrError);
                    return false;
                }
                $obj=$this->statement->fetchObject($class,$arrParam);
                if(empty($this->statement))
                {
                    $this->arrError[]=array("message"=>"Fetching object throws error and statement object empty for class: {$class} and parameters:".print_r($arrParam,true).". Verify the class {$class} is valid","errorInfo"=>$this->errorInfo(),"errorCode"=>$this->errorCode());
                    if($this->stopOnError) trace($this->arrError);
                    return false;
                }
                return $obj; 
            }
            return $obj;
        }
        catch(PDOException $e)
        {
            $this->arrError[]=array("message"=>"Exception Occured","errorInfo"=>$this->errorInfo(),"errorCode"=>$this->errorCode(),"statementErrorInfo"=>$this->statement->errorInfo(),"statementErrorCode"=>$this->statement->errorCode());
            if($this->stopOnError) trace($this->arrError);
            return false;
        }						
    } 	
    function &getAssoc($query = null)
    {
        try
        {
            if($row=$this->statement->fetch(PDO::FETCH_ASSOC))
            {
                return $row;
            }
            $row=null;
            return $row;
        }
        catch(PDOException $e)
        {
            $this->error=$e;
            $this->arrError[]=array("message"=>"Exception Occured","errorInfo"=>$this->errorInfo(),"errorCode"=>$this->errorCode(),"statementErrorInfo"=>$this->statement->errorInfo(),"statementErrorCode"=>$this->statement->errorCode());
            $row=false;
            if($this->stopOnError) trace($this->arrError);
            return $row;
        }	
    }
    public function getColumnCount()
    {
        return $this->statement->columnCount();
    }
    public function getRowCount()
    {
        return $this->statement->rowCount();
    }
    /**
     * 
     * @param type $colname - data of the column to store in the value
     * @param type $keyField - data of the column to store in the key. if false, 
     * it will be index, if true, it will be the id field or if any field name mentioned,
     * the data of the field name will be used for key of the array
     * @return boolean|null
     */
    function getColumn($colname,$keyField=false)
    {       
        if(empty($this->statement))
        {
            $this->arrError[]=array("message"=>"Statement Object Empty at getColumn method. Param:colname={$colname}, isKeyID={$isKeyID}");
            if($this->stopOnError) trace($this->arrError);
            return false;
        }
        try
        {
            $this->statement->setFetchMode(PDO::FETCH_ASSOC);
            if($arr=$this->statement->fetchALL()) 
            {
                $arrCol=array();
                foreach($arr as $ind=>$r)
                {
                    if($keyField===true)
                    {
                        $keyField="id";
                    }
                    if(!isset($r[$keyField]))
                    {
                        $keyField=false;
                    }
                    if($keyField)
                    {
                        $arrCol[$r[$keyField]]=$r[$colname];
                    }
                    else
                        $arrCol[]=$r[$colname];
                } 
                return $arrCol;
            }
            return null;
        }
        catch(PDOException $e)
        {
            $this->error=$e;
            $this->arrError[]=array("message"=>"Exception Occured","errorInfo"=>$this->errorInfo(),"errorCode"=>$this->errorCode(),"statementErrorInfo"=>$this->statement->errorInfo(),"statementErrorCode"=>$this->statement->errorCode());
            if($this->stopOnError) trace($this->arrError);
            return false;
        }
    }
    public function getError()
    {
        return $this->arrError;
    }
    function &getAllObject($class=false,$arrParam=null)
    {
        if($class===false)
        {
            $arrObj=array();
            while($obj=$this->statement->fetchObject())
            {
                $arrObj[]=$obj;
            }
            return $arrObj;
        }
        if(!class_exists($class))
        {
            $this->arrError[]=array("message"=>"{$class} not exist");
            if($this->stopOnError) trace($this->arrError);
            return false;
        }
        $arrObj=array();
        if(is_null($arrParam))
        {
            $arrObj=$this->statement->fetchAll(PDO::FETCH_CLASS,$class);
            return $arrObj;
        }
        else
        {
            $arrObj=$this->statement->fetchAll(PDO::FETCH_CLASS,$class,$arrParam);
            return $arrObj;
        }
        while($obj=$this->statement->fetchObject($class,$arrParam))
        {
            $arrObj[]=$obj;
        }
        if($this->arrError)
        {
            $arrObj=false;
            if($this->stopOnError) trace($this->arrError);
            return $arrObj;
        }
        return $arrObj;
    }
    function getAllAssoc()
    {       
        if(empty($this->statement))
        {
            $this->arrError[]=array("message"=>"Statement Object Empty","errorInfo"=>$this->errorInfo(),"errorCode"=>$this->errorCode());
            if($this->stopOnError) trace($this->arrError);
            return false;
        }
        try
        {
            $this->statement->setFetchMode(PDO::FETCH_ASSOC);
            if($arr=$this->statement->fetchALL()) 
            {
                return $arr;
            }
            return array();
        }
        catch(PDOException $e)
        {
            $this->error=$e;
            if($this->stopOnError) trace($this->arrError);
            return false;
        }
    }
    function getAllRow()
    {       
        try
        {
            if(empty($this->statement))
            {
                $this->arrError[]="Unexpected statement object empty";
                if($this->stopOnError) trace($this->arrError);
                return false;
            }
            $this->statement->setFetchMode(PDO::FETCH_NUM);
            if($arr=$this->statement->fetchALL()) 
            {
                return $arr;
            }
            return null;
        }
        catch(PDOException $e)
        {
            $this->error=$e;
            if($this->stopOnError) trace($e);
            return false;
        }
    }
    function getAllRowAsTable()
    {       
        try
        {
            $this->statement->setFetchMode(PDO::FETCH_NUM);
            if($arr=$this->statement->fetchALL()) 
            {
                return $arr;
            }
            return null;
        }
        catch(PDOException $e)
        {
            $this->error=$e;
            if($this->stopOnError) trace($this->arrError);
            return false;
        }
    }
    function isTableExist($tablename,$database=null)
    {
        $database=is_null($database)?$this->dbname:$database;
        if(is_null($database)) trace("Database not set");

        $sql="SELECT COUNT(*) as tot FROM information_schema.tables WHERE table_schema = '{$database}' AND table_name = '{$tablename}';";
        $this->setQuery($sql);
        $arrRow = $this->getAllRow();
        if(isset($arrRow[0][0]) && $arrRow[0][0]>0) return true;
        return false;
    }
    function createTable($tablename,$database=null)
    {
        $database=is_null($database)?$this->dbname:$database;
        if(is_null($database)) die("Database not set");
        $sql="CREATE TABLE {$database}.{$tablename} (id INT NOT NULL AUTO_INCREMENT, primary key(id))ENGINE=InnoDB;";
        $this->exec($sql);
    }
    function isFieldExist($tablename,$fieldname,$database=null)
    {
        $database=is_null($database)?$this->dbname:$database;
        if(is_null($database)) die("Database not set");

        $sql="SELECT count(*) AS tot FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='{$database}' AND TABLE_NAME = '{$tablename}' AND COLUMN_NAME = '{$fieldname}'";
        $this->setQuery($sql);
        $arrRow = $this->getAllRow();
        if(isset($arrRow[0][0]) && $arrRow[0][0]>0) return true;
        return false;
    }
    function addField($tablename,$fieldname,$fieldtype,$size=null,$defaultvalue=null,$after=null,$database=null) 
    {
        $fieldtype=trim($fieldtype);
        $fieldtype=  strtolower($fieldtype);
        
        if($fieldtype=="string")
        {
            $fieldtype="VARCHAR";
            if(is_null($size))
            {
                $size=255;
            }
        }
        else if($fieldtype=="number")
        {
            $fieldtype="INT";
            if(is_null($size))
            {
                $size=14;
            }
        }
        else if($fieldtype=="boolean")
        {
            $fieldtype="INT";
            if(is_null($size))
            {
                $size=1;
            }
        }
        else if($fieldtype=="binary")
        {
            $fieldtype="VARBINARY";
            if(is_null($size))
            {
                $size=100;
            }
        }
        else if($fieldtype=="date")
        {
            $fieldtype="INT";
            if(is_null($size))
            {
                $size=14;
            }
        }
        else if($fieldtype=="reference")
        {
            $fieldtype="INT";
            if(is_null($size))
            {
                $size=14;
            }
        }
        if($database)
            $sql="ALTER TABLE `{$database}`.`{$tablename}` ADD `{$fieldname}` {$fieldtype}({$size})";
        else
            $sql="ALTER TABLE `{$tablename}` ADD `{$fieldname}` {$fieldtype}({$size})";
        if(!is_null($after))
        {
            $sql=$sql." ".$after;
        }
        $this->setQuery($sql);
    }
}
class ClsNaanalDB extends ClsNaanalPDO
{
    public function __construct($host,$user,$pass,$dbname,$sqlserver="mysql")
    {
        parent::__construct($host,$user,$pass,$dbname,$sqlserver);
    }
    
}
class ClsNaanalRecords
{
    private $sql="";
    private $arrRecord=array();
    private $arrRecordOriginal=array();
    private $arrNewRecordIndex=array();
    private $arrUpdateRecordIndex=array();
    private $recordCount=-1;
    private $columnCount=-1;
    private $objPDO=null;
    private $arrHead=array();
    private $cellHook=null;
    private $fieldHook=null;
    
    public function __construct($sql,&$objPDO)
    {
        $this->sql=$sql;
        $this->objPDO=$objPDO;
        $this->objPDO->setQuery($sql);
        $this->arrRecord = $this->objPDO->getAllAssoc();
        $this->arrHead=array_keys($this->arrRecord[0]);
        $this->arrRecordOriginal=$this->arrRecord;
        $this->recordCount=count($this->arrRecord);
        $this->columnCount=count($this->arrHead);
    }
    public static function &getInstance($sql,&$objPDO)
    {
        $obj=new ClsNaanalRecords($sql,$objPDO);
        return $obj;
    }
    public function setHook($name,$loopCell)
    {
        $this->$name=$loopCell;
    }
    public function findRecordIndex($arrUniqueRecordData)
    {
        if($this->arrRecord)
        foreach($this->arrRecord as $ind=>$row)
        {
            $isMatch=true;
            foreach($this->arrUniqueRecordField as $field)
            {
                $fieldIndex=array_search($field, $this->arrField);
                if($row[$fieldIndex]!=$arrUniqueRecordData[$field])
                {
                    $isMatch=false;
                    break;
                }
            }
            if($isMatch) return $ind;
        }
        return -1;
    }
    
    public function setData($arrUniqueRecordData,$columnField,$data)
    {
        $index=$this->findRecordIndex($arrUniqueRecordData);
        if($index>-1)
        {
            $this->arrRecord[$index][$columnField]=$data;
            if(!in_array($needle, $this->arrUpdateRecordIndex))
            {
                $this->arrUpdateRecordIndex[]=$index;
            }
            return true;
        }
        return false;
    }
    
    public function insertEmptyRecord()
    {
        $this->arrRecord[$this->recordCount]=$this->emptyRecord;
        $this->arrNewRecordIndex[]=$this->recordCount;
        return $this->recordCount;
    }
    
    public function addRecord($arrField)
    {
        $index=$this->insertEmptyRecord();
        $this->recordCount=$this->recordCount+1;
        foreach($this->arrRecord[$index] as $fieldIndex=>$data)
        {
            $this->arrRecord[$index][$fieldIndex]=$arrField[$this->arrField[$fieldIndex]];
        }
        return true;
    }
    
    public function modifyRecord($arrField)
    {
        $arrUniqueRecordData=array();
        foreach($this->arrUniqueRecordField as $field)
        {
            $arrUniqueRecordData[$field]=$arrField[$field];
        }
        $index=$this->findRecordIndex($arrUniqueRecordData);
        if($index>-1)
        {
            foreach($this->arrRecord[$index] as $fieldIndex=>$data)
            {
                $this->arrRecord[$index][$fieldIndex]=$arrField[$this->arrField[$fieldIndex]];
            }
            $this->arrUpdateRecordIndex[]=$index;
            return true;
        }
        return false;
    }
    
    public function updateRecord($arrField)
    {
        if(!$this->modifyRecord($arrField))
        {
            if(!$this->addRecord($arrField))
            {
                return false;
            }
        }
        return true;
    }
    
    public function setDataByIndex($index,$field,$data)
    {
        $this->arrRecord[$index][$field]=$data;
        if(!in_array($index, $this->arrNewRecordIndex))
        {
            if(!in_array($needle, $this->arrUpdateRecordIndex))
            {
                $this->arrUpdateRecordIndex[]=$index;
            }
        }
    }
    
    public function getDataByIndex($index,$field)
    {
        if(isset($this->arrRecord[$index][$field]))
        {
            return $this->arrRecord[$index][$field];
        }
        return null;
    }
    /**
     * 
     * @param type $row integer
     * @param type $col integer or string
     * @return data or null
     */
    public function getData($row,$col)
    {
        $head=$col;
        if(is_numeric($col))
        {
            $head=$this->arrHead[$col];
        }
        if(isset($this->arrRecord[$row][$head]))
        {
            if(is_null($this->cellHook))
            {
                return $this->arrRecord[$row][$head];
            }
            else
            {
                $lamda=$this->cellHook;
                $data=$lamda($head,$this->arrRecord[$row][$head]);
                return $data;
            }
        }
        return null;
    }
    
    public function getHead($col)
    {
        if(is_null($this->fieldHook))
        {
            return $this->arrHead[$col];
        }
        else
        {
            $lamda=$this->fieldHook;
            $data=$lamda($this->arrHead[$col]);
            return $data;
        }
    }
    
    public function getRecordCount()
    {
        return $this->recordCount;
    }
    
    public function getColumnCount()
    {
        return $this->columnCount;
    }
}
class ClsAuieoResultset
{
	/** PDO resultset to wrap */
	private $_st;
	/** One-time resultset information */
	private $results;
	private $rowcount;
	private $cursor=-1;
	/** Publically accessible row values */
	public $fields;
	/** Public end-of-resultset flag */
	public $EOF;
	/** * Constructor: Initialise resultset and first results * @param st PDOStatement object to wrap */
	public function __construct(&$st)
	{
		$this->_st = $st;
		$this->results = $st->fetchAll();
		$this->rowcount = 0;
		if(!empty($this->results))
		{
			$this->rowcount = count($this->results);
			$this->cursor = 0;
			$this->fields = $this->results[$this->cursor];
		}
		else
		{
			$this->EOF=1;
		}
		//$this->MoveNext();
	}

	public function __toString() {
		return "$this->rowcount";
	}
        public function __get($name) {
            return $this->_st->$name;
        }
        public function __call($name, $arguments) {
            if(empty($arguments))
            {
                return $this->_st->$name();
            }
            else
            {
                return $this->_st->$name($arguments);
            }
        }
        public function free()
        {
            
        }
	public function GetRowAssoc()
	{
		if(empty($this->results))
		{
			return null;
		}
		if($this->cursor<0)
		{
			$this->cursor=0;
		}
		return $this->results[$this->cursor];
	}
	/** * RecordCount: Retrieve number of records in this RS * @return Integer number of records */
	public function RecordCount()
	{
		return $this->rowcount;
	}
        public function NumRows()
        {
            return $this->RecordCount();
        }
	/** * MoveNext: Fetch next row and check if we're at the end */
	public function MoveNext()
	{
		if($this->EOF) return false;
		if(empty($this->results))
		{
			return false;
		}
		if($this->cursor<0)
		{
			$this->cursor=0;
		}
		$this->fields = $this->results[$this->cursor++];
		$this->EOF = ($this->cursor == $this->rowcount) ? 1 : 0;
	}

	public function Move($row)
	{
		if(!empty($this->results))
		{
			if($row>=$this->rowcount)$row=$this->rowcount-1;
			$this->cursor=$row;
			return true;
		}
		else
			return false;
	}
	public function FetchRow()
	{
		if(empty($this->results))
		{
			return null;
		}
		if($this->cursor<0)
		{
			$this->cursor=0;
		}
		if(!isset($this->results[$this->cursor])) return null;
		$this->fields = $this->results[$this->cursor++];
		$this->EOF = ($this->cursor == $this->rowcount) ? 1 : 0;
		return $this->fields;
	}
	public function FieldCount()
	{
		return $this->_st->columnCount();
	}
	public function FetchField($pos)
	{
		$arrMeta = $this->_st->getColumnMeta($pos);
		$obj=new ADODB_PDO_FieldData($arrMeta);
		return $obj;
	}
	public function &GetRows()
	{
		$arrRow=array();
		while($row=$this->FetchRow())
		{
			$arrRow[]=$row;
		}
		return $arrRow;
	}
}
?>