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
		$req = \vakata\http\Request::fromString("GET / HTTP/1.1");
		$this->assertEquals('1.1', $req->getProtocolVersion());
		$this->assertEquals('GET', $req->getMethod());
		$this->assertEquals(null, $req->getHeaderLine('Content-Type'));
		$this->assertEquals(false, $req->hasHeader('Content-Type'));
		$this->assertEquals(['Host'=>['localhost']], $req->getHeaders());
		$this->assertEquals('', $req->getBody(true));

		$this->assertEquals('text/html', $req->getPreferredResponseFormat());
		$req = $req->withHeader('Accept', "text/plain,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
		$this->assertEquals('text/plain', $req->getPreferredResponseFormat());
		$this->assertEquals('en', $req->getPreferredResponseLanguage());
		$this->assertEquals('bg', $req->getPreferredResponseLanguage('bg'));
		$req = $req->withHeader('Accept-Language', "es,en-US;q=0.7,en;q=0.3");
		$this->assertEquals('es', $req->getPreferredResponseLanguage());
		$this->assertEquals(false, $req->isCors());
		$this->assertEquals(false, $req->isAjax());
		$req = $req->withHeader('X-Requested-With', 'XMLHttpRequest');
		$this->assertEquals(true, $req->isAjax());
		$this->assertEquals(null, $req->getCookie('asdf'));
		$this->assertEquals('no', $req->getCookie('asdf', 'no'));
		$req = \vakata\http\Request::fromString("GET /?a.b=b.c HTTP/1.1\r\nCookie: asdf=1a<strong>2</strong>\r\n\r\n");
		$this->assertEquals('1a<strong>2</strong>', $req->getCookie('asdf'));
		$this->assertEquals(1, $req->getCookie('asdf', null, 'int'));
		$this->assertEquals('b.c', $req->getQuery('a.b'));
	}
}
