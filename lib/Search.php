<?php
/**
 * CATS
 * Search Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: Search.php 3587 2007-11-13 03:55:57Z will $
 */

include_once('./lib/Pager.php');
include_once('./lib/DatabaseSearch.php');
include_once('./lib/SearchBase.php');

if (ENABLE_SPHINX)
{
    include_once(SPHINX_API);
}

/**
 *	Search Utility Library
 *	@package    CATS
 *	@subpackage Library
 */
class SearchUtility
{
    /**
     * Returns an excerpt of text based on incidence of keys.
     *
     * @param keys string wildcard terms
     * @param text string result text
     * @return string excerpt
     */
    public static function searchExcerpt($keywords, $text)
    {
        /* CATS fulltext encode the search string. */
        $keywords = DatabaseSearch::fulltextEncode($keywords);

        /* Create an array of keywords to highlight. */
        $keywords = self::makeKeywordsArray($keywords);

        /* Make a copy of the keywords array for manupulating below. */
        $workingKeys = $keywords;

        /* Extract a fragment per keyword, for at most 4 keywords.
         * First we collect ranges of text around each keyword, starting/ending
         * at spaces. If the sum of all fragments is too short, we look for
         * second occurrences.
         */
        $ranges = array();
        $included = array();
        $length = 0;
        while ($length < SEARCH_EXCERPT_LENGTH && count($workingKeys))
        {
            foreach ($workingKeys as $keyOffset => $key)
            {
                if ($length >= SEARCH_EXCERPT_LENGTH)
                {
                    break;
                }

                /* Escape the key for use with preg_*(). */
                $key = preg_quote($key, '/');

                /* Remember occurrence of key so we can skip over it if more occurrnces
                 * are desired.
                 */
                if (!isset($included[$key]))
                {
                    $included[$key] = 0;
                }

                $regExPass = false;

                /* Check for wildcards */
                if (strpos($key, '*') !== false)
                {
                    $newKey = str_replace('\*', '', $key);
                    $regExPass = preg_match(
                        '/' . $newKey . '/i', $text, $matches,
                        PREG_OFFSET_CAPTURE, $included[$key]
                    );
                }
                else
                {
                    $regExPass = preg_match(
                        '/\b' . $key . '\b/i', $text, $matches,
                        PREG_OFFSET_CAPTURE, $included[$key]
                    );
                }

                if ($regExPass)
                {
                    $firstMatchOffset = $matches[0][1];

                    $firstSpaceInRange = strpos($text, ' ', max(0, $firstMatchOffset - 60));
                    if ($firstSpaceInRange !== false)
                    {
                        $end = substr($text, $firstMatchOffset, 80);
                        $lastSpaceInRange = strrpos($end, ' ');

                        if ($lastSpaceInRange !== false)
                        {
                            $ranges[$firstSpaceInRange] = $firstMatchOffset + $lastSpaceInRange;
                            $length += $firstMatchOffset + $lastSpaceInRange - $firstSpaceInRange;
                            $included[$key] = $firstMatchOffset + 1;
                        }
                        else
                        {
                            unset($workingKeys[$keyOffset]);
                        }
                    }
                    else
                    {
                        unset($workingKeys[$keyOffset]);
                    }
                }
                else
                {
                    unset($workingKeys[$keyOffset]);
                }
            }
        }

        /* If we didn't find anything, return the beginning of the text up to
         * SEARCH_EXCERPT_LENGTH.
         */
        if (sizeof($ranges) == 0)
        {

            $text = DatabaseSearch::fulltextDecode($text);
            return substr($text, 0, SEARCH_EXCERPT_LENGTH);
        }

        /* Sort the text ranges by starting position. */
        ksort($ranges);

        /* For each range, in the $ranges array, compare to every other range
         * and test for overlapping ranges. Merge overlapping ranges togeather.
         * The ksort()ing makes this O(n).
         */
        $newRanges = array();
        foreach ($ranges as $rangeFrom => $rangeTo)
        {
            /* On the first loop, set the 'base range' to the first range's
             * limits and continue on to the next loop.
             */
            if (!isset($baseRangeFrom))
            {
                $baseRangeFrom = $rangeFrom;
                $baseRangeTo = $rangeTo;

                continue;
            }

            /* If the start of the current range is before the end of the
             * previous range, make the 'base range' include the new range as
             * well. Otherwise, start the 'base range' over at the limits for
             * the current range.
             */
            if ($rangeFrom <= $baseRangeTo)
            {
                $baseRangeTo = max($baseRangeTo, $rangeTo);
            }
            else
            {
                /* Every time we start the 'base range' over, store the
                 * previous combined range that we just calculated in the
                 * 'new ranges' array.
                 */
                $newRanges[$baseRangeFrom] = $baseRangeTo;

                $baseRangeFrom = $rangeFrom;
                $baseRangeTo = $rangeTo;
            }
        }

        /* Store the last combined range that we just calculated in the 'new
         * ranges' array.
         */
        $newRanges[$baseRangeFrom] = $baseRangeTo;

        /* Fetch text. */
        $out = array();
        foreach ($newRanges as $from => $to)
        {
            $out[] = substr($text, $from, $to - $from);
        }

        $text = implode(' ... ', $out);

        /* Highlight wildcards differently. */
        $keywordsWild = array();
        foreach ($keywords as $keyOffset => $key)
        {
            if (strpos($key, '*') !== false)
            {
                $keywordsWild[] = str_replace('*', '', $key);
                unset($keywords[$keyOffset]);
            }
        }
        $keywords = array_merge($keywords);

        if (!empty($keywordsWild))
        {
            $regex = implode('|', array_map(
                create_function('$string','return preg_quote($string, \'/\');'), $keywordsWild
            ));
            $text = preg_replace(
                '/(' . $regex . ')/i',
                '<span style="background-color: #ffff99">\1</span>',
                $text
           );
        }

        if (!empty($keywords))
        {
            $regex = implode('|', array_map(
                create_function('$string','return preg_quote($string, \'/\');'), $keywords
            ));
            $text = preg_replace(
                '/\b(' . $regex . ')\b/i',
                '<span style="background-color: #ffff99">\1</span>',
                $text
            );
        }

        if (isset($newRanges[0]))
        {
            $text = $text . ' ...';
        }
        else
        {
            $text = '... ' . $text . ' ...';
        }


        /* Remove AntiWord 'table bars' */
        $text = str_replace('|', '', $text);

        return DatabaseSearch::fulltextDecode($text);
    }

