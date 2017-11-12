<?php

namespace Svi;

use Svi\Service\BundlesService;
use Svi\Service\ConfigService;
use Svi\Service\ConsoleService;
use Svi\Service\ExceptionService;
use Svi\Service\LoggerService;
use Svi\Service\TemplateService;
use Svi\Service\TranslationService;

class Application extends ArrayAccess
{
    private $instanceId;
    private $rootDir;

    public function __construct($config = null, array $argv = null)
    {
        $this->rootDir = getcwd();
        $this->instanceId = md5(time() . microtime() . rand());

        $this['console'] = !!$argv;

        $loader = require $this->getRootDir() . '/vendor/autoload.php';

        $loader->add('', $this->getRootDir() . '/src');

        $this[ConfigService::class] = new ConfigService($this, $config);
        $this['debug'] = $this->getConfigService()->get('debug');
        $this[LoggerService::class] = new LoggerService($this);

        $this[ExceptionService::class] = new ExceptionService($this);

        $this[TemplateService::class] = function () {
            return new TemplateService($this);
        };

        $this[TranslationService::class] = function(){
            return new TranslationService($this);
        };

        $this[BundlesService::class] = new BundlesService($this);

        if ($this->isConsole()) {
            $this[ConsoleService::class] = new ConsoleService($this, $argv);
        }
    }

    public function run()
    {
        if (!$this->isConsole()) {
            $this['Svi\HttpBundle\Service\HttpService']->run();
        } else {
            $this[ConsoleService::class]->run();
        }
    }

    /**
     * @return ConfigService
     */
    public function getConfigService()
    {
        return $this[ConfigService::class];
    }

    /**
     * @return BundlesService
     */
    public function getBundlesService()
    {
        return $this[BundlesService::class];
    }

    /**
     * @return TranslationService
     */
    public function getTranslationService()
    {
        return $this[TranslationService::class];
    }

    /**
     * @return LoggerService
     */
    public function getLoggerService()
    {
        return $this[LoggerService::class];
    }

    /**
     * @return string Always returns current site dir with "/" in the end, so like /var/www/sample/www/sample.com/ will be returned
     */
    public function getRootDir()
    {
        return $this->rootDir;
    }

    /**
     * @return TemplateService
     */
    public function getTemplateService()
    {
        return $this[TemplateService::class];
    }

    public function isConsole()
    {
        return $this['console'];
    }

    public function getInstanceId()
    {
        return $this->instanceId;
    }

    public function error($callback, $prepend = false)
    {
        $this[ExceptionService::class]->error($callback, $prepend);
    }

}