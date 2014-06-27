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

/**
 * http://symfony.com/it/doc/current/book/http_cache.html
 *   DONE /expiration/cc/(private|public|max-age|s-maxage|mix)
 *   DONE /expiration/expire
 *   /validation/last-modified
 *   /validation/etag
 */
$app->match('/reverse-proxy/{slug}', function($slug) use ($app) {

    $request = Request::createFromGlobals();
    $response = Response::create('', 200);
    $lesson_title = 'RFC2616 says...';
    $lesson = 'nothing';
    $lesson_link = 'http://www.w3.org/Protocols/rfc2616/';

    switch($slug) {
	case 'exp-basic':
		$response->setExpires(new DateTime('+30 seconds'));
		$lesson_title = 'Basic method to manage cache time';
		$lesson = 'The Expires entity-header field gives the date/time after which the response is considered stale. A stale cache entry may not normally be returned by a cache (either a proxy cache or a user agent cache) unless it is first validated with the origin server (or with an intermediate cache that has a fresh copy of the entity). See section <a href="http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html#sec13.2">13.2</a> for further discussion of the expiration model.

The presence of an Expires field does not imply that the original resource will change or cease to exist at, before, or after that time.';
		$lesson_link = 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.21';
		break;


	case 'exp-cc-private':
		$response->setPrivate();
		$lesson_title = 'Cachable by user-agent caches';
		$lesson = "Indicates that all or part of the response message is intended for a single user and MUST NOT be cached by a shared cache. This allows an origin server to state that the specified parts of the
response are intended for only one user and are not a valid response for requests by other users. A private (non-shared) cache MAY cache the response.";
		$lesson_link = 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9.1';
		break;

	case 'exp-cc-public':
		$response->setPublic();
		$lesson_title = 'Cachable by shared caches (CDN, Varnish, Nginx, ...)';
		$lesson = "Indicates that the response MAY be cached by any cache, even if it would normally be non-cacheable or cacheable only within a non- shared cache. (See also Authorization, section 14.8, for additional details.)";
		$lesson_link = 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9.1';
		break;

	case 'exp-cc-nocache':
		$response->headers->addCacheControlDirective('no-cache');
		$lesson_title = 'Nothing is cachable';
		$lesson = "If the no-cache directive does not specify a field-name, then a cache MUST NOT use the response to satisfy a subsequent request without successful revalidation with the origin server. This allows an origin server to prevent caching even by caches that have been configured to return stale responses to client requests.
If the no-cache directive does specify one or more field-names, then a cache MAY use the response to satisfy a subsequent request, subject to any other restrictions on caching. However, the specified field-name(s) MUST NOT be sent in the response to a subsequent request without successful revalidation with the origin server. This allows an origin server to prevent the re-use of certain header fields in a response, while still allowing caching of the rest of the response.";
		$lesson_link = 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9.1';
		break;

	case 'exp-cc-maxage':
		$response->setMaxAge(30)->setPublic();
		# maxage implictly set the content as private
		$lesson_title = 'Modifications of the Basic Expiration Mechanism';
		$lesson = 'Indicates that the client is willing to accept a response whose age is no greater than the specified time in seconds. Unless max- stale directive is also included, the client is not willing to accept a stale response.

The expiration time of an entity MAY be specified by the origin server using the Expires header (see section <a href="http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.21">14.21</a>). Alternatively, it MAY be specified using the max-age directive in a response. When the max-age cache-control directive is present in a cached response, the response is stale if its current age is greater than the age value given (in seconds) at the time of a new request for that resource. The max-age directive on a response implies that the response is cacheable (i.e., "public") unless some other, more restrictive cache directive is also present.';

		$lesson_link = 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9.1';
		break;

	case 'exp-cc-smaxage':
		$response->setTtl(30);
		$lesson_title = 'Modifications of the Basic Expiration Mechanism';
		$lesson = 'If a response includes an s-maxage directive, then for a shared cache (but not for a private cache), the maximum age specified by this directive overrides the maximum age specified by either the max-age directive or the Expires header. The s-maxage directive also implies the semantics of the proxy-revalidate directive (see section <a href="http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9.4">14.9.4</a>), i.e., that the shared cache must not use the entry after it becomes stale to respond to a subsequent request without first revalidating it with the origin server. The s- maxage directive is always ignored by a private cache.';
		$lesson_link = 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9.1';
		# shared-maxage implictly set the content as public
		break;

	case 'exp-cc-mixed':
		$response->setExpires(new DateTime('+300 seconds'))->setMaxAge(30)->setSharedMaxAge(90);
		$lesson_title = 'Mixing expiration headers';
		$lesson = 'If a response includes both an Expires header and a max-age directive, the max-age directive overrides the Expires header, even if the Expires header is more restrictive. This rule allows an origin server to provide, for a given response, a longer expiration time to an HTTP/1.1 (or later) cache than to an HTTP/1.0 cache. This might be useful if certain HTTP/1.0 caches improperly calculate ages or expiration times, perhaps due to desynchronized clocks.';
		$lesson_link = 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9.1';
		break;

  case 'val-etag':
    $response->setETag(md5($response->getContent()));
    $response->setPublic(); // make sure the response is public/cacheable
    $response->isNotModified($request);

    # $response->headers->addCacheControlDirective('must-revalidate', true);
		$lesson_title = 'Validation Model: Entity Tag cache validators';
    $lesson = '';
		$lesson_link = 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html#sec13.3';
		break;

  case 'val-lastmod':
    $date = new \DateTime('+20 seconds');
    $response->setLastModified($date);
    $response->setPublic();
    if ($response->isNotModified($request)) {
        return $response;
    }

		$lesson_title = 'Validation Model: Last-modified cache validator';
    $lesson = '';
		$lesson_link = 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec13.html#sec13.3';
		break;



	default:
		$lesson = 'You should not be here!';
		$lesson_link = 'http://silex.sensiolabs.org';
		break;
    }


    $datetime= date(DATE_RFC822);
    $content = $app['twig']->render(
      'reverse_proxy.html.twig'
	    , array(
	      'section' => 'reverse_proxy'
	      , 'slug' => $slug
	      , 'datetime' => $datetime
	      , 'request_headers' => $request->headers->__toString()
	      , 'response_headers' => $response->headers->__toString()
	      , 'lesson_title' => $lesson_title
	      , 'lesson' => $lesson
	      , 'lesson_link' => $lesson_link
	    )
    );
    $response->setContent($content);

    return $response;

})->value('slug', 'exp-basic')->bind('reverse_proxy_slug');





