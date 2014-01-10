<?php

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

$app->match('/', function() use ($app) {
/*
    $app['session']->getFlashBag()->add('warning', 'Warning flash message');
    $app['session']->getFlashBag()->add('info', 'Info flash message');
    $app['session']->getFlashBag()->add('success', 'Success flash message');
    $app['session']->getFlashBag()->add('error', 'Error flash message');
*/

    return $app['twig']->render('index.html.twig'
	, array(
	    'section' => 'homepage'
	)
	);
})->bind('homepage');

$app->match('/reverse_proxy/{slug}', function($slug) use ($app) {

    $oRequest = Request::createFromGlobals();
    $oResponse = Response::create('', 200);

    switch($slug) {
    case 'private':
	$oResponse->setPublic()
	    ->setExpires(new DateTime('+1 hour'));
	break;
    default:
	break;
    }


    $datetime= date(DATE_RFC822);
    $sContent = $app['twig']->render('reverse_proxy.html.twig'
	, array(
	    'section' => 'reverse_proxy'
	    , 'slug' => $slug
	    , 'datetime' => $datetime
	    , 'request_headers' => $oRequest->headers->__toString()
	    , 'response_headers' => $oResponse->headers->__toString()
	)
	);
    $oResponse->setContent($sContent);


    return $oResponse;

})->value('slug', 'private')->bind('reverse_proxy_slug');

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
