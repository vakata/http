<?php
namespace vakata\http\test;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
	public function testCreate() {
		$res = new \vakata\http\Response();
		$this->assertEquals('application/json; charset=UTF-8', $res->setContentTypeByExtension('json')->getHeaderLine('Content-Type'));
		$this->assertEquals(false, $res->hasCache());
		$this->assertEquals(gmdate('D, d M Y H:i:s', strtotime('tomorrow')).' GMT', $res->cacheUntil('tomorrow')->getHeaderLine('Expires'));
		$this->assertEquals(true, $res->cacheUntil('tomorrow')->hasCache());
		$this->assertEquals('asdf', $res->setBody('asdf')->getBody(true));
	}
}
