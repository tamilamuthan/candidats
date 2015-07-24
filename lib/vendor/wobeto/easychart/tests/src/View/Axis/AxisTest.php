<?php 

use Wobeto\EasyChart\View\Axis\Axis;

class AxisTest extends \PHPUnit_Framework_Testcase{

	private $axis;

	public function __construct(){
		$this->axis = new Axis(5, 8);

		$this->axis->computeBoundaries();
	}

	public function testGetLowerBondary(){		
		$this->assertEquals(5, $this->axis->getLowerBoundary());
	}

	public function testGetUpperBoundary(){		
		$this->assertEquals(8, $this->axis->getUpperBoundary());
	}

	public function testGetTics(){
		$this->assertEquals(0.5, $this->axis->getTics());
	}

}