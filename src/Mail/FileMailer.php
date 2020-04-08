<?php

namespace Ryby\Mail;

use DateTime;
use Nette\InvalidStateException;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\SmartObject;

/**
 * Based on https://github.com/romanmatyus/FileMailer
 */
class FileMailer implements Mailer
{

	use SmartObject;

	/**
	 * Directory for storing message files
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
		$this->tempDir = $options['tempDir'];
	}

	/**
	 * Store mails to files.
	 * @param Message $message
	 * @param callable|null $pathCallback
	 * @return void
	 */
	public function send(Message $message, callable $pathCallback = null): void
	{
		$this->checkMailerRequirements();
		$content = $message->generateMessage();
		preg_match('/Message-ID: <(?<message_id>\w+)[^>]+>/', $content, $matches);
		$path = $this->tempDir . '/' . $this->prefix . $matches['message_id'];
		if ($this->extension) {
			$path .= '.' . $this->extension;
		}
		$this->history[] = $message;
		$bytes = @file_put_contents($path, $content);
		if (!$bytes) {
			throw new InvalidStateException(sprintf("Unable to write email to '%s'.", $path));
		}
		if ($pathCallback !== null) {
			$pathCallback($path);
		}
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

	public function clearHistory()
	{
		$this->history = [];
	}

	private function checkMailerRequirements()
	{
		if (!is_dir($this->tempDir)) {
			if (!@mkdir($this->tempDir, 0777, true)) {
				throw new \InvalidArgumentException(sprintf("Unable to create directory '%s'.", $this->tempDir));
			}
		}
	}
}
