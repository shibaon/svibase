<?php

namespace Svi\Service;

use Svi\AppContainer;
use Svi\Application;
use Svi\Service\BundlesService\Bundle;

class BundlesService extends AppContainer
{
	private $bundles;

	public function __construct(Application $app)
	{
	    parent::__construct($app);

		$this->app = $app;
		foreach ($app->getConfigService()->get('bundles') as $bundleName) {
			/** @var Bundle $bundle */
			$bundle = new $bundleName($app);
			if (!($bundle instanceof Bundle)) {
			    throw new \Exception("Class $bundleName doesn't extends " . Bundle::class);
            }
			$this->bundles[] = $bundle;
			$app[$bundleName] = $bundle;
		}
	}

	/**
	 * @return Bundle[]
	 */
	public function getBundles()
	{
		return $this->bundles;
	}

	public function getCommandClasses()
	{
		$result = [];
		/** @var Bundle $b */
		foreach ($this->bundles as $b) {
			$result = array_merge($result, $b->getCommandClasses());
		}

		return $result;
	}

}