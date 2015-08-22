<?php
class ClsAuieoFluidArray extends ClsAuieoDataStructure
{
    protected $cols=1;
    protected $rows=false;
    private $arrData=array();
    protected $arrRow=array();
    protected $arrRenderRow=array();
    function __construct($cols=1)
    {
        $this->cols=$cols;
        parent::__construct();
    }
    public function setCols()
    {
        $this->cols=$cols;
    }
    public function getCols()
    {
        return $this->cols;
    }
    public function buildStructure($cols=false)
    {
        $count=count($this->arrData);
        if($cols===false) $cols=$this->cols;
        $r=0;
        for($cell=0;$cell<$count;)
        {
            for($c=0;$c<$this->cols;$c++)
            {
                $colspan=1;
                $currentCol=$c;
                if(isset($this->arrData[$cell]))
                {
                    $this->arrRow[$r][$c]=$this->arrData[$cell];
                    $colspan=$this->arrData[$cell]->getColSpan();
                    if($colspan>1)
                    {
                        $c=$c+$colspan-1;
                    }
                }
                else
                    $this->arrRow[$r][$c]=new ClsAuieoData();
                $cell++;
            }
            $r++;
        }
        $this->rows=$r;
    }
    public function searchData($data,$fromPos=0)
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
    public function &getDataObject($pos=-1,$isInsert=false)
    {
        if($pos===-1)
        {
            $this->arrData[]=new ClsAuieoData();
            return $this->arrData[count($this->arrData)-1];
        }
        $count=count($this->arrData);
        if($pos>=$count)
        {
            for($c=$count;$c<=$pos;$c++)
            {
                $this->arrData[]=new ClsAuieoData();
            }
        }
        else
        {
            if($isInsert)
            {
                $tmp=new ClsAuieoData();
                array_splice($this->arrData, $pos, 0, array($tmp));
            }
        }
        return $this->arrData[$pos];
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
        $count=count($this->arrData);
        if($pos<0)
        {
            $objCell=$this->getDataObject($pos);
            $objCell->addData($value);
            return $objCell;
        }
        if($pos>=$count)
        {
            $objCell=$this->getDataObject($pos);
        }
        else
        {
            $objCell=$this->getDataObject($pos,true);
        }
        $objCell->addData($value);
        return $objCell;
    }
    function addEmptyCells($count,$row=false)
    {
        if($row===false)
        {
            $pos=count($this->arrData);
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
            $pos=count($this->arrData);
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
    function getData($pos)
    {
        if(isset($this->arrData[$pos])) return $this->arrData[$pos]->getData();
        return null;
    }
    function &modifyData($value,$pos)
    {
        $objCell=$this->getDataObject($pos);
        $objCell->addData($value);
        return $objCell;
    }
    public function getRenderArray()
    {
        return $this->arrRenderRow;
    }

    function &render($transpose=false)
    {
        if(empty($this->arrRow)) $this->buildStructure();
        foreach($this->arrRow as $rowNum=>$arrCol)
        {
            foreach($arrCol as $ind=>$objCol)
            {
                $data=$objCol->getData();
                if($this->arrHook)
                {
                    foreach($this->arrHook as $hook)
                    {
                        $ret=$hook($data,$rowNum,$ind);
                        if(!is_null($ret))
                        {
                            $data=$ret;
                        }
                    }
                }
                $this->arrRenderRow[$rowNum][$ind]=$data;
            }
        }
        return $this->arrRenderRow;
    }
}
?>