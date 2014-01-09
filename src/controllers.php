<?php

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

$app->match('/', function() use ($app) {
    $app['session']->getFlashBag()->add('warning', 'Warning flash message');
    $app['session']->getFlashBag()->add('info', 'Info flash message');
    $app['session']->getFlashBag()->add('success', 'Success flash message');
    $app['session']->getFlashBag()->add('error', 'Error flash message');

    return $app['twig']->render('index.html.twig');
})->bind('homepage');

$app->match('/reverse_proxy', function() use ($app) {
    return $app['twig']->render('reverse_proxy.html.twig');
})->bind('reverse_proxy');

$app->match('/reverse_proxy/{slug}', function($slug) use ($app) {

    $datetime= date(DATE_RFC822);

    $sContent = $app['twig']->render('reverse_proxy.html.twig'
	, array(
	    'slug' => $slug
	    , 'datetime' => $datetime
	)
	);

    $oResponse = Response::create($sContent, 200);

    switch($slug) {
    case 'private':
	$oResponse->setPublic()
	    ->setExpires(new DateTime('+1 hour'));
	break;
    default:
	break;
    }

    return $oResponse;

})->bind('reverse_proxy_slug');

$app->get('/page-with-cache', function() use ($app) {
    $response = new Response($app['twig']->render('page-with-cache.html.twig', array('date' => date('Y-M-d h:i:s'))));
    $response->setTtl(10);

    return $response;
})->bind('page_with_cache');

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
    }

    return new Response($message, $code);
});

return $app;
