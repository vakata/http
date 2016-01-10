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
		$this->assertEquals('1.1', $res->getProtocolVersion());
		$this->assertEquals('1.0', $res->setProtocolVersion('1.0')->getProtocolVersion());
		$this->assertEquals(null, $res->getHeader('Content-Type'));
		$this->assertEquals(false, $res->hasHeader('Content-Type'));
		$this->assertEquals('test', $res->setHeader('Content-Type', 'test')->getHeader('Content-Type'));
		$this->assertEquals(true, $res->hasHeader('Content-Type'));
		$res->removeHeader('Content-Type');
		$this->assertEquals(null, $res->getHeader('Content-Type'));
		$this->assertEquals(false, $res->hasHeader('Content-Type'));
		$res->setHeader('Content-Type', 'test');
		$this->assertEquals(true, $res->hasHeader('Content-Type'));
		$this->assertEquals(['Content-Type' => 'test', 'Status' => '200 OK'], $res->getHeaders());
		$res->removeHeaders();
		$this->assertEquals([], $res->getHeaders());
		$this->assertEquals(null, $res->getBody());
		$this->assertEquals('', $res->getBody(true));
		$stream = fopen('php://temp', 'r+');
		fwrite($stream, 'asdf');
		$this->assertEquals('asdf', $res->setBody($stream)->getBody(true));
		$this->assertEquals($stream, $res->getBody());
		$this->assertEquals('test', $res->setBody('test')->getBody(true));
		$this->assertEquals(200, $res->getStatusCode());
		$this->assertEquals(404, $res->setStatusCode(404)->getStatusCode());
		$this->assertEquals('application/json; charset=UTF-8', $res->setContentTypeByExtension('json')->getHeader('Content-Type'));
		$this->assertEquals(gmdate('D, d M Y H:i:s', strtotime('tomorrow')).' GMT', $res->cacheUntil('tomorrow')->getHeader('Expires'));
	}
}
