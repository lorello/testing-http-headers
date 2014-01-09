<?php
/*
 * Simple app to test HTTP Headers and reverse-proxy behaviour
 *
 * For Response headers details:
 * http://api.symfony.com/master/Symfony/Component/HttpFoundation/Response.html
 *
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

# Create the app instance
$app = new Silex\Application();

# Turn on debugging
$app['debug'] = true;

# Useful in a clustered environment
$hostname=shell_exec('/bin/uname -n');

# HTTP Standard date format
$date= date(DATE_RFC822);

# I'll use this as fake body content
$app['body'] = "I'm your content, I've been generated on <strong>$date</strong> by host <strong>$hostname</strong>";


$app->get(
    '/',
    function () use ($app) {
        $homepage = '
        welcome!
        ';
        return Response::create($homepage, 200)
            ->setPublic()
            ->setExpires(new DateTime('+1 hour'));
    }
);

$app->get(
    '/expiration',
    function (Request $request) use ($app) {
        //$request->headers->get('name');
        return Response::create($app['body'], 200)
            ->setMaxAge(0)
            ->setSharedMaxAge(60);
    }
);


# Let Silex trust the X-Forwarded-For* headers:
Request::setTrustedProxies(array('127.0.0.1'));
$app->run();
