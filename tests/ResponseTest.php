<?php
namespace vakata\http\test;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass() {
	}
	public static function tearDownAfterClass() {
	}
	protected function setUp() {
	}
	protected function tearDown() {
	}

	public function testCreate() {
		$res = new \vakata\http\Response();
		$this->assertEquals(true, $res instanceof \vakata\http\Response);
	}
}
