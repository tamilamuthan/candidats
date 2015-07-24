<?php 

use Wobeto\EasyChart\Model\Point;

class PointTest extends \PHPUnit_Framework_Testcase{
 
	public function testHasX(){
		$Point = new Point(2014, 33);
		$this->assertEquals("2014", $Point->getX());
	}


	public function testHasY(){
		$Point = new Point(2014, 33);
		$this->assertEquals("33", $Point->getY());
	}

}