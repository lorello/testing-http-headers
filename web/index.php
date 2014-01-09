<?php
/*
 * Simple app to test HTTP Headers and reverse-proxy behaviour
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

# Turn on debugging
$app['debug'] = false;

# Create the app instance
$app = new Silex\Application();

# Let Silex trust the X-Forwarded-For* headers:
Request::setTrustedProxies(array('127.0.0.1'));
$app->run();
