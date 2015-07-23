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

/**
 *	Database Connector / Database Abstraction Layer
 *	@package    CandidATS
 *	@subpackage Library
 */
class DatabaseConnection
{
    static private $_instance;
    private $_connection = null;
    private $_queryResult = null;
    private $_timeZone;
    private $_dateDMY;
    private $_inTransaction;


    /**
     * Returns an instance of DatabaseConnection.
     *
     * @return DatabaseConnection Instance of DatabaseConnection.
     */
    public static function getInstance()
    {
        if (self::$_instance == null)
        {
            self::$_instance = new DatabaseConnection();
            self::$_instance->connect();
            self::$_instance->setInTransaction(false);
        }

        // FIXME: Remove Session tight-coupling here.
        if (isset($_SESSION['CATS']) && $_SESSION['CATS']->isLoggedIn())
        {
            self::$_instance->_timeZone = $_SESSION['CATS']->getTimeZoneOffset();
            self::$_instance->_dateDMY = $_SESSION['CATS']->isDateDMY();
        }
        else
        {
            self::$_instance->_timeZone = OFFSET_GMT * -1;
            self::$_instance->_dateDMY = false;
        }

        return self::$_instance;
    }


    /* Prevent this class from being instantiated by any means other
     * than getInstance().
     */
    private function __construct() {}
    private function __clone() {}

    public function setInTransaction($tf)
    {
        return ($this->_inTransaction = $tf);
    }


    /**
     * Returns this instance's connection resource, or null if nonexistant.
     *
     * @return resource This instance's connection resource, or null if
     *                  nonexistant.
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * Initiate a connection with the MySQL database. This is called by the
     * constructor.
     *
     * @param string MySQL query or null to operate on the last executed query
     *               for this instance.
     * @return boolean Was the connection successful?
     */
    public function connect()
    {
        $this->_connection=ClsNaanalPDO::getNamedInstance("cats",DATABASE_NAME,DATABASE_USER,DATABASE_PASS,"mysql",DATABASE_HOST);
        return true;
    }
    
    function isFieldExist($tablename,$fieldname,$database=null)
    {
        return $this->_connection->isFieldExist($tablename,$fieldname,$database);
    }
    
    function isTableExist($tablename,$database=null)
    {
        return $this->_connection->isTableExist($tablename,$database);
    }

    function addField($tablename,$fieldname,$fieldtype,$size=null,$defaultvalue=null,$after=null,$database=null) 
    {
        return $this->_connection->addField($tablename,$fieldname,$fieldtype,$size,$defaultvalue,$after,$database);
    }
    
    function createTable($tablename,$database=null)
    {
        return $this->_connection->createTable($tablename,$database);
    }

    /**
     * Executes a query against the current connection. Unless
     * $ignoreErrors is true, any failed queies will result in a die().
     *
     * @param string MySQL query or null to operate on the last executed query
     *               for this instance.
     * @return resource MySQL query result. For non-SELECT queries, this will
     *                  return a boolean value indicating whether or not the
     *                  query's execution was successful. SELECT queries can
     *                  also return false indicating a permission error or
     *                  other failure.
     */
    public function query($query, $ignoreErrors = false)
    {
        /* Does our current configuration allow the execution of this query? */
        if (!$this->allowQuery($query))
        {
            return false;
        }

        /* Fix formatted dates and time zones for localization. */
        // FIXME: I don't like rewriting queries....
        $query = $this->_localizationFilter($query);

        /* Don't limit the execution time of queries. */
        set_time_limit(0);

        $this->_queryResult = $this->_connection->setQuery($query);
        if (!$this->_queryResult && !$ignoreErrors)
        {
            $error = $this->_connection->getError();

            echo (
                '<!-- NOSPACEFILTER --><p style="background: #ec3737; padding:'
                . ' 4px; margin-top: 0; font: normal normal bold 12px/130%'
                . ' Arial, Tahoma, sans-serif;">Query Error -- Report to System'
                . " Administrator ASAP</p><pre>\n\nMySQL Query Failed: "
                . $error . "\n\n" . $query . "</pre>\n\n"
            );

            echo('<!--');

            trigger_error(
                str_replace("\n", " ", 'MySQL Query Error: ' . $error . " - " . $query)
            );

            echo('-->');

            die();
        }

        return $this->_queryResult;
    }
    
    public function getAllRow($query=null)
    {
        if (!is_null($query))
        {
            $this->query($query);
        }
        return $this->_connection->getAllRow();
    }

