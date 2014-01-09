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

# Useful in a clustered environment
$hostname=shell_exec('/bin/uname -n');

# HTTP Standard date format
$date= date(DATE_RFC822);

# I'll use this as fake body content
$app['body'] = "I'm your content, I've been generated on $date by host $hostname";


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
        return Response::create($body, 200)
            ->setMaxAge(0)
            ->setSharedMaxAge(60);
    }
);


# Let Silex trust the X-Forwarded-For* headers:
Request::setTrustedProxies(array('127.0.0.1'));
$app->run();
