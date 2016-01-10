<?php
namespace vakata\http\test;

class UploadTest extends \PHPUnit_Framework_TestCase
{
	private static $dir;

	public static function setUpBeforeClass() {
		self::$dir = __DIR__ . DIRECTORY_SEPARATOR . 'up';
		mkdir(self::$dir);
	}
	public static function tearDownAfterClass() {
		foreach (scandir(self::$dir) as $file) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			if (is_file(self::$dir.DIRECTORY_SEPARATOR.$file)) {
				unlink(self::$dir.DIRECTORY_SEPARATOR.$file);
			}
		}
		rmdir(self::$dir);
	}
	protected function setUp() {
	}
	protected function tearDown() {
	}

	public function testCreate() {
		$up = new \vakata\http\Upload();

		$this->assertEquals('', $up->getName());
		$this->assertEquals(false, $up->hasSize());
		$this->assertEquals(null, $up->getSize());
		$this->assertEquals(null, $up->getPath());
		$this->assertEquals(null, $up->getBody());
		$this->assertEquals('', $up->getBody(true));

		$this->assertEquals('asdf.txt', $up->setName('asdf.txt')->getName());

		file_put_contents(self::$dir . DIRECTORY_SEPARATOR . 'asdf.txt', 'asdf');
		$this->assertEquals(self::$dir . DIRECTORY_SEPARATOR . 'asdf.txt', $up->setPath(self::$dir . DIRECTORY_SEPARATOR . 'asdf.txt')->getPath());
		$this->assertEquals(true, $up->hasSize());
		$this->assertEquals(4, $up->getSize());
		$this->assertEquals('asdf', $up->getBody(true));

		$stream = fopen('php://temp', 'r+');
		fwrite($stream, 'asdf');
		$this->assertEquals('asdf', $up->setBody($stream)->getBody(true));
		$this->assertEquals(null, $up->getPath());
		$this->assertEquals($stream, $up->getBody());
		$this->assertEquals('test', $up->setBody('test')->getBody(true));
		$this->assertEquals(null, $up->getPath());
		$up->saveAs(self::$dir . DIRECTORY_SEPARATOR . 'test.txt');
		$this->assertEquals('test', file_get_contents(self::$dir . DIRECTORY_SEPARATOR . 'test.txt'));
	}
}