    /**
     * Highlights keywords in text for a resume preview and preforms CATS
     * fulltext decoding.
     *
     * @param array keywords to highlight
     * @param string resume text
     * @return string highlighted preview text
     */
    public static function makePreview($keywords, $text)
    {
        if (empty($keywords))
        {
            return DatabaseSearch::fulltextDecode($text);
        }

        /* CATS fulltext encode the search string. */
        $keywords = DatabaseSearch::fulltextEncode($keywords);

        /* Create an array of keywords to highlight. */
        $keywords = self::makeKeywordsArray($keywords);

        /* Highlight wildcards differently. */
        $keywordsWild = array();
        foreach ($keywords as $keyOffset => $key)
        {
            if (strpos($key, '*') !== false)
            {
                $keywordsWild[] = str_replace('*', '', $key);
                unset($keywords[$keyOffset]);
            }
        }
        $keywords = array_merge($keywords);

        if (!empty($keywordsWild))
        {
            $regex = implode('|', array_map(
                create_function('$string','return preg_quote($string, \'/\');'), $keywordsWild
            ));
            $text = preg_replace(
                '/(' . $regex . ')/i',
                '<span style="background-color: #ffff99">\1</span>',
                $text
           );
        }

        if (!empty($keywords))
        {
            $regex = implode('|', array_map(
                create_function('$string','return preg_quote($string, \'/\');'), $keywords
            ));
            $text = preg_replace(
                '/\b(' . $regex . ')\b/i',
                '<span style="background-color: #ffff99">\1</span>',
                $text
            );
        }

        return DatabaseSearch::fulltextDecode($text);
    }

    // FIXME: Document me.
    private static function makeKeywordsArray($string)
    {
        /* Mark up quoted strings with filler characters (no white space). */
        $string = DatabaseSearch::markUpQuotes($string);

        /* Split keywords into an array by "words" and fix quotes. */
        $keywords = explode(' ', $string);
        $keywords = array_map(
            array('DatabaseSearch', 'unMarkUpQuotes'), $keywords
        );

        /* Escape special regex characters in keys, and filter out boolean words. */
        foreach ($keywords as $index => $keyword)
        {
            $keywords[$index] = str_replace(
                array('(', ')'), '', $keywords[$index]
            );

            if (strtoupper($keyword) == 'AND' ||
                strtoupper($keyword) == 'OR' ||
                strtoupper($keyword) == 'NOT')
            {
                unset($keywords[$index]);
                continue;
            }
        }

        return array_merge($keywords);
    }
}


/**
 *	Candidates Search Library
 *	@package    CATS
 *	@subpackage Library
 */
class SearchCandidates extends SearchBase
{
    private $_db;
    private $_siteID;
    protected $_userID = -1;


    public function __construct($siteID)
    {
        parent::__construct("candidate");
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        //FIXME: Library code Session dependencies suck.
        $this->_userID = $_SESSION['CATS']->getUserID();
    }
    
