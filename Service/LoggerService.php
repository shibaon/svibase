<?php

namespace Svi\Service;

use Svi\AppContainer;
use Svi\HttpBundle\Exception\AccessDeniedHttpException;
use Svi\HttpBundle\Exception\NotFoundHttpException;

class LoggerService extends AppContainer
{

	public function write($text, $logFile = 'error')
	{
		$file = fopen($this->app->getRootDir() . '/app/logs/' . $logFile . '.log', 'a');
		fwrite($file, date('Y-m-d [H:i:s]', time()) . ': ' . $text . "\n");
		fclose($file);
	}

	public function handleError($errno, $errstr, $errfile, $errline)
	{
		$errs = [
			E_ERROR => 'E_ERROR',
			E_WARNING => 'E_WARNING',
			E_PARSE => 'E_PARSE',
			E_NOTICE => 'E_NOTICE',
			E_CORE_ERROR => 'E_CORE_ERROR',
			E_CORE_WARNING => 'E_CORE_WARNING',
			E_COMPILE_ERROR => 'E_COMPILE_ERROR',
			E_COMPILE_WARNING => 'E_COMPILE_WARNING',
			E_USER_ERROR => 'E_USER_ERROR',
			E_USER_WARNING => 'E_USER_WARNING',
			E_USER_NOTICE => 'E_USER_NOTICE',
			E_STRICT => 'E_STRICT',
			E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
			E_DEPRECATED => 'E_DEPRECATED',
			E_USER_DEPRECATED => 'E_USER_DEPRECATED',
 		];

		$this->write($errs[$errno] . ': ' . $errstr . ' in ' . $errfile . ':' . $errline);

		return true;
	}

	public function handleException(\Throwable $e)
	{
	    if (!$this->app->isConsole()) {
            if ($e instanceof AccessDeniedHttpException || $e instanceof NotFoundHttpException) {
                return;
            }
        }
		$this->write(get_class($e) . ' with message "' . $e->getMessage() . '"' . ' in ' . $e->getFile() . ':' . $e->getLine()
			. "\nTrace: \n" . $e->getTraceAsString()
		);
	}

} 