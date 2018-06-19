<?php

namespace Ryby\Mail\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;
use Nette\Mail\IMailer;
use Ryby\Mail\FileMailer;

class FileMailerExtension extends CompilerExtension
{

	public $defaults = [
		'tempDir' => '%tempDir%/mails',
		'extension' => 'eml',
	];

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		$this->validateConfig($this->defaults);
		$config = $this->getConfig($this->defaults);

		foreach ($builder->findByType(IMailer::class) as $name => $def) {
			$builder->removeDefinition($name);
		}

		$builder->addDefinition('fileMailer')
				->setClass(FileMailer::class, [$config])
				->addSetup('$tempDir', [
					Helpers::expand($config['tempDir'], $builder->parameters)
		]);
	}

}
