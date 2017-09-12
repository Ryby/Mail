<?php

namespace Ryby\Mail;

use DateTime;
use Nette\FileNotFoundException;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Object;

/**
 * Based on https://github.com/romanmatyus/FileMailer
 */
class FileMailer extends Object implements IMailer
{

	/**
	 * Temp dir
	 * @var string
	 */
	private $tempDir;

	/**
	 * Message file prefix
	 * @var string
	 */
	private $prefix;

	/**
	 * Message file extension
	 * @var string
	 */
	private $extension;

	/**
	 * @var Message[]
	 */
	private $history = [];

	public function __construct(array $options = [])
	{
		$now = new DateTime;
		$this->prefix = $now->format("YmdHis") . '-';
		$this->extension = $options['extension'];
	}

	/**
	 * Store mails to files.
	 * @param Message $message
	 */
	public function send(Message $message)
	{
		$this->checkRequirements();
		$content = $message->generateMessage();
		preg_match('/Message-ID: <(?<message_id>\w+)[^>]+>/', $content, $matches);
		$path = $this->tempDir . '/' . $this->prefix . $matches['message_id'];
		if ($this->extension) {
			$path .= '.' . $this->extension;
		}
		$this->history[] = $message;
		$bytes = file_put_contents($path, $content);
		if ($bytes) {
			return $bytes;
		} else {
			throw new InvalidStateException("Unable to write email to '$path'");
		}
	}

	public function setTempDir($tempDir)
	{
		$this->tempDir = $tempDir;
	}

	public function findBySubject($subject)
	{
		foreach ($this->getHistory() as $message) {
			if ($message->getSubject() === $subject) {
				return $message;
			}
		}
		return NULL;
	}

	/**
	 * @return Message[]
	 */
	public function getHistory()
	{
		return $this->history;
	}

	private function checkRequirements()
	{
		if (is_null($this->tempDir)) {
			throw new InvalidArgumentException("Directory for temporary files is not defined.");
		}
		if (!is_dir($this->tempDir)) {
			mkdir($this->tempDir);
			if (!is_dir($this->tempDir)) {
				throw new FileNotFoundException("'$this->tempDir' is not directory.");
			}
		}
		if (!is_writable($this->tempDir)) {
			throw new InvalidArgumentException("Directory '$this->tempDir' is not writeable.");
		}
	}
}