    /**
     * Returns all candidates criterial matching $matchingCriteria.
     *
     * @param string wildcard match string
     * @return array candidates data
     */
    public function byJoborderMatching($matchingCriteria, $sortBy="lastName", $sortDirection="ASC")
    {
        $objSearchBase=new SearchBase("candidate");
        $matchingExtraCriteria=array();
        $matchingRegularCriteria=array();
        $matchingExtraCriteriaData=array();
        $matchingRegularCriteriaData=array();
        $where="";
        /**
         * split extra field and regular field
         * and build where condition
         */
        foreach($matchingCriteria as $field=>$data)
        {
            $realField=getRealFieldName(100,$field);
            if(is_numeric($realField))
            {
                $matchingExtraCriteria[]=$field;
                $matchingExtraCriteriaData[$field]=$data;
            }
            else
            {
                $matchingRegularCriteria[]=$field;
                $matchingRegularCriteriaData[$field]=$data;
            }
        }
        $arrExtra=$objSearchBase->getExtraFieldWhere($matchingExtraCriteria);
        $arrTable=$arrExtra["table"];
        foreach($matchingExtraCriteriaData as $fieldName=>$data)
        {
            $table=$arrTable[$fieldName];
            $data=trim($data);
            if($data[0]=="<" || $data[0]==">" || $data[0]=="!" || $data[0]=="=") 
            {
                if(empty($where))
                {
                    $where="`{$table}`.`{$fieldName}` {$data}";
                }
                else
                {
                    $where="{$where} AND `{$table}`.`{$fieldName}` {$data}";
                }
            }
            else if($data[0]=="%")
            {
                if(empty($where))
                {
                    $where="`{$table}`.`{$fieldName}` like '{$data}'";
                }
                else
                {
                    $where="{$where} AND `{$table}`.`{$fieldName}` like '{$data}'";
                }
            }
            else
            {
                if(empty($where))
                {
                    $where="`{$table}`.`{$fieldName}` = '{$data}'";
                }
                else
                {
                    $where="{$where} AND `{$table}`.`{$fieldName}` = '{$data}'";
                }
            }
        }
        foreach($matchingRegularCriteriaData as $fieldName=>$data)
        {
            //$table=$arrTable[$fieldName];
            $data=trim($data);
            
            if(empty($where))
            {
                $where="`candidate`.`{$fieldName}` = '{$data}'";
            }
            else
            {
                $where="{$where} AND `candidate`.`{$fieldName}` = '{$data}'";
            }
        }
        if(!empty($where)) $where="AND {$where}";
        $customSqlColum=implode(",", $arrExtra["column"]);
        $customSqlJoin=implode(" ", $arrExtra["join"]);
        
        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.city AS city,
                candidate.state AS state,
                candidate.phone_home AS phoneHome,
                candidate.phone_cell AS phoneCell,
                candidate.key_skills AS keySkills,
                candidate.email1 AS email1,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
                %s
            FROM
                candidate
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
                %s
            WHERE
                candidate.site_id = %s
                {$where}
            ORDER BY
                %s %s",
            empty($customSqlColum)?"":",{$customSqlColum}",
            $customSqlJoin,
                $this->_siteID,
            $sortBy,
            $sortDirection
        );
        return $this->_db->getAllAssoc($sql);
    }    
    
    /**
     * Returns all candidates with full names matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array candidates data
     */
    public function byFullName($wildCardString, $sortBy, $sortDirection)
    {
        $wildCardString = str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);
        $arrFilter = $this->buildFilter();
        $filter=$arrFilter["where"];
        $column=$arrFilter["extra_column"];
        $join=$arrFilter["extra_join"];
        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.city AS city,
                candidate.state AS state,
                candidate.phone_home AS phoneHome,
                candidate.phone_cell AS phoneCell,
                candidate.key_skills AS keySkills,
                candidate.email1 AS email1,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
                %s
            FROM
                candidate
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
                %s
            WHERE
            (
                CONCAT(candidate.first_name, ' ', candidate.last_name) LIKE %s
                OR CONCAT(candidate.last_name, ' ', candidate.first_name) LIKE %s
                OR CONCAT(candidate.last_name, ', ', candidate.first_name) LIKE %s
            )
            %s
            AND
                candidate.is_admin_hidden = 0
            AND
                candidate.site_id = %s
            ORDER BY
                %s %s",
            empty($column)?"":",{$column}",
            $join,
            $wildCardString,
            $wildCardString,
            $wildCardString,
                $filter,
            $this->_siteID,
            $sortBy,
            $sortDirection
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all candidates with key skills matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array candidates data
     */
    public function byKeySkills($wildCardString, $sortBy, $sortDirection)
    {
        $tmp=DatabaseSearch::makeBooleanSQLWhere(
                DatabaseSearch::fulltextEncode($wildCardString),
                $this->_db,
                'candidate.key_skills'
                );
            $WHERE =  str_replace(") (", ") AND (", $tmp);
        /*$WHERE = DatabaseSearch::makeBooleanSQLWhere(
            $wildCardString, $this->_db, 'candidate.key_skills'
        );*/
        $wildCardString = str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);
        $arrFilter = $this->buildFilter();
        $filter=$arrFilter["where"];
        $column=$arrFilter["extra_column"];
        $join=$arrFilter["extra_join"];
        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.city AS city,
                candidate.state AS state,
                candidate.phone_home AS phoneHome,
                candidate.phone_cell AS phoneCell,
                candidate.key_skills AS keySkills,
                candidate.email1 AS email1,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
                %s
            FROM
                candidate
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
                %s
            WHERE
                %s
            AND
                candidate.is_admin_hidden = 0
            AND
                candidate.site_id = %s
            AND
                candidate.is_active = 1
            %s
            ORDER BY
                %s %s",
                empty($column)?"":",{$column}",
            $join,
            $WHERE,
            $this->_siteID,
            $filter,
            $sortBy,
            $sortDirection
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all candidates with E-Mail addresses matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array candidates data
     */
    public function byEmail($wildCardString, $sortBy = 'firstName', $sortDirection = 'ASC')
    {
        $wildCardString = str_replace('*', '%', $wildCardString);
        $wildCardString = $this->_db->makeQueryString($wildCardString);
        $arrFilter = $this->buildFilter();
        $filter=$arrFilter["where"];
        $column=$arrFilter["extra_column"];
        $join=$arrFilter["extra_join"];
        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.city AS city,
                candidate.state AS state,
                candidate.phone_home AS phoneHome,
                candidate.phone_cell AS phoneCell,
                candidate.key_skills AS keySkills,
                candidate.email1 AS email1,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
                %s
            FROM
                candidate
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
                %s
            WHERE
                candidate.email1 LIKE %s
            AND
                candidate.is_admin_hidden = 0
            AND
                candidate.site_id = %s
            %s
            ORDER BY
                %s %s",
                empty($column)?"":",{$column}",
            $join,
            $wildCardString,
            $this->_siteID,
            $filter,
            $sortBy,
            $sortDirection
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all candidates with phone numbers matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array candidates data
     */
    public function byPhone($wildCardString, $sortBy, $sortDirection)
    {
        $wildCardString = str_replace(
            array('.', '-', '(', ')'),
            '',
            $wildCardString
        );
        $wildCardString = $this->_db->makeQueryString($wildCardString);
        $arrFilter = $this->buildFilter();
        $filter=$arrFilter["where"];
        $column=$arrFilter["extra_column"];
        $join=$arrFilter["extra_join"];
        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.city AS city,
                candidate.state AS state,
                candidate.phone_home AS phoneHome,
                candidate.phone_cell AS phoneCell,
                candidate.key_skills AS keySkills,
                candidate.email1 AS email1,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
                %s
            FROM
                candidate
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
                %s
            WHERE
            (
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(candidate.phone_home, '-', ''),
                        '.', ''),
                    ')', ''),
                '(', '') LIKE %s
                OR REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(candidate.phone_cell, '-', ''),
                        '.', ''),
                    ')', ''),
                '(', '') LIKE %s
            )
            AND
                candidate.is_admin_hidden = 0
            AND
                candidate.site_id = %s
            %s
            ORDER BY
                %s %s",
                empty($column)?"":",{$column}",
            $join,
            $wildCardString,
            $wildCardString,
            $this->_siteID,
            $filter,
            $sortBy,
            $sortDirection
        );

        return $this->_db->getAllAssoc($sql);
    }
}


/**
 *	Companies Search Library
 *	@package    CATS
 *	@subpackage Library
 */
class SearchCompanies extends SearchBase
{
    private $_db;
    private $_siteID;
    protected $_userID = -1;


    public function __construct($siteID)
    {
        parent::__construct("company");
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        //FIXME: Library code Session dependencies suck.
        $this->_userID = $_SESSION['CATS']->getUserID();
    }
    
    
    /**
     * Returns all companies with names matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array companies data
     */
    public function byName($wildCardString, $sortBy, $sortDirection)
    {
        $wildCardString = str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);
        $arrFilter = $this->buildFilter();
        $filter=$arrFilter["where"];
        $column=$arrFilter["extra_column"];
        $join=$arrFilter["extra_join"];
        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS name,
                company.city AS city,
                company.state AS state,
                company.phone1 AS phone1,
                company.url AS url,
                company.key_technologies AS keyTechnologies,
                company.is_hot AS isHot,
                DATE_FORMAT(
                    company.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    company.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
                %s
            FROM
                company
            LEFT JOIN user AS owner_user
                ON company.owner = owner_user.user_id
            %s
            WHERE
                company.name LIKE %s
            AND
                company.site_id = %s
            %s
            ORDER BY
                %s %s",
            empty($column)?"":",{$column}",
            $join,
            $wildCardString,
            $this->_siteID,
            $filter,
            $sortBy,
            $sortDirection
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all companies with key technologies matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array candidates data
     */
    public function byKeyTechnologies($wildCardString)
    {
        $WHERE = DatabaseSearch::makeBooleanSQLWhere(
            $wildCardString, $this->_db, 'company.key_technologies'
        );
        $arrFilter = $this->buildFilter();
        $filter=$arrFilter["where"];
        $column=$arrFilter["extra_column"];
        $join=$arrFilter["extra_join"];
        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS name,
                company.city AS city,
                company.state AS state,
                company.phone1 AS phone1,
                company.url AS url,
                company.key_technologies AS keyTechnologies,
                company.is_hot AS isHot,
                DATE_FORMAT(
                    company.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    company.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
                %s
            FROM
                company
            LEFT JOIN user AS owner_user
                ON company.owner = owner_user.user_id
                %s
            WHERE
                %s
            AND
                company.site_id = %s
            %s
            ORDER BY
                company.name ASC",
                empty($column)?"":",{$column}",
            $join,
            $WHERE,
            $this->_siteID,
            $filter
        );

        return $this->_db->getAllAssoc($sql);
    }
}

/**
 *	Job Orders Search Library
 *	@package    CATS
 *	@subpackage Library
 */
class SearchJobOrders extends SearchBase
{
    private $_db;
    private $_siteID;
    protected $_userID = -1;


