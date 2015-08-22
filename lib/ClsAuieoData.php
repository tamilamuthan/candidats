<?php
class ClsAuieoData
{
    private $data="";
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
    public function getColSpan($cols)
    {
        return $this->colSpan;
    }
    public function setRowSpan($rows)
    {
        $this->rowSpan=$rows;
    }
    public function addData($data)
    {
        $this->data=$data;
    }
    public function getData()
    {
        return $this->data;
    }
}
?>