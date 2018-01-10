<?php
namespace vakata\http\test;

class UriTest extends \PHPUnit_Framework_TestCase
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
		$uri = new \vakata\http\Uri('https://user:pass@domain.tld:8080/path/to/file.html?query=string#fragment', '/path/');
		$this->assertEquals('to', $uri->getSegment(0));
		$this->assertEquals('to/file.html', $uri->getRealPath());
		$this->assertEquals('/path/', $uri->getBasePath());
		$this->assertEquals('/path/to/file.html?query=string', $uri->self());
		$this->assertEquals('/path/asdf', $uri->linkTo('asdf'));
		$this->assertEquals('/asdf', $uri->linkTo('/asdf'));
		$this->assertEquals('/path/asdf?a.b=b.c', $uri->linkTo('asdf', ['a.b'=>'b.c']));
	}
}
