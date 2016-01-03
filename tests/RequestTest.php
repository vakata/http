<?php
namespace vakata\http\test;

class RequestTest extends \PHPUnit_Framework_TestCase
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
		$req = new \vakata\http\Request();
		$this->assertEquals(true, $req instanceof \vakata\http\Request);
	}
}
