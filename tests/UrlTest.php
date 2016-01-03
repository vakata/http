<?php
namespace vakata\http\test;

class UrlTest extends \PHPUnit_Framework_TestCase
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
		$url = new \vakata\http\Url();
		$this->assertEquals(true, $url instanceof \vakata\http\Url);
	}
}
