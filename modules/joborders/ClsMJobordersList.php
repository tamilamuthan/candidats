<?php
include_once("mvc/models/ClsMList.php");
class ClsMJobordersList extends ClsMList
{
    public function __construct()
    {
        parent::__construct("joborder",6);
        $this->_db = DatabaseConnection::getInstance();
        
        $this->_userID = $_SESSION['CATS']->getUserID();
        $this->objSQL = new ClsAuieoSQL();
        $objFrom=$this->objSQL->addFrom("joborder");
        $joinContactCompanyID=$objFrom->addJoinField("company_id");
        
        $objFromCompany=$this->objSQL->addFrom("company");
        $companyJoinID=$objFromCompany->addJoinField("company_id");
        $objFromCompany->setJoinWith($objFrom,$joinContactCompanyID,$companyJoinID);
        
        $joinID=$objFrom->addJoinField("owner");
        $objFromUser=$this->objSQL->addFrom("user","owner_user");
        $userJoinID=$objFromUser->addJoinField("user_id");
        $objFromUser->setJoinWith($objFrom,$joinID,$userJoinID);
        
        $joinID=$objFrom->addJoinField("recruiter");
        $objFromUserR=$this->objSQL->addFrom("user","recruiter_user");
        $userJoinID=$objFromUserR->addJoinField("user_id");
        $objFromUserR->setJoinWith($objFrom,$joinID,$userJoinID);
        
        $this->objSQL->addSelect($objFrom, "joborder_id","jobOrderID");
        $this->objSQL->addSelect($objFrom, "company_id","companyID");
        $this->objSQL->addSelect($objFrom, "title","title");
        $this->objSQL->addSelect($objFrom, "type","type");
        $this->objSQL->addSelect($objFrom, "is_hot","isHot");
        $this->objSQL->addSelect($objFrom, "duration","duration");
        $this->objSQL->addSelect($objFrom, "rate_max","maxRate");
        $this->objSQL->addSelect($objFrom, "salary","salary");
        $this->objSQL->addSelect($objFrom, "status","status");
        
        $this->objSQL->addSelect($objFromCompany, "name","companyName");
        
        $this->objSQL->addSelect($objFromUserR, "first_name","recruiterFirstName");
        $this->objSQL->addSelect($objFromUserR, "last_name","recruiterLastName");
        
        $this->objSQL->addSelect($objFromUser, "first_name","ownerFirstName");
        $this->objSQL->addSelect($objFromUser, "last_name","ownerLastName");
        
        $this->objSQL->addSelectCustom("DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y'
                )","dateCreated");
        $this->objSQL->addSelectCustom("DATE_FORMAT(
                    joborder.date_modified, '%%m-%%d-%%y'
                )","dateModified");
        $objWhereCandidate=$this->objSQL->addWhere($objFrom, "site_id", $this->_siteID);
    }
    
    public function byFullName($wildCardString, $sortBy, $sortDirection)
    {
        if(empty($wildCardString)) return $this->byAll ($sortBy, $sortDirection);
        $from=$this->objSQL->getFromObjectByTableName("contact");
        $wildCardString=trim($wildCardString);
        if(preg_match("/{$wildCardString}/", null) !== false)
        {
            $objWhere=$this->objSQL->addWhere($from, "last_name", $wildCardString);
            $objWhere->setCondition("rlike");
            $objWhere->setBoolean("OR");
            $objWhere->setGroup();
            
            $objWhere=$this->objSQL->addWhere($from, "first_name", $wildCardString);
            $objWhere->setCondition("rlike");
            $objWhere->setBoolean("OR");
            $objWhere->setGroup();
        }
        else
        {
            $objWhere=$this->objSQL->addWhere($from, "last_name", $wildCardString);
            $objWhere->setCondition("like");
            $objWhere->setBoolean("OR");
            $objWhere->setGroup();
            
            $objWhere=$this->objSQL->addWhere($from, "first_name", $wildCardString);
            $objWhere->setCondition("like");
            $objWhere->setBoolean("OR");
            $objWhere->setGroup();
        }
        
        $this->buildFilter($this->objSQL);
        $this->objSQL->addOrderBy($sortBy,$sortDirection=="ASC"?true:false);
        ClsNaanalSession::getInstance()->setPanelData("search_sql", $this->objSQL->render());
        $arrPager=ClsNaanalRequest::getInstance()->getPager();
        $this->objSQL->setLimit($arrPager["start"],$arrPager["items_per_page"]);
        $sql=$this->objSQL->render();
        $count_sql=$this->objSQL->render(true);
        $arr=$this->_db->getAllAssoc($count_sql);
        $totalRecords=$arr[0]["count"];
        
        return array("total_records"=>$totalRecords,"records"=>$this->_db->getAllAssoc($sql));
        
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

    
    public function byCompanyName($wildCardString, $sortBy,
        $sortDirection)
    {
        if(empty($wildCardString)) return $this->byAll ($sortBy, $sortDirection);
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

    public function byAll($sortBy, $sortDirection)
    {
        $from=false;
        $this->buildFilter($this->objSQL);
        $this->objSQL->addOrderBy($sortBy,$sortDirection=="ASC"?true:false);
        ClsNaanalSession::getInstance()->setPanelData("search_sql", $this->objSQL->render());
        $arrPager=ClsNaanalRequest::getInstance()->getPager();
        $this->objSQL->setLimit($arrPager["start"],$arrPager["items_per_page"]);
        $sql=$this->objSQL->render();
        $count_sql=$this->objSQL->render(true);
        $arr=$this->_db->getAllAssoc($count_sql);
        $totalRecords=$arr[0]["count"];
        $rs=$this->_db->getAllAssoc($sql);
        foreach ($rs as $rowNumber => $row)
        {
            $this->setValue($row["companyID"],$rowNumber,0);
            $this->setValue($row["name"],$rowNumber,1);
            $this->setValue($row["phone1"],$rowNumber,2);
            $this->setValue($row["keyTechnologies"],$rowNumber,3);
            $this->setValue($row["dateCreated"],$rowNumber,4);
            $this->setValue($row["ownerFirstName"]." ".$row["ownerLastName"],$rowNumber,5);
        }
        $this->render();
    }
    
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
?>