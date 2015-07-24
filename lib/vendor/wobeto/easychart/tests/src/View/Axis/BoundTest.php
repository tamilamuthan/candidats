<?php 


use Wobeto\EasyChart\View\Axis\Bound;
use Wobeto\EasyChart\Model\XYDataSet;
use Wobeto\EasyChart\Model\Point;

class BoundTest extends \PHPUnit_Framework_Testcase{

	private $bound;

	public function __construct(){
		$this->bound   = new Bound();
		$this->dataSet = new XYDataSet();
		
		$this->dataSet->addPoint(new Point(2014, 25));
		$this->dataSet->addPoint(new Point(2014, 30));
		$this->bound->computeBound($this->dataSet);			
	}

	public function testGetYMinValueDataSet(){
		$this->assertEquals(25, $this->bound->getYMinValue());
	}

	public function testGetYMaxValue(){
		$this->assertEquals(30, $this->bound->getYMaxValue());
	}	
}