    public function __construct($siteID)
    {
        parent::__construct("joborder");
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        //FIXME: Library code Session dependencies suck.
        $this->_userID = $_SESSION['CATS']->getUserID();
    }
    
    
    /**
     * Returns all job orders with titles matching $wildCardString. If
     * activeOnly is true, only Active/OnHold/Full job orders will be shown.
     *
     * @param string wildcard match string
     * @param boolean return active job orders only
     * @return array job orders data
     */
    public function byTitle($wildCardString, $sortBy, $sortDirection,
        $activeOnly)
    {
        if ($activeOnly)
        {
            //FIXME:  Remove session dependancy.
            if ($_SESSION['CATS']->isFree())
            {
                $activeCriterion = "AND joborder.status = 'Active'";
            }
            else
            {
                $activeCriterion = "AND (joborder.status IN ('Active', 'OnHold', 'Full'))";
            }
        }
        else
        {
            $activeCriterion = "";
        }

        /*$WHERE = DatabaseSearch::makeBooleanSQLWhere(
            $wildCardString, $this->_db, 'joborder.title'
        );*/
        $wildCardString = str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);
        $arrFilter = $this->buildFilter();
        $filter=$arrFilter["where"];
        $column=$arrFilter["extra_column"];
        $join=$arrFilter["extra_join"];
        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS companyName,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.type AS type,
                joborder.is_hot AS isHot,
                joborder.duration AS duration,
                joborder.rate_max AS maxRate,
                joborder.salary AS salary,
                joborder.status AS status,
                recruiter_user.first_name AS recruiterFirstName,
                recruiter_user.last_name AS recruiterLastName,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS startDate,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    joborder.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
                %s
            FROM
                company
            LEFT JOIN joborder
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS recruiter_user
                ON joborder.recruiter = recruiter_user.user_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
                %s
            WHERE
              joborder.title LIKE %s
            %s
            AND
                joborder.is_admin_hidden = 0
            AND
                joborder.site_id = %s
            %s
            ORDER BY
                %s %s",
                empty($column)?"":",{$column}",
            $join,
            $wildCardString,
            $activeCriterion,
            $this->_siteID,
            $filter,
            $sortBy,
            $sortDirection
        );

        if (!eval(Hooks::get('JO_SEARCH_SQL'))) return;
        if (!eval(Hooks::get('JO_SEARCH_BY_TITLE'))) return;

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all job orders with company names matching $wildCardString. If
     * activeOnly is true, only Active/OnHold/Full job orders will be shown.
     *
     * @param string wildcard match string
     * @param boolean return active job orders only
     * @return array job orders data
     */
    public function byCompanyName($wildCardString, $sortBy, $sortDirection, $activeOnly)
    {
        $wildCardString = str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);
        
        if ($activeOnly)
        {
            //FIXME:  Remove session dependancy.
            if ($_SESSION['CATS']->isFree())
            {
                $activeCriterion = "AND joborder.status = 'Active'";
            }
            else
            {
                $activeCriterion = "AND (joborder.status IN ('Active', 'OnHold', 'Full'))";
            }
        }
        else
        {
            $activeCriterion = "";
        }
        $arrFilter = $this->buildFilter();
        $filter=$arrFilter["where"];
        $column=$arrFilter["extra_column"];
        $join=$arrFilter["extra_join"];
        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS companyName,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.type AS type,
                joborder.is_hot AS isHot,
                joborder.duration AS duration,
                joborder.rate_max AS maxRate,
                joborder.salary AS salary,
                joborder.status AS status,
                recruiter_user.first_name AS recruiterFirstName,
                recruiter_user.last_name AS recruiterLastName,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS startDate,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    joborder.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
                %s
            FROM
                company
            LEFT JOIN joborder
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS recruiter_user
                ON joborder.recruiter = recruiter_user.user_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
                %s
            WHERE
                company.name LIKE %s
            %s
            AND
                joborder.is_admin_hidden = 0
            AND
                joborder.site_id = %s
            AND
                company.site_id = %s
            %s
            ORDER BY
                %s %s",
                empty($column)?"":",{$column}",
            $join,
            $wildCardString,
            $activeCriterion,
            $this->_siteID,
            $this->_siteID,
            $filter,
            $sortBy,
            $sortDirection
        );

        if (!eval(Hooks::get('JO_SEARCH_SQL'))) return;
        if (!eval(Hooks::get('JO_SEARCH_BY_CLIENT_NAME'))) return;

        return $this->_db->getAllAssoc($sql);
    }
    
    /**
     * Returns all recently modified job orders. If activeOnly is true, 
     * only Active/OnHold/Full job orders will be shown.
     *
     * @param boolean return active job orders only
     * @return array job orders data
     */
    public function recentlyModified($sortDirection, $activeOnly, $limit)
    {
        if ($activeOnly)
        {
            //FIXME:  Remove session dependancy.
            if ($_SESSION['CATS']->isFree())
            {
                $activeCriterion = "AND joborder.status = 'Active'";
            }
            else
            {
                $activeCriterion = "AND (joborder.status IN ('Active', 'OnHold', 'Full'))";
            }
        }
        else
        {
            $activeCriterion = "";
        }

        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS companyName,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.type AS type,
                joborder.is_hot AS isHot,
                joborder.duration AS duration,
                joborder.rate_max AS maxRate,
                joborder.salary AS salary,
                joborder.status AS status,
                recruiter_user.first_name AS recruiterFirstName,
                recruiter_user.last_name AS recruiterLastName,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS startDate,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    joborder.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                joborder.date_modified AS dateModifiedSort
            FROM
                company
            LEFT JOIN joborder
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS recruiter_user
                ON joborder.recruiter = recruiter_user.user_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            WHERE
                joborder.site_id = %s
                %s
            AND
                company.site_id = %s
            AND
                joborder.is_admin_hidden = 0
            ORDER BY
                dateModifiedSort %s
            LIMIT 0, %s",
            $this->_siteID,
            $activeCriterion,
            $this->_siteID,
            $sortDirection,
            $limit
        );

        if (!eval(Hooks::get('JO_SEARCH_SQL'))) return;

        return $this->_db->getAllAssoc($sql);
    }
}


