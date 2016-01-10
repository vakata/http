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
		$this->assertEquals('1.1', $req->getProtocolVersion());
		$this->assertEquals('1.0', $req->setProtocolVersion('1.0')->getProtocolVersion());
		$this->assertEquals('GET', $req->getMethod());
		$this->assertEquals('POST', $req->setMethod('POST')->getMethod());
		$this->assertEquals(null, $req->getHeader('Content-Type'));
		$this->assertEquals(false, $req->hasHeader('Content-Type'));
		$this->assertEquals('test', $req->setHeader('Content-Type', 'test')->getHeader('Content-Type'));
		$this->assertEquals(true, $req->hasHeader('Content-Type'));
		$req->removeHeader('Content-Type');
		$this->assertEquals(null, $req->getHeader('Content-Type'));
		$this->assertEquals(false, $req->hasHeader('Content-Type'));
		$req->setHeader('Content-Type', 'test');
		$this->assertEquals(true, $req->hasHeader('Content-Type'));
		$this->assertEquals(['Content-Type' => 'test'], $req->getHeaders());
		$req->removeHeaders();
		$this->assertEquals([], $req->getHeaders());
		$this->assertEquals(null, $req->getBody());
		$this->assertEquals('', $req->getBody(true));
		$stream = fopen('php://temp', 'r+');
		fwrite($stream, 'asdf');
		$this->assertEquals('asdf', $req->setBody($stream)->getBody(true));
		$this->assertEquals($stream, $req->getBody());
		$this->assertEquals('test', $req->setBody('test')->getBody(true));
		$this->assertEquals(null, $req->getUrl());
		$this->assertEquals('http://domain.tld/', (string)$req->setUrl('http://domain.tld/')->getUrl());
		$this->assertEquals(false, $req->hasUploads());
		$this->assertEquals([], $req->getUploads());
		$this->assertEquals(false, $req->hasUpload('test'));
		$this->assertEquals(null, $req->getUpload('test'));
		$this->assertEquals(true, $req->addUpload('test', 'asdf', 'asdf.txt')->hasUploads());
		$this->assertEquals(true, $req->hasUpload('test'));
		$req->removeUpload('test');
		$this->assertEquals(false, $req->hasUpload('test'));
		$this->assertEquals(false, $req->hasUploads());
		$up = new \vakata\http\Upload('asdf.txt', null, 'asdf');
		$this->assertEquals(true, $req->addUpload('test', $up)->hasUpload('test'));
		$this->assertEquals($up, $req->getUpload('test'));
		$req->removeUploads();
		$this->assertEquals(false, $req->hasUpload('test'));
		$this->assertEquals(false, $req->hasUploads());
		$this->assertEquals(null, $req->getAuthorization());
		$this->assertEquals(['token'=>'asdf'], $req->setHeader('Authorization', 'bearer asdf')->getAuthorization());
		$this->assertEquals(['username'=>'user','password'=>'pass'], $req->setHeader('Authorization', 'Basic ' . base64_encode('user:pass'))->getAuthorization());
		$this->assertEquals('text/html', $req->getPreferedResponseFormat());
		$this->assertEquals(null, $req->getPreferedResponseFormat(null));
		$req->setHeader('Accept', "text/plain,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8");
		$this->assertEquals('text/plain', $req->getPreferedResponseFormat());
		$this->assertEquals('en', $req->getPreferedResponseLanguage());
		$this->assertEquals('bg', $req->getPreferedResponseLanguage('bg'));
		$req->setHeader('Accept-Language', "es,en-US;q=0.7,en;q=0.3");
		$this->assertEquals('es', $req->getPreferedResponseLanguage());
		$this->assertEquals(false, $req->isCors());
		$req->setHeader('Origin', 'other.tld');
		$this->assertEquals(true, $req->isCors());
		$this->assertEquals(false, $req->isAjax());
		$req->setHeader('X-Requested-With', 'XMLHttpRequest');
		$this->assertEquals(true, $req->isAjax());
		$this->assertEquals(null, $req->getCookie('asdf'));
		$this->assertEquals('no', $req->getCookie('asdf', 'no'));
		$req->setHeader('Cookie', 'asdf=1a<strong>2</strong>');
		$this->assertEquals('1a<strong>2</strong>', $req->getCookie('asdf'));
		$this->assertEquals(1, $req->getCookie('asdf', null, 'int'));
		$this->assertEquals(1.0, $req->getCookie('asdf', null, 'float'));
		$this->assertEquals('1a2', $req->getCookie('asdf', null, 'nohtml'));
		$this->assertEquals('1a&lt;strong&gt;2&lt;/strong&gt;', $req->getCookie('asdf', null, 'escape'));
		$this->assertEquals(null, $req->getQuery('asdf'));
		$this->assertEquals('no', $req->getQuery('asdf', 'no'));
		$req->getUrl()->setQuery('asdf=yes&asdf2=moreso');
		$this->assertEquals('yes', $req->getQuery('asdf'));
		$this->assertEquals(['asdf'=>'yes','asdf2'=>'moreso'], $req->getQuery());
		$this->assertEquals(null, $req->getPost('asdf'));
		$this->assertEquals('no', $req->getPost('asdf', 'no'));
		$req->setBody('asdf=yes&asdf2=moreso');
		$this->assertEquals('yes', $req->getPost('asdf'));
		$this->assertEquals(['asdf'=>'yes','asdf2'=>'moreso'], $req->getPost());
	}
}
