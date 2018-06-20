<?php

namespace Ryby\Mail\Tests;

use Nette\InvalidStateException;
use Nette\Mail\Message;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Ryby\Mail\FileMailer;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
class FileMailerTest extends TestCase
{

	/**
	 * @var vfsStreamDirectory
	 */
	private $root;

	protected function setUp()
	{
		parent::setUp();
		$this->root = vfsStream::setup('temp');
	}

	public function testCreateTempDirectory()
	{
		$fileMailer = new FileMailer(['extension' => 'eml', 'tempDir' => vfsStream::url('temp/mails')]);
		$fileMailer->send(new Message());
		Assert::true($this->root->hasChild('temp/mails'));
	}

	public function testSend()
	{
		$tempDir = vfsStream::url('temp/mails');
		mkdir($tempDir);
		$fileMailer = new FileMailer(['extension' => 'eml', 'tempDir' => $tempDir]);
		$message = new Message();
		$message
			->setSubject('last warning')
			->setFrom('obi-wan@tatooine.com')
			->addTo('anakin@deathstar.com')
			->setBody('I have the high ground!');
		$filename = $fileMailer->send($message);
		Assert::type('string', $filename);
		Assert::null($fileMailer->findBySubject(''));
		Assert::equal($message, $fileMailer->findBySubject('last warning'));
		Assert::match('%A?%I have the high ground!%A?%', file_get_contents($filename));
		$fileMailer->clearHistory();
		Assert::equal(0, count($fileMailer->getHistory()));
	}

	public function testUnableToCreateDirectoryDueToExistingFile()
	{
		Assert::exception(
			function () {
				$path = vfsStream::url('temp/mails');
				file_put_contents($path, 'contens');
				$fileMailer = new FileMailer(['extension' => 'eml', 'tempDir' => $path]);
				$fileMailer->send(new Message());
			},
			\InvalidArgumentException::class,
			"Unable to create directory 'vfs://temp/mails'."
		);
	}

	public function testUnableToCreateDirectoryDueToAccessRigths()
	{
		Assert::exception(
			function () {
				$path = vfsStream::url('temp');
				chmod($path, 000);
				$fileMailer = new FileMailer(['extension' => 'eml', 'tempDir' => $path]);
				$fileMailer->send(new Message());
			},
			InvalidStateException::class,
			"Unable to write email to 'vfs://temp/%a%'."
		);
	}
}

(new FileMailerTest())->run();
