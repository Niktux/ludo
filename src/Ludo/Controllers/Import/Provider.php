<?php

namespace Ludo\Controllers\Import;

use Silex\Application;
use Silex\ControllerProviderInterface;

class Provider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $app['import.controller'] = $app->share(function() use($app) {
            return $app->initializeController(new Controller());
        });

        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'import.controller:homeAction')->bind('import_home');
        $controllers->post('/search', 'import.controller:searchAction')->bind('import_search');
        $controllers->get('/bgg/import/{gameId}', 'import.controller:bggImportAction')
                    ->assert('\d+', 'gameId')
                    ->bind('import_bgg');
        
        return $controllers;
    }
}