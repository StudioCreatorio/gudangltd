<?php

namespace NinjaTablesPro\App;

class Application
{
    public function __construct($app)
    {
        $this->boot($app);
    }

    public function boot($app)
    {
        $router = $app->router;
        require_once NINJAPROPLUGIN_PATH . 'app/Hooks/actions.php';
        require_once NINJAPROPLUGIN_PATH . 'app/Hooks/filters.php';
        require_once NINJAPROPLUGIN_PATH . 'app/Http/Routes/api.php';
        require_once NINJAPROPLUGIN_PATH . 'boot/ninja-tables-pro-global-functions.php';
    }
}