/**
 *	Contacts Search Library
 *	@package    CATS
 *	@subpackage Library
 */
class ContactsSearch extends SearchBase
{
    private $_db;
    private $_siteID;
    protected $_userID = -1;


    public function __construct($siteID)
    {
        parent::__construct("contact");
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        //FIXME: Library code Session dependencies suck.
        $this->_userID = $_SESSION['CATS']->getUserID();
    }
    
    
    /**
     * Returns all contacts with full names matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array contacts data
     */
    public function byFullName($wildCardString, $sortBy, $sortDirection)
    {
        $wildCardString = str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);
        $arrFilter = $this->buildFilter();
        $filter=$arrFilter["where"];
        $column=$arrFilter["extra_column"];
        $join=$arrFilter["extra_join"];
        $sql = sprintf(
            "SELECT
                contact.contact_id AS contactID,
                contact.company_id AS companyID,
                contact.last_name AS lastName,
                contact.first_name AS firstName,
                contact.title AS title,
                contact.phone_work AS phoneWork,
                contact.phone_cell AS phoneCell,
                contact.phone_other AS phoneOther,
                contact.email1 AS email1,
                contact.email2 AS email2,
                contact.is_hot AS isHotContact,
                contact.left_company AS leftCompany,
                DATE_FORMAT(
                    contact.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    contact.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                company.name AS companyName,
                company.is_hot AS isHotCompany
                %s
            FROM
                contact
            LEFT JOIN company
                ON contact.company_id = company.company_id
            LEFT JOIN user AS owner_user
                ON contact.owner = owner_user.user_id
                %s
            WHERE
            (
                CONCAT(contact.first_name, ' ', contact.last_name) LIKE %s
                OR CONCAT(contact.last_name, ' ', contact.first_name) LIKE %s
                OR CONCAT(contact.last_name, ', ', contact.first_name) LIKE %s
            )
            AND
                contact.site_id = %s
            AND
                company.site_id = %s
            %s
            ORDER BY
                %s %s",
                empty($column)?"":",{$column}",
            $join,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $this->_siteID,
            $this->_siteID,
            $filter,
            $sortBy,
            $sortDirection
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all contacts with company names matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array contacts data
     */
    public function byCompanyName($wildCardString, $sortBy,
        $sortDirection)
    {
        $wildCardString = str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);
        $arrFilter = $this->buildFilter();
        $filter=$arrFilter["where"];
        $column=$arrFilter["extra_column"];
        $join=$arrFilter["extra_join"];
        $sql = sprintf(
            "SELECT
                contact.contact_id AS contactID,
                contact.company_id AS companyID,
                contact.last_name AS lastName,
                contact.first_name AS firstName,
                contact.title AS title,
                contact.phone_work AS phoneWork,
                contact.phone_cell AS phoneCell,
                contact.phone_other AS phoneOther,
                contact.email1 AS email1,
                contact.email2 AS email2,
                contact.is_hot AS isHotContact,
                contact.left_company AS leftCompany,
                DATE_FORMAT(
                    contact.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    contact.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                company.name AS companyName,
                company.is_hot AS isHotCompany
                %s
            FROM
                contact
            LEFT JOIN company
                ON contact.company_id = company.company_id
            LEFT JOIN user AS owner_user
                ON contact.owner = owner_user.user_id
                %s
            WHERE
                company.name LIKE %s
            AND
                contact.site_id = %s
            AND
                company.site_id = %s
            %s
            ORDER BY
                %s %s",
                empty($column)?"":",{$column}",
            $join,
            $wildCardString,
            $this->_siteID,
            $this->_siteID,
            $filter,
            $sortBy,
            $sortDirection
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all contacts with titles matching $wildCardString.
     *
     * @param string wildcard match string
     * @return array contacts data
     */
    public function byTitle($wildCardString, $sortBy, $sortDirection)
    {
        $wildCardString = str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);
        $arrFilter = $this->buildFilter();
        $filter=$arrFilter["where"];
        $column=$arrFilter["extra_column"];
        $join=$arrFilter["extra_join"];
        $sql = sprintf(
            "SELECT
                contact.contact_id AS contactID,
                contact.company_id AS companyID,
                contact.last_name AS lastName,
                contact.first_name AS firstName,
                contact.title AS title,
                contact.phone_work AS phoneWork,
                contact.phone_cell AS phoneCell,
                contact.phone_other AS phoneOther,
                contact.email1 AS email1,
                contact.email2 AS email2,
                contact.is_hot AS isHotContact,
                contact.left_company AS leftCompany,
                DATE_FORMAT(
                    contact.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    contact.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                company.name AS companyName,
                contact.is_hot AS isHotCompany
                %s
            FROM
                contact
            LEFT JOIN company
                ON contact.company_id = company.company_id
            LEFT JOIN user AS owner_user
                ON contact.owner = owner_user.user_id
                %s
            WHERE
                contact.title LIKE %s
            AND
                contact.site_id = %s
            AND
                company.site_id = %s
            %s
            ORDER BY
                %s %s",
                empty($column)?"":",{$column}",
            $join,
            $wildCardString,
            $this->_siteID,
            $this->_siteID,
            $filter,
            $sortBy,
            $sortDirection
        );

        return $this->_db->getAllAssoc($sql);
    }
}


/**
 *	Quick Search Library
 *	@package    CATS
 *	@subpackage Library
 */
class QuickSearch
{
    private $_db;
    private $_siteID;
    protected $_userID = -1;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        //FIXME: Library code Session dependencies suck.
        $this->_userID = $_SESSION['CATS']->getUserID();
    }
    
    
    /**
     * Support function for Quick Search code. Searches all relevant fields for
     * $wildCardString.
     *
     * @param string wildcard match string
     * @return array candidates data
     */
    public function candidates($wildCardString)
    {
        $wildCardString = str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);

        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.phone_home AS phoneHome,
                candidate.phone_cell AS phoneCell,
                candidate.key_skills AS keySkills,
                candidate.email1 AS email1,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
            FROM
                candidate
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
            WHERE
            (
                CONCAT(candidate.first_name, ' ', candidate.last_name) LIKE %s
                OR CONCAT(candidate.last_name, ' ', candidate.first_name) LIKE %s
                OR CONCAT(candidate.last_name, ', ', candidate.first_name) LIKE %s
                OR candidate.email1 LIKE %s
                OR REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(candidate.phone_home, '-', ''),
                        '.', ''),
                    ')', ''),
                '(', '') LIKE %s
                OR REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(candidate.phone_cell, '-', ''),
                        '.', ''),
                    ')', ''),
                '(', '') LIKE %s
            )
            AND
                candidate.site_id = %s
            AND
                candidate.is_admin_hidden = 0
            ORDER BY
                candidate.date_modified DESC,
                candidate.first_name ASC,
                candidate.last_name ASC",
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }
    
    /**
     * Support function for Quick Search code. Searches all relevant fields for
     * $wildCardString.
     *
     * @param string wildcard match string
     * @return array companies data
     */
    public function companies($wildCardString)
    {
        $wildCardString = str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);

        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS name,
                company.city AS city,
                company.state AS state,
                company.phone1 AS phone1,
                company.url AS url,
                company.key_technologies AS keyTechnologies,
                company.is_hot AS isHot,
                DATE_FORMAT(
                    company.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    company.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                company
            LEFT JOIN user AS owner_user
                ON company.owner = owner_user.user_id
            WHERE
            (
                company.name LIKE %s
                OR company.phone1 LIKE %s
                OR company.phone2 LIKE %s
                OR company.url LIKE %s
            )
            AND
                company.site_id = %s
            ORDER BY
                company.name ASC",
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }
    
    /**
     * Support function for Quick Search code. Searches all relevant fields for
     * $wildCardString.
     *
     * @param string wildcard match string
     * @return array contacts data
     */
    public function contacts($wildCardString)
    {
        $wildCardString = str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);

        $sql = sprintf(
            "SELECT
                contact.contact_id AS contactID,
                contact.company_id AS companyID,
                contact.last_name AS lastName,
                contact.first_name AS firstName,
                contact.title AS title,
                contact.phone_work AS phoneWork,
                contact.phone_cell AS phoneCell,
                contact.phone_other AS phoneOther,
                contact.email1 AS email1,
                contact.email2 AS email2,
                contact.is_hot AS isHotContact,
                contact.left_company AS leftCompany,
                DATE_FORMAT(
                    contact.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    contact.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                company.name AS companyName,
                company.is_hot AS isHotCompany
            FROM
                contact
            LEFT JOIN company
                ON contact.company_id = company.company_id
            LEFT JOIN user AS owner_user
                ON contact.owner = owner_user.user_id
            WHERE
            (
                CONCAT(contact.first_name, ' ', contact.last_name) LIKE %s
                OR CONCAT(contact.last_name, ' ', contact.first_name) LIKE %s
                OR CONCAT(contact.last_name, ', ', contact.first_name) LIKE %s
                OR contact.phone_work LIKE %s
                OR company.name LIKE %s
                OR contact.email1 LIKE %s
                OR contact.email2 LIKE %s
                OR REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(contact.phone_work, '-', ''),
                        '.', ''),
                    ')', ''),
                '(', '') LIKE %s
                OR REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(contact.phone_cell, '-', ''),
                        '.', ''),
                    ')', ''),
                '(', '') LIKE %s
            )
            AND
                contact.site_id = %s
            AND
                company.site_id = %s
            ORDER BY
                name ASC",
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $wildCardString,
            $this->_siteID,
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }
    
    /**
     * Support function for Quick Search code. Searches all relevant fields for
     * $wildCardString.
     *
     * @param string wildcard match string
     * @return array job orders data
     */
    public function jobOrders($wildCardString)
    {
        $wildCardString = str_replace('*', '%', $wildCardString) . '%';
        $wildCardString = $this->_db->makeQueryString($wildCardString);

        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS companyName,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.type AS type,
                joborder.is_hot AS isHot,
                joborder.duration AS duration,
                joborder.rate_max AS maxRate,
                joborder.salary AS salary,
                joborder.status AS status,
                joborder.city AS city,
                joborder.state AS state,
                recruiter_user.first_name AS recruiterFirstName,
                recruiter_user.last_name AS recruiterLastName,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS startDate,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                DATE_FORMAT(
                    joborder.date_modified, '%%m-%%d-%%y'
                ) AS dateModified
            FROM
                joborder
            LEFT JOIN company
                ON joborder.company_id = company.company_id
            LEFT JOIN user AS recruiter_user
                ON joborder.recruiter = recruiter_user.user_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            WHERE
            (
                company.name LIKE %s
                OR joborder.title LIKE %s
            )
            AND
                joborder.is_admin_hidden = 0
            AND
                joborder.site_id = %s
            AND
                company.site_id = %s
            ORDER BY
                name ASC",
            $wildCardString,
            $wildCardString,
            $this->_siteID,
            $this->_siteID
        );

        if (!eval(Hooks::get('JO_SEARCH_SQL'))) return;
        if (!eval(Hooks::get('JO_SEARCH_BY_EVERYTHING'))) return;

        return $this->_db->getAllAssoc($sql);
    }
}