    /**
     * Executes multiple queries from a string. Each query in the specified
     * string must be terminated with a semicolon (;).
     *
     * @param string MySQL query or null to operate on the last executed query
     *               for this instance.
     * @param string Delimiter to use to split the SQL commands (usually ';')
     * @return void
     */
    public function queryMultiple($string, $delimiter = ';')
    {
        $SQLStatments = explode($delimiter, str_replace("\r\n", "\n", $string));

        foreach ($SQLStatments as $SQL)
        {
            $SQL = trim($SQL);

            if (empty($SQL))
            {
                continue;
            }

            $this->query($SQL);
        }
    }

    /**
     * Returns a single field from a result set, based on the field's row and
     * column number. If a query is not specified, this method will operate on the
     * last executed query for this instance.
     *
     * @param string MySQL query or null to operate on the last executed query
     *               for this instance.
     * @param integer Row number.
     * @param integer Column number.
     * @return array Multi-dimensional associative result set array, or array()
     */
    public function getColumn($query = null, $row, $column)
    {
        if ($query != null)
        {
            $arrRow=array();
            $this->query($query);
        }
        
        //$numRows = mysql_num_rows($this->_queryResult);
        $numRows = $this->_connection->getRowCount();
        if ($numRows === false)
        {
            return false;
        }
        else if ($row >= $numRows)
        {
            return false;
        }
        else if ($row < 0)
        {
            return false;
        }
        $arrRow=$this->_connection->getAllRow();
        return $arrRow[$row][$column];
        //return mysql_result($this->_queryResult, $row, $column);
    }
    
    public function getAllColumn($columnNum=0)
    {
        $arrColumn=array();
        $arrAssoc=$this->_connection->getAllRow();
        if($arrAssoc)
        {
            foreach($arrAssoc as $row)
            {
                $arrColumn[]=$row[$columnNum];
            }
        }
        return $arrColumn;
    }

    public function getColumnsMeta($query=null)
    {
        if ($query != null)
        {
            $pdo_stmt=$this->_connection->query($query." limit 0,1");
            foreach(range(0, $pdo_stmt->columnCount() - 1) as $column_index)
            {
              $meta[] = $pdo_stmt->getColumnMeta($column_index);
            }
        }
        return $meta;
    }
    
    public function getTablesMeta()
    {
        
    }
    
    public function getAssoc($query = null)
    {
        if ($query != null)
        {
            $this->query($query);
        }

        $recordSet = $this->_connection->getAssoc();

        if (empty($recordSet))
        {
            $recordSet = array();
        }

        return $recordSet;
    }

    /**
     * Returns all rows from a query's result set in a multi-dimensional
     * associative array. If a query is not specified, this method will operate
     * on the last executed query for this instance.
     *
     * Example:
     * array(
     *    0 => array(
     *        'firstName'   => 'Will',
     *        'lastName'    => 'Buckner',
     *        'dateCreated' => '05/05/07 4:32 PM'
     *    ),
     *    1 => array(
     *        'firstName'   => 'Asim',
     *        'lastName'    => 'Baig',
     *        'dateCreated' => '05/06/07 3:30 PM'
     *    ),
     *    ...
     * );
     *
     * @param string MySQL query or null to operate on the last executed query
     *               for this instance.
     * @return array Multi-dimensional associative result set array, or array()
     *               if no records were returned.
     */
    public function getAllAssoc($query = null)
    {
        if (!is_null($query))
        {
            $this->query($query);
        }

        /* Make sure we always return an array. */
        $recordSetArray = $this->_connection->getAllAssoc();

        /* Store all rows in $recordSetArray; */
        /*while (($recordSet = mysql_fetch_assoc($this->_queryResult)))
        {
            $recordSetArray[] = $recordSet;
        }*/

        /* Return the multi-dimensional record set array. */
        return $recordSetArray;
    }

    /**
     * Returns the number of rows in a query's result set (regardless of where
     * the current row pointer is).
     *
     * @return integer Total rows in a query's result set.
     */
    public function getNumRows($query = null)
    {
        if ($query != null)
        {
            $this->query($query);
        }

        return $this->_connection->getRowCount();
    }

    /**
     * Returns true if there are no (more) records in the result set for the
     * last query.
     *
     * @return boolean Are we at the end of the MySQL result set?
     */
    public function isEOF()
    {
        //$rowCount = mysql_num_rows($this->_queryResult);
        $rowCount = $this->_connection->getRowCount();
        if (!$rowCount)
        {
            return true;
        }

        return false;
    }

