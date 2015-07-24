<?php 

use Wobeto\EasyChart\Model\XYDataSet;
use Wobeto\EasyChart\Model\Point;

class XYDataSetTest extends \PHPUnit_Framework_Testcase{

	private $point;
	private $dataSet;

	public function __construct(){
		$this->point   = new Point(2014, 34);
		$this->dataSet = new XYDataSet();
		$this->dataSet->addPoint($this->point);
	}

	public function testGetPointList(){
		$this->assertInternalType('array', $this->dataSet->getPointList());
	}

	public function testGetTotalPointList(){
		$this->assertEquals(1,count($this->dataSet->getPointList()));
	}

	public function testIsPointInstanceInDataSet(){
		$PointList = $this->dataSet->getPointList();
		$this->assertInstanceOf('Wobeto\EasyChart\Model\Point', $PointList[0]);
	}

}