/**
 *	Saved Searches Library
 *	@package    CATS
 *	@subpackage Library
 */
class SavedSearches
{
    private $_db;
    private $_siteID;
    protected $_userID = -1;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
        //FIXME: Library code Session dependencies suck.
        $this->_userID = $_SESSION['CATS']->getUserID();
    }
    
    
    /**
     * Removes a saved search entry.
     *
     * @param integer search ID
     * @return void
     */
    public function remove($searchID)
    {
        $sql = sprintf(
            "DELETE FROM
                saved_search
            WHERE
                search_id = %s
            AND
                user_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($searchID),
            $this->_userID,
            $this->_siteID
        );
        $this->_db->query($sql);
    }

    /**
     * Promotes a recent search to a saved search.
     *
     * @param integer search ID
     * @return boolean True if successful; false otherwise.
     */
    public function save($searchID)
    {
        $sql = sprintf(
            "UPDATE
                saved_search
            SET
                is_custom = 1
            WHERE
                search_id = %s
            AND
                user_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($searchID),
            $this->_userID,
            $this->_siteID
        );

        return (boolean) $this->_db->query($sql);
    }

    //FIXME: Document me.
    public function removeRecent($dataItemType, $text)
    {
        $sql = sprintf(
            "DELETE FROM
                saved_search
            WHERE
                data_item_text = %s
            AND
                data_item_type = %s
            AND
                user_id = %s
            AND
                is_custom = 0
            AND
                site_id = %s",
            $this->_db->makeQueryString($text),
            $this->_db->makeQueryInteger($dataItemType),
            $this->_userID,
            $this->_siteID
        );
        $this->_db->query($sql);
    }

    //FIXME: Document me.
    public function add($dataItemType, $text, $url, $isCustom)
    {
        /* If this item is already in the saved search list, remove it. */
        $this->removeRecent($dataItemType, $text);

        $sql = sprintf(
            "INSERT INTO saved_search (
                site_id,
                user_id,
                data_item_type,
                data_item_text,
                url,
                is_custom,
                date_created
            )
            VALUES (
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                NOW()
            )",
            $this->_siteID,
            $this->_userID,
            $this->_db->makeQueryInteger($dataItemType),
            $this->_db->makeQueryString($text),
            $this->_db->makeQueryString($url),
            ($isCustom ? 1 : 0)
        );
        $this->_db->query($sql);

        $this->prune();
    }

    //FIXME: Document me.
    public function get($dataItemType)
    {
        $sql = sprintf(
            "SELECT
                search_id AS searchID,
                data_item_text AS dataItemText,
                url AS URL,
                is_custom AS isCustom
            FROM
                saved_search
            WHERE
                site_id = %s
            AND
                user_id = %s
            AND
                data_item_type = %s
            ORDER BY
                search_id DESC",
            $this->_siteID,
            $this->_userID,
            $this->_db->makeQueryInteger($dataItemType)
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Removes old saved search entries for a user.
     *
     * @return void
     */
    private function prune()
    {
        $sql = sprintf(
            "SELECT
                COUNT(*) AS count
            FROM
                saved_search
            WHERE
                site_id = %s
            AND
                user_id = %s
            AND
                is_custom = 0",
            $this->_siteID,
            $this->_userID
        );
        $rs = $this->_db->getAssoc($sql);

        $count = $rs['count'];

        // FIXME: Remove multiple entries at onceif we're more than one over?
        while ($count > RECENT_SEARCH_MAX_ITEMS)
        {
            /* Remove the least recent entry. */
            $sql = sprintf(
                "SELECT
                    search_id AS searchID
                FROM
                    saved_search
                WHERE
                    site_id = %s
                AND
                    user_id = %s
                AND
                    is_custom = 0
                ORDER BY
                    search_id
                ASC LIMIT 1",
                $this->_siteID,
                $this->_userID
            );
            $rs = $this->_db->getAssoc($sql);

            $sql = sprintf(
                "DELETE FROM
                    saved_search
                WHERE
                    search_id = %s",
                $rs['searchID']
            );
            $this->_db->query($sql);

            --$count;
        }
    }
}


/**
 *	Search by Resume Pager
 *	@package    CATS
 *	@subpackage Library
 */
class SearchByResumePager extends Pager
{
    private $_siteID;
    private $_db;
    private $_WHERE;


    public function __construct($rowsPerPage, $currentPage, $siteID,
        $wildCardString, $sortBy, $sortDirection)
    {
        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;

        $this->_sortByFields = array(
            'firstName',
            'lastName',
            'city',
            'state',
            'dateModifiedSort',
            'dateCreatedSort',
            'ownerSort'
        );

        if (ENABLE_SPHINX)
        {
            /* Sphinx API likes to throw PHP errors *AND* use it's own error
             * handling.
             */
            assert_options(ASSERT_WARNING, 0);

            $sphinx = new SphinxClient();
            $sphinx->SetServer(SPHINX_HOST, SPHINX_PORT);
            $sphinx->SetWeights(array(0, 100, 0, 0, 50));
            $sphinx->SetMatchMode(SPH_MATCH_EXTENDED);
            $sphinx->SetLimits(0, 1000);
            $sphinx->SetSortMode(SPH_SORT_TIME_SEGMENTS, 'date_added');

            // FIXME: This can be sped up a bit by actually grouping ranges of
            //        site IDs into their own index's. Maybe every 500 or so at
            //        least on the Hosted system.
            $sphinx->SetFilter('site_id', array($this->_siteID));

            /* Create the Sphinx query string. */
            $wildCardString = DatabaseSearch::humanToSphinxBoolean($wildCardString);
            
            /* Execute the Sphinx query. Sphinx can ask us to retry if its
             * maxed out. Retry up to 5 times.
             */
            $tries = 0;
            do
            {
                /* Wait for one second if this isn't out first attempt. */
                if (++$tries > 1)
                {
                    sleep(1);
                }
                
                $results = $sphinx->Query($wildCardString, SPHINX_INDEX);
                $errorMessage = $sphinx->GetLastError();
            }
            while (
                $results === false &&
                strpos($errorMessage, 'server maxed out, retry') !== false &&
                $tries <= 5
            );

            /* Throw a fatal error if Sphinx errors occurred. */
            if ($results === false)
            {   
                $this->fatal('Sphinx Error: ' . ucfirst($errorMessage) . '.');
            }

            /* Throw a fatal error (for now) if Sphinx warnings occurred. */
            $lastWarning = $sphinx->GetLastWarning();
            if (!empty($lastWarning))
            {
                // FIXME: Just display a warning, and notify dev team.
                $this->fatal('Sphinx Warning: ' . ucfirst($lastWarning) . '.');
            }

            /* Show warnings for assert()s again. */
            assert_options(ASSERT_WARNING, 1);

            if (empty($results['matches']))
            {
                $this->_WHERE = '0';
            }
            else
            {
                $attachmentIDs = implode(',', array_keys($results['matches']));
                $this->_WHERE = 'attachment.attachment_id IN(' . $attachmentIDs . ')';
            }

        }
        else
        {
            $tmp=DatabaseSearch::makeBooleanSQLWhere(
                DatabaseSearch::fulltextEncode($wildCardString),
                $this->_db,
                'attachment.text'
                );
            $this->_WHERE =  str_replace(") (", ") AND (", $tmp);
            /*trace($tmp);
            $matches=array();
            preg_match_all('/"(?:\\\\.|[^\\\\"])*"|\S+/', $wildCardString, $matches);
            
            $arrWhere=array();
            $arrNew=array();
            for($m=0;$m<count($matches[0]);$m++)
            {
                $match=$matches[0][$m];
                if($match[0]=='"')
                {
                    $arrNew[]=$match;
                }
                else
                {
                    $tmp="";
                    for($n=$m;$n<count($matches[0]);$n++)
                    {
                        $match=$matches[0][$n];
                        if($match[0]=='"' || strtolower($match)=="and" || strtolower($match)=="or")
                        {
                            $m=$n-1;
                            break;
                        }
                        if($tmp==="")
                        {
                            $tmp=$match;
                        }
                        else
                        {
                            $tmp=$tmp." ".$match;
                        }
                    }
                    $tmp='"'.$tmp.'"';
                    $arrNew[]=$tmp;
                }
            }
            foreach($arrNew as $match)
            {
                $arrWhere[] = DatabaseSearch::makeBooleanSQLWhere(
                DatabaseSearch::fulltextEncode($match),
                $this->_db,
                'attachment.text'
                );
            }
             $this->_WHERE=implode(" AND ",$arrWhere);*/
        }
        $sbase=new SearchBase();
        $arrFilter = $sbase->buildFilter();
        $filter=$arrFilter["where"];
        $column=$arrFilter["extra_column"];
        $join=$arrFilter["extra_join"];
        $isSearchAttachment=(isset($_REQUEST["bulk_resume"]) && $_REQUEST["bulk_resume"]);
        if($isSearchAttachment)
        {
            /* How many companies do we have? */
            $sql = sprintf(
                "SELECT
                count(*) AS count
            FROM
                attachment
            LEFT JOIN candidate
                ON attachment.data_item_id = candidate.candidate_id
                AND attachment.site_id = candidate.site_id
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
                %s
            WHERE
                resume = 1
            AND
                %s
            AND
                (attachment.data_item_type = %s OR attachment.data_item_type = %s)
            AND
                attachment.site_id = %s
            AND
                (ISNULL(candidate.is_admin_hidden) OR (candidate.is_admin_hidden = 0))
            AND
                (ISNULL(candidate.is_active) OR (candidate.is_active = 1))
                %s
",
                    
                $join,
            $this->_WHERE,
            DATA_ITEM_CANDIDATE,
            DATA_ITEM_BULKRESUME,
            $this->_siteID,
                    $filter
            );
        }
        else
        {
             $sql = sprintf(
                "SELECT
                count(*) AS count
            FROM
                attachment
            LEFT JOIN candidate
                ON attachment.data_item_id = candidate.candidate_id
                AND attachment.site_id = candidate.site_id
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
                %s
            WHERE
                resume = 1
            AND
                %s
            AND
                (attachment.data_item_type = %s)
            AND
                attachment.site_id = %s
            AND
                (ISNULL(candidate.is_admin_hidden) OR (candidate.is_admin_hidden = 0))
            AND
                (ISNULL(candidate.is_active) OR (candidate.is_active = 1))
                %s
",
                $join,
            $this->_WHERE,
            DATA_ITEM_CANDIDATE,
            $this->_siteID,
                    $filter
            );
        }
        $rs = $this->_db->getAssoc($sql);

        /* Pass "Search By Resume"-specific parameters to Pager constructor. */
        parent::__construct($rs['count'], $rowsPerPage, $currentPage);
    }
    
    //FIXME: Document me.
    public function getPage()
    {
        $sbase=new SearchBase();
        $arrFilter = $sbase->buildFilter();
        $filter=$arrFilter["where"];
        $column=$arrFilter["extra_column"];
        $join=$arrFilter["extra_join"];
        $isSearchAttachment=(isset($_REQUEST["bulk_resume"]) && $_REQUEST["bulk_resume"]);
        if($isSearchAttachment)
        {
            $sql = sprintf(
            "SELECT
                attachment.attachment_id AS attachmentID,
                attachment.data_item_id AS candidateID,
                attachment.title AS title,
                attachment.text AS text,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.city AS city,
                candidate.state AS state,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                candidate.date_created AS dateCreatedSort,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                candidate.date_modified AS dateModifiedSort,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                CONCAT(owner_user.last_name, owner_user.first_name) AS ownerSort
                %s
            FROM
                attachment
            LEFT JOIN candidate
                ON attachment.data_item_id = candidate.candidate_id
                AND attachment.site_id = candidate.site_id
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
                %s
            WHERE
                resume = 1
            AND
                %s
            AND
                (attachment.data_item_type = %s OR attachment.data_item_type = %s)
            AND
                attachment.site_id = %s
            AND
                (ISNULL(candidate.is_admin_hidden) OR (candidate.is_admin_hidden = 0))
            AND
                (ISNULL(candidate.is_active) OR (candidate.is_active = 1))
                %s
            ORDER BY
                %s %s
            LIMIT %s, %s",
            empty($column)?"":",{$column}",
            $join,
            $this->_WHERE,
            DATA_ITEM_CANDIDATE,
            DATA_ITEM_BULKRESUME,
            $this->_siteID,
                    $filter,
            $this->_sortBy,
            $this->_sortDirection,
            $this->_thisPageStartRow,
            $this->_rowsPerPage
            );
        }
        else
        {
            $sql = sprintf(
            "SELECT
                attachment.attachment_id AS attachmentID,
                attachment.data_item_id AS candidateID,
                attachment.title AS title,
                attachment.text AS text,
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.city AS city,
                candidate.state AS state,
                DATE_FORMAT(
                    candidate.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                candidate.date_created AS dateCreatedSort,
                DATE_FORMAT(
                    candidate.date_modified, '%%m-%%d-%%y'
                ) AS dateModified,
                candidate.date_modified AS dateModifiedSort,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                CONCAT(owner_user.last_name, owner_user.first_name) AS ownerSort
                %s
            FROM
                attachment
            LEFT JOIN candidate
                ON attachment.data_item_id = candidate.candidate_id
                AND attachment.site_id = candidate.site_id
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
                %s
            WHERE
                resume = 1
            AND
                %s
            AND
                (attachment.data_item_type = %s)
            AND
                attachment.site_id = %s
            AND
                (ISNULL(candidate.is_admin_hidden) OR (candidate.is_admin_hidden = 0))
            AND
                (ISNULL(candidate.is_active) OR (candidate.is_active = 1))
                %s
            ORDER BY
                %s %s
            LIMIT %s, %s",
            empty($column)?"":",{$column}",
            $join,
            $this->_WHERE,
            DATA_ITEM_CANDIDATE,
            $this->_siteID,
                    $filter,
            $this->_sortBy,
            $this->_sortDirection,
            $this->_thisPageStartRow,
            $this->_rowsPerPage
            );
        }
        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Print a fatal error and die.
     *
     * @param string error message
     * @return void
     */
    protected function fatal($error)
    {
        $template = new Template();
        $template->assign('errorMessage', $error);
        $template->display('./Error.tpl');
        die();
    }
}


/**
 *	Search Results Pager
 *	@package    CATS
 *	@subpackage Library
 */
class SearchPager extends Pager
{
    private $_siteID;
    private $_db;
    private $_rs;


    public function __construct($rowsPerPage, $currentPage, $siteID)
    {
        $this->_sortByFields = array(
            'firstName',
            'lastName',
            'city',
            'state',
            'dateModified',
            'dateCreated',
            'owner',
            'phone1',
            'companyName',
            'title',
            'owner_user',
            'owner_user.last_name',
            'type',
            'status',
            'startDate',
            'recruiterLastName',
            'dateCreatedSort',
            'dateModifiedSort',
            'ownerSort'
        );

        /* Pass "Search By Resume"-specific parameters to Pager constructor. */
        parent::__construct(count($this->_rs), $rowsPerPage, $currentPage);
    }
}

?>