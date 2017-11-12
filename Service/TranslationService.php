<?php

namespace Svi\Service;

use Svi\AppContainer;
use Svi\Application;

class TranslationService extends AppContainer
{
	private $locale;
	private $translations;

	public function __construct(Application $app)
	{
		parent::__construct($app);

		$this->locale = strtolower($app->getConfigService()->get('locale'));
	}

	public function trans($string, array $params = [])
	{
		$this->loadTranslations();

		$result = array_key_exists($string, $this->translations) ? $this->translations[$string] : $string;

		return count($params) ? str_replace(array_keys($params), $params, $result) : $result;
	}

	protected function loadTranslations()
	{
		if (isset($this->translations)) {
			return null;
		}

		if (!$this->app->getConfigService()->get('debug')) {
			$cacheFile = $this->app->getRootDir().'/app/cache/translations_' . $this->locale . '.php';
			if (file_exists($cacheFile)) {
				$this->translations = include $cacheFile;
			} else {
				$this->translations = $this->getTranslationsFromBundles();
				$cache = "<?php\n return [\n";
				foreach ($this->translations as $key => $value) {
					$cache .= "'$key' => '" . str_replace(array('\\', "'"), array('\\\\', "\\'"), $value) . "',\n";
				}
				$cache .= '];';
				$file =fopen($cacheFile, 'w');
				fwrite($file, $cache);
				fclose($file);
			}
		} else {
			$this->translations = $this->getTranslationsFromBundles();
		}

		return null;
	}

	protected function getTranslationsFromBundles()
	{
	    $result = [];

        $getPairs = function($key, array $value) use (&$getPairs) {
            $result = array();
            foreach ($value as $k => $v) {
                $k = $key . '.' . $k;
                if (!is_array($v)) {
                    $result[$k] = $v;
                } else {
                    $result = array_merge($result, $getPairs($k, $v));
                }
            }

            return $result;
        };

		foreach ($this->app->getBundlesService()->getBundles() as $b) {
			foreach ($b->getTranslations($this->locale) as $key => $value) {
			    if (!is_array($value)) {
			        $result[$key] = $value;
                } else {
                    $result = array_merge($result, $getPairs($key, $value));
                }
            }
		}

		return $result;
	}

} 