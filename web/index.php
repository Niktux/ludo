<?php

require __DIR__ . '/../vendor/autoload.php';

$config = new Firenote\Configuration\Yaml(__DIR__ . '/../config');
$app = new Ludo\Application($config, __DIR__ . '/..');

$app->enableDebug()->enableProfiling();

$app->mountProviders();
$app->initializeAdminLayout();
                
$app->run();