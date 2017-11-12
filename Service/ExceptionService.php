<?php

namespace Svi\Service;

use Svi\AppContainer;
use Svi\Application;
use Svi\Exception\CompileErrorException;
use Svi\Exception\CoreErrorException;
use Svi\Exception\CoreWarningException;
use Svi\Exception\DeprecatedException;
use Svi\Exception\NoticeException;
use Svi\Exception\ParseException;
use Svi\Exception\RecoverableErrorException;
use Svi\Exception\StrictException;
use Svi\Exception\UserDeprecatedException;
use Svi\Exception\UserErrorException;
use Svi\Exception\UserNoticeException;
use Svi\Exception\UserWarningException;
use Svi\Exception\WarningException;
use Svi\HttpBundle\Exception\AccessDeniedHttpException;
use Svi\HttpBundle\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

class ExceptionService extends AppContainer
{
    private $handlers = [];

    public function __construct(Application $app)
    {
        parent::__construct($app);

        set_exception_handler([$this, 'handler']);
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            switch ($errno) {
                case E_ERROR:
                    throw new \ErrorException($errstr, $errno, 1, $errfile, $errline);
                case E_WARNING:
                    throw new WarningException($errstr, $errno, 1, $errfile, $errline);
                case E_PARSE:
                    throw new ParseException($errstr, $errno, 1, $errfile, $errline);
                case E_NOTICE:
                    throw new NoticeException($errstr, $errno, 1, $errfile, $errline);
                case E_CORE_ERROR:
                    throw new CoreErrorException($errstr, $errno, 1, $errfile, $errline);
                case E_CORE_WARNING:
                    throw new CoreWarningException($errstr, $errno, 1, $errfile, $errline);
                case E_COMPILE_ERROR:
                    throw new CompileErrorException($errstr, $errno, 1, $errfile, $errline);
                case E_COMPILE_WARNING:
                    throw new CoreWarningException($errstr, $errno, 1, $errfile, $errline);
                case E_USER_ERROR:
                    throw new UserErrorException($errstr, $errno, 1, $errfile, $errline);
                case E_USER_WARNING:
                    throw new UserWarningException($errstr, $errno, 1, $errfile, $errline);
                case E_USER_NOTICE:
                    throw new UserNoticeException($errstr, $errno, 1, $errfile, $errline);
                case E_STRICT:
                    throw new StrictException($errstr, $errno, 1, $errfile, $errline);
                case E_RECOVERABLE_ERROR:
                    throw new RecoverableErrorException($errstr, $errno, 1, $errfile, $errline);
                case E_DEPRECATED:
                    throw new DeprecatedException($errstr, $errno, 1, $errfile, $errline);
                case E_USER_DEPRECATED:
                    throw new UserDeprecatedException($errstr, $errno, 1, $errfile, $errline);
                default:
                    throw new \ErrorException();
            }
        }, E_ALL);
    }

    public function handler(\Throwable $e)
    {
        $this->app->getLoggerService()->handleException($e);

        foreach ($this->handlers as $handler) {
            $reflection = new \ReflectionFunction($handler);
            $class = $reflection->getParameters()[0]->getClass();
            if ($class->isInstance($e)) {
                $request = $this->app->isConsole() ? null : $this->app['Svi\HttpBundle\Service\HttpService']->getRequest();

                if ($result = $handler($e, $request)) {
                    if ($this->app->isConsole()) {
                        print $result;
                    } else {
                        if (!($result instanceof Response)) {
                            $result = Response::create($result);
                        }
                        $result->prepare($request)->send();
                    }

                    return;
                }
            }
        }

        if ($this->app['debug']) {
            $this->renderException($e);
        }
    }

    public function renderException(\Throwable $e)
    {
        if ($this->app->isConsole()) {
            print_r($e);
        } else {
            $root = $this->app->getRootDir();
            ob_start();
            include __DIR__ . '/./ExceptionService/exception_template.php';
            $content = ob_get_contents();
            ob_end_clean();

            $code = 500;
            if ($e instanceof NotFoundHttpException) {
                $code = 404;
            } elseif ($e instanceof AccessDeniedHttpException) {
                $code = 403;
            }

            Response::create($content, $code)->send();
        }
    }

    public function error($callback, $prepend = false)
    {
        if ($prepend) {
            array_unshift($this->handlers, $callback);
        } else {
            $this->handlers[] = $callback;
        }
    }

}