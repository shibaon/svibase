<?php

namespace Svi\Service\ConsoleService;

use Svi\AppContainer;
use Svi\Application;

abstract class ConsoleCommand extends AppContainer
{

	abstract public function getName();

	abstract public function getDescription();

	abstract public function execute(array $args);

	/**
	 * @return Application
	 */
	protected function getApp()
	{
		return $this->app;
	}

	protected function write($text)
	{
		print $text;
	}

	protected function writeLn($text = '')
	{
		print $text . "\n    ";
	}

} 