$app->get('/boxes', function() use ($app) {
    
    $request = Request::createFromGlobals();
    $response = Response::create('', 200);
    $content = $app['twig']->render(
      'boxes.html.twig'
      , array(
	      'section' => 'boxes'
	      , 'request_headers' => $request->headers->__toString()
	      , 'response_headers' => $response->headers->__toString()
	    )
    );
    $response->setContent($content);

    return $response;
})->bind('boxes');


$app->match('/boxes/date/{frame}', function($frame) use ($app) {

    $request = Request::createFromGlobals();

    $response = Response::create('', 200);
    $date = new \DateTime();

    switch($frame) {
      case 'second':
        $sDate = $date->format('Y-m-d H:i:s');
        break;

      case 'minute':
        $sDate = $date->format('Y-m-d H:i');
        break;

      case 'day':
      default:
        $sDate = $date->format('Y-m-d');
        $frame = 'day';
    }
    $content = "I'm a simple box, created on <strong>$sDate</strong>. My content ".
      "change each <strong>$frame</strong> and my ETAG changes with it";
    $response->setContent($content);
		$response->setPublic();
    $response->setETag(md5($response->getContent()));
    # this returns a 304 if response' ETAG matches request's ETAG
    $response->isNotModified($request);
    return $response;
});



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
