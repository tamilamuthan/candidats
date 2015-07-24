<?php 

use Wobeto\EasyChart\Model\XYSeriesDataSet;
use Wobeto\EasyChart\Model\XYDataSet;
use Wobeto\EasyChart\Model\Point;

class XYSeriesDataSetTest extends \PHPUnit_Framework_Testcase{

	private $point;
	private $dataSet;
	private $seriesDataSet;

	public function __construct(){
		$this->point         =  new Point(2014, 54);
		$this->dataSet       = new XYDataSet();

		$this->dataSet->addPoint($this->point);

		$this->seriesDataSet = new XYSeriesDataSet();
		
		$this->seriesDataSet->addSerie('Years', $this->dataSet);
	}

	public function testGetTitlesSeries(){
		$this->assertInternalType('array', $this->seriesDataSet->getTitleList());
	}

	public function testTotalTitleSeries(){
		$this->assertEquals(1, count($this->seriesDataSet->getTitleList()));
	}

	public function testFirstTitleSerie(){
		$titles = $this->seriesDataSet->getTitleList();
		$first = $titles[0];
		$this->assertEquals('Years', $first);
	}

	public function testGetSeriesList(){
		$this->assertInternalType('array', $this->seriesDataSet->getSerieList());
	}

	public function testTotalSeriesList(){
		$this->assertEquals(1, count($this->seriesDataSet->getSerieList()));
	}

	public function testFirstSerie(){
		$series = $this->seriesDataSet->getSerieList();
		$first = $series[0];
		$this->assertInstanceOf('Wobeto\EasyChart\Model\XYDataSet', $first);
	}

}