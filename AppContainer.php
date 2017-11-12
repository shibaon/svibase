<?php

namespace Svi;

abstract class AppContainer
{
    /** @var Application */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

}