    /**
     * Creates a blocking advisory lock with the specified name. Subsequent
     * calls to this method will block until the previous lock with the same
     * name has been released. THIS DOES NOT ACTUALLY PREVENT READS OR WRITES
     * TO THE DATABASE! This currently only works with MySQL.
     *
     * @param string Name to assign to the lock.
     * @param integer Lock timeout.
     * @return void
     */
    public function getAdvisoryLock($lockName, $timeout = 120)
    {
        $sql = sprintf(
            "SELECT
                GET_LOCK(%s, %s)",
            $this->makeQueryString($lockName),
            $this->makeQueryInteger($timeout)
        );
        $this->query($sql);
    }


    /**
     * Returns true if the blocking advisory lock is free.
     *
     * @param string Name assigned to the lock.
     * @return boolean Has the lock been freed?
     */
    public function isAdvisoryLockFree($lockName)
    {
        $sql = sprintf(
            "SELECT
                IS_FREE_LOCK(%s) AS isFreeLock",
            $this->makeQueryString($lockName)
        );
        $rs = $this->getAssoc($sql);

        if ($rs['isFreeLock'] == 1)
        {
            return true;
        }

        return false;
    }

    /**
     * Releases a blocking advisory lock with the specified name (created with
     * $this->getAdvisoryLock(). This currently only works with MySQL.
     *
     * @param string Name of lock to be released.
     * @return void
     */
    public function releaseAdvisoryLock($lockName)
    {
        $sql = sprintf(
            "SELECT
                RELEASE_LOCK(%s)",
            $this->makeQueryString($lockName)
        );
        $this->query($sql);
    }
    
    function mysql_escape_eq($inp) 
    {
        if(is_array($inp))
            return array_map(__METHOD__, $inp);

        if(!empty($inp) && is_string($inp)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
        }

        return $inp;
    } 

    /**
     * Returns the original string escaped for query use.
     *
     * @param string String to process.
     * @return string Original string, escaped for query use.
     */
    public function escapeString($string)
    {
        return $this->mysql_escape_eq($string);// mysql_real_escape_string($string, $this->_connection);
    }

    /**
     * Returns the original string quoted / escaped for query use.
     *
     * @param string String to process.
     * @return string Original string, escaped / quoted for query use.
     */
    public function makeQueryString($string)
    {
        return "'" . $this->escapeString($string) . "'";
    }

    /**
     * Returns 'NULL' if $string is empty; otherwise, the original string
     * quoted / escaped for query use.
     *
     * @param string String to process.
     * @return string Original string, escaped / quoted for query use, or NULL
     *               for an empty string.
     */
    public function makeQueryStringOrNULL($string)
    {
        $string = trim($string);

        if (empty($string))
        {
            return 'NULL';
        }

        return $this->makeQueryString($string);
    }

    /**
     * Returns 'NULL' if the specified value is equal to -1; otherwise the
     * original value as an integer safe for MySQL. This follows PHP5's integer
     * casting rules. Doubles will be rounded using truncation (1.9999 => 1).
     *
     * @param mixed Value to process.
     * @return integer Value converted to an integer, or 'NULL'.
     */
    public function makeQueryIntegerOrNULL($value)
    {
        if ($value == '-1')
        {
            return 'NULL';
        }

        return (integer) $value;
    }

    /**
     * Returns the original value as an integer safe for MySQL. This follows
     * PHP5's integer casting rules. Doubles will be rounded using truncation
     * (1.9999 => 1).
     *
     * @param mixed Value to process.
     * @return integer Value converted to an integer.
     */
    public function makeQueryInteger($value)
    {
        return (integer) $value;
    }

    /**
     * Returns the original value as a safe MySQL double, rounded to the
     * specified precision. 0.00 is returned for bad values.
     *
     * @param string Double / string value to process.
     * @return string Safe MySQL double, rounded to the specified precision.
     */
    public function makeQueryDouble($value, $precision = false)
    {
        $value = trim($value);

        if (empty($value) || !preg_match('/^-?[0-9]+(?:\.[0-9]+)?$/', $value))
        {
            return '0.0';
        }

        if ($precision !== false)
        {
            return (string) number_format(round($value, $precision),$precision,".","");
        }

        return (string) $value;
    }

    /**
     * Returns the last error message (value of mysql_error()) for the current
     * MySQL connection.
     *
     * @return string Error message, or '' if no error occurred.
     */
    public function getError()
    {
        return $this->_connection->getError();
    }

    /**
     * Returns the last insert's AUTO_INCREMENT key's value for the current
     * database connection connection.
     *
     * @return integer ID generated for an AUTO_INCREMENT column by the
     *         previous INSERT query on success, 0 if the previous query does
     *         not generate an AUTO_INCREMENT value, or false if no database
     *         connection was established.
     */
    public function getLastInsertID()
    {
        return $this->_connection->lastinsertid();
    }

    /**
     * Returns the number of rows in the database that were affected by the
     * last query (INSERT / UPDATE / DELETE / etc.).
     *
     * @return integer Number of affected rows by the last executed MySQL
     *                 operation (INSERT / UPDATE / DELETE / etc.).
     */
    public function getAffectedRows()
    {
        return $this->_connection->getRowCount();
    }

    /**
     * Returns the current RDBMS version, as reported by the RDBMS.
     * The string 'MySQL ' is prepended for MySQL.
     *
     * @return string RDBMS version.
     */
    public function getRDBMSVersion()
    {
        $rs = $this->getAssoc('SELECT VERSION() AS version');
        return 'MySQL ' . $rs['version'];
    }

    /**
     * Returns true if the specified query is allowed by the filter. Currently
     * this is only used to prevent database writes when CATS_SLAVE is enabled.
     *
     * @param string Query to check.
     * @return boolean Is this query allowed by the current configuration?
     */
    public function allowQuery($query)
    {
        if (CATS_SLAVE &&
            preg_match('/^\s*(?:UPDATE|INSERT|DELETE)\s/i', trim($query)))
        {
            return false;
        }

        return true;
    }


    // FIXME: Document me.
    private function _localizationFilter($query)
    {
        /* Fix query to allow time results to be offset by $_timeZone. */
        if (strpos($query , 'SELECT') !== 0)
        {
            return $query;
        }

        // FIXME: This could probably be done better with regexes.
        // FIXME: D M Y support.
        // FIXME: Document this. Any string-manipulation things like this can
        //        get fairly confusing if not documented.
        $newQuery = '';
        while ($query != '')
        {
            /* Does the query contain a DATE_FORMAT()? */
            $dateFormatPosition = strpos($query, 'DATE_FORMAT(');
            if ($dateFormatPosition === false)
            {
                $newQuery .= $query;
                $query = '';
                continue;
            }

            if ($dateFormatPosition > 0)
            {
                $newQuery .= substr($query, 0, strpos($query, 'DATE_FORMAT('));
                $query = substr($query, strpos($query, 'DATE_FORMAT('));
            }

            $working = substr($query, 0, strpos($query, ','));
            $query = substr($query, strpos($query, ','));
            if (strpos(substr($working, 13), '(') === false)
            {
                /* Add or subtract time before the date format depeidng on the
                 * time zone offset. We don't have to do any replacement if the
                 * offset is 0.
                 */
                if ($this->_timeZone > 0)
                {
                    $working = str_replace('DATE_FORMAT(', 'DATE_FORMAT(DATE_ADD(', $working);
                    $working .= ', INTERVAL ' . $this->_timeZone . ' HOUR)';
                }
                else if ($this->_timeZone < 0)
                {
                    $working = str_replace('DATE_FORMAT(', 'DATE_FORMAT(DATE_SUB(', $working);
                    $working .= ', INTERVAL ' . ($this->_timeZone * -1) . ' HOUR)';
                }
            }
            $newQuery .= $working;
        }

        $query = $newQuery;

        /* Replace m-d-y dates with d-m-y dates if we're in dmy mode. */
        if ($this->_dateDMY)
        {
            $query = str_replace('%m-%d-%y', '%d-%m-%y', $query);
            $query = str_replace('%m-%d-%Y', '%d-%m-%Y', $query);
            $query = str_replace('%m/%d/%Y', '%d/%m/%Y', $query);
            $query = str_replace('%m/%d/%y', '%d/%m/%y', $query);
        }

        return $query;
    }

    /**
     * Transaction functions for InnoDB tables.
     */

    public function beginTransaction()
    {
        if (!$this->_inTransaction)
        {
            // Ignore errors (if called for MyISAM, for example)
            $this->query('BEGIN', true);
            return ($this->_inTransaction = true);
        }
        else
        {
            // Already in a transaction
            return false;
        }
    }

    public function commitTransaction()
    {
        if ($this->_inTransaction)
        {
            $this->query('COMMIT', true);
            $this->_inTransaction = false;
            return true;
        }
        else
        {
            // We're not in a transaction
            return false;
        }
    }

    public function rollbackTransaction()
    {
        if ($this->_inTransaction)
        {
            $this->query('ROLLBACK', true);
            $this->_inTransaction = false;
            return true;
        }
        else
        {
            // We're not in a transaction
            return false;
        }
    }
}

?>