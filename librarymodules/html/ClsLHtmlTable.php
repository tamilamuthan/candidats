<?php

class ClsLHtmlTable
{
    private $cols=1;
    private $arrCell=array();
    private $tableID;
    private $arrParam=array();
    private $colWidth=array();
    private $rowParam=array();
    private $colParam=array();
    private $arrRow=array();
    
    private $isColHeading=false;
    private $isSideHeading=false;
    
    function __construct($cols=1)
    {
        $this->cols=$cols;
    }
    function setParam($name,$data)
    {
        $this->arrParam[$name]=$data;
    }
    function setWidth($wid)
    {
        $this->arrParam["width"]=$wid;
    }
    function setBackground($htmlColorCode)
    {
        $this->arrParam["bgcolor"]=$htmlColorCode;
    }
    public function setColumnWidth($col,$width)
    {
        $this->colWidth[$col]=$width;
    }
    public function setRowParam($row,$param,$data)
    {
        $this->rowParam[$param][$row]=$data;
    }
    public function setColParam($col,$param,$data)
    {
        $this->colParam[$param][$col]=$data;
    }
    public function toggleColHeading()
    {
        $this->isColHeading=$this->isColHeading===false?true:false;
    }
    public function toggleSideHeading()
    {
        $this->isSideHeading=$this->isSideHeading===false?true:false;
    }
    public function getColHeading()
    {
        return $this->isColHeading;
    }
    public function getSideHeading()
    {
        return $this->isSideHeading;
    }
    public function setColHeading()
    {
        $this->isColHeading=true;
    }
    public function setSideHeading()
    {
        $this->isSideHeading=true;
    }
    public function resetColHeading()
    {
        $this->isColHeading=false;
    }
    public function resetSideHeading()
    {
        $this->isSideHeading=false;
    }
    public function buildTableStructure($cols=false)
    {
        $count=count($this->arrCell);
        if($cols===false) $cols=$this->cols;
        $r=0;
        for($cell=0;$cell<$count;)
        {
            for($c=0;$c<$this->cols;$c++)
            {
                $colspan=1;
                $currentCol=$c;
                if(isset($this->arrCell[$cell]))
                {
                    $this->arrRow[$r][$c]=$this->arrCell[$cell];
                    $colspan=$this->arrCell[$cell]->getColSpan();
                    if($colspan>1)
                    {
                        $c=$c+$colspan-1;
                    }
                }
                else
                    $this->arrRow[$r][$c]=new ClsLHtmlcell();
                if(isset($this->colWidth[$c]) && $colspan<=1)
                {
                    $cellwidth=$this->arrRow[$r][$c]->getParam("width");
                    if($cellwidth===false)
                    {
                        $this->arrRow[$r][$c]->setParam("width",$this->colWidth[$c]);
                    }
                }
                foreach($this->colParam as $attribute=>$colParam)
                {
                    if(isset($this->colParam[$attribute][$c]) && $colspan<=1)
                    {
                        $celldata=$this->arrRow[$r][$c]->getParam($attribute);
                        if($celldata===false)
                        {
                            $this->arrRow[$r][$c]->setParam($attribute,$this->colParam[$attribute][$c]);
                        }
                    }
                }
                $cell++;
            }
            $r++;
        }
    }
    public function searchHtmlcell($data,$fromPos=0)
    {
        if(empty($this->arrRow)) return false;
        $pos=-1;
        foreach($this->arrRow as $rowNum=>$row)
        {
            foreach($row as $colNum=>$cell)
            {
                $pos++;
                $cellData=$cell->getData();
                if(stripos($cellData, $data)!==false)
                {
                    if($pos<$fromPos) continue;
                    return array("row"=>$rowNum,"col"=>$colNum,"pos"=>$pos);
                }
            }
        }
        return false;
    }
    /**
     * 
     * @param type $row ($row can be both string as well as number. if $row is string, the data is retrived based on the side heading)
     * @param type $col ($col can be both string as well as number. if $col is string, the data is retrived based on the column heading)
     * @param type $isInsert (if true, the cell will be inserted anywhere in the array)
     * @return type (returns cell object)
     */
    public function &getHtmlcell($pos=-1,$isInsert=false)
    {
        if($pos===-1)
        {
            $this->arrCell[]=new ClsLHtmlcell();
            return $this->arrCell[count($this->arrCell)-1];
        }
        $count=count($this->arrCell);
        if($pos>=$count)
        {
            for($c=$count;$c<=$pos;$c++)
            {
                $this->arrCell[]=new ClsLHtmlcell();
            }
        }
        else
        {
            if($isInsert)
            {
                $tmp=new ClsLHtmlcell();
                array_splice($this->arrCell, $pos, 0, array($tmp));
            }
        }
        return $this->arrCell[$pos];
    }
    /**
     * add data at the end of table if $row and $col is -1, else the data will be inserted (or) created
     * @param type $value
     * @param type $row
     * @param type $col
     * @return type
     */
    function &addData($value,$pos=-1)
    {
        $count=count($this->arrCell);
        if($pos<0)
        {
            $objCell=$this->getHtmlcell($pos);
            $objCell->addData($value);
            return $objCell;
        }
        if($pos<=$count)
        {
            $objCell=$this->getHtmlcell($pos);
        }
        else
        {
            $objCell=$this->getHtmlcell($pos,true);
        }
        $objCell->addData($value);
        return $objCell;
    }
    function addEmptyCells($count,$row=false)
    {
        if($row===false)
        {
            $pos=count($this->arrCell);
            for($i=0;$i<$count;$i++)
            {
                $this->addData("");
            }
        }
        else
        {
            $pos=$row*$this->cols;
            for($i=0;$i<$count;$i++)
            {
                $this->addData("",$pos+$i);
            }
        }
        return $pos;
    }
    function addRow($arrValue,$pos=-1)
    {
        if($pos===-1)
        {
            $pos=count($this->arrCell);
            for($i=0;$i<count($arrValue);$i++)
            {
                $this->addData($arrValue[$i]);
            }
        }
        else
        {
            for($i=0;$i<count($arrValue);$i++)
            {
                $this->addData($arrValue[$i],$pos+$i);
            }
        }
        return $pos;
    }

