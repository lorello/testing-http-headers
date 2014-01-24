<?php
/*
 * Simple app to test HTTP Headers and reverse-proxy behaviour
 *
 * For Response headers details:
 * http://api.symfony.com/master/Symfony/Component/HttpFoundation/Response.html
 *
 */

date_default_timezone_set('Europe/Rome');

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

require __DIR__.'/../resources/config/prod.php';
require __DIR__.'/../src/app.php';
require __DIR__.'/../src/controllers.php';

$app['http_cache']->run();