    function &modifyData($value,$pos)
    {
        $objCell=$this->getHtmlcell($pos);
        $objCell->addData($value);
        return $objCell;
    }

    function render($transpose=false,$isAssociativeArray=false)
    {
        if($this->tableID!="")
        {
            $tblID="id='".$this->tableID."'";
        }
        else
        {
            $tblID="";
        }
        $param="";
        foreach($this->arrParam as $k=>$v)
        {
            if($param==="")
            {
                $param="{$k}='{$v}'";
            }
            else
            {
                $param=$param." {$k}='{$v}'";
            }
        }
        $table="<table $tblID $param>";
                
        if(empty($this->arrRow)) $this->buildTableStructure();
        if($transpose)
        {
            $arrTr=array();
            foreach($this->arrRow as $rowNum=>$arrCol)
            {
                foreach($arrCol as $ind=>$objCol)
                {
                    if($this->isColHeading && $rowNum===0)
                    {
                        $objCol->setAsColHeading();
                    }
                    if($this->isSideHeading && $ind===0)
                    {
                        $objCol->setAsSideHeading();
                    }
                    if(isset($this->rowParam["bgcolor"][$rowNum]))
                    {
                        $celldata=$objCol->getParam("bgcolor");
                        if($celldata===false)
                        {
                            $objCol->setParam("bgcolor",$this->rowParam["bgcolor"][$rowNum]);
                        }
                    }
                    $arrTr[$ind][]=$objCol->render();
                    //$tr.=$objCol->render();
                }
            }
            foreach($arrTr as $arrtr)
            {
                $table=$table."<tr>";
                foreach($arrtr as $tr)
                {
                    $table="{$table}
                    {$tr}";
                }
                $table=$table."</tr>";
            }
        }
        else
        {
            foreach($this->arrRow as $rowNum=>$arrCol)
            {
                $tr="";
                foreach($arrCol as $ind=>$objCol)
                {
                    if($this->isColHeading && $rowNum===0)
                    {
                        $objCol->setAsColHeading();
                    }
                    if($this->isSideHeading && $ind===0)
                    {
                        $objCol->setAsSideHeading();
                    }
                    if(isset($this->rowParam["bgcolor"][$rowNum]))
                    {
                        $celldata=$objCol->getParam("bgcolor");
                        if($celldata===false)
                        {
                            $objCol->setParam("bgcolor",$this->rowParam["bgcolor"][$rowNum]);
                        }
                    }
                    $tr.=$objCol->render();
                }
                if($this->isColHeading)
                {
                    if($rowNum===0)
                    {
                        $table="{$table}
                        <thead><tr>{$tr}</tr></thead>";
                    }
                    else if($rowNum===1)
                    {
                        $table="{$table}
                        <tbody><tr>{$tr}</tr>";
                    }
                    else
                    {
                        $table="{$table}
                        <tr>{$tr}</tr>";
                    }
                }
                else
                {
                    if($rowNum===0)
                    {
                        $table="{$table}
                        <tbody><tr>{$tr}</tr>";
                    }
                    else
                    {
                        $table="{$table}
                        <tr>{$tr}</tr>";
                    }
                }
            }
            $table.="</tbody>";
        }
        $table.="</table>";
        return $table;
    }
}
class ClsLHtmlcell
{
    private $isColHeading=false;
    private $isSideHeading=false;
    private $data="";
    private $arrParam=array();
    private $arrClass=array();
    private $colSpan=1;
    private $rowSpan=1;
    public function __construct()
    {
    }
    public function setColSpan($cols)
    {
        $this->colSpan=$cols;
    }
    public function getRowSpan()
    {
        return $this->rowSpan;
    }
    public function getColSpan()
    {
        return $this->colSpan;
    }
    public function setRowSpan($rows)
    {
        $this->rowSpan=$rows;
    }
    public function setAsColHeading()
    {
        $this->isColHeading=true;
    }
    public function setAsSideHeading()
    {
        $this->isSideHeading=true;
    }
    public function addData($data)
    {
        $this->data=$data;
    }
    public function setParam($name,$value)
    {
        $this->arrParam[$name]=$value;
    }
    public function addParam($name,$value)
    {
        $this->setParam($name,$value);
    }
    public function addClass($class)
    {
        $this->arrClass[]=$class;
    }
    public function getParam($name)
    {
        if(isset($this->arrParam[$name]))
        {
            return $this->arrParam[$name];
        }
        return false;
    }
    public function getData()
    {
        return $this->data;
    }
    public function render()
    {
        $param="";
        if($this->arrParam)
        {
            foreach($this->arrParam as $k=>$v)
            {
                $param=$param." {$k}='{$v}'";
            }
        }
        $class="";
        if(!empty($this->arrClass))
        {
            $class=" class='".implode(" ",$this->arrClass)."'";
        }
        if($this->isColHeading)
            return "<th{$param}{$class}>{$this->data}</th>";
        else
        {
            $colSpan="";
            if($this->colSpan>1)
            {
                $colSpan=" colspan={$this->colSpan} ";
            }
            return "<td{$colSpan}{$param}{$class}>{$this->data}</td>";
        }
    }
}
?>