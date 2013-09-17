<?php

#ob=ini_get('output_buffering');

# Useful in a clustered environment
$hostname=shell_exec('/bin/uname -n');

# HTTP Standard date format
$date= date(DATE_RFC822);

# Get apache headers
$request=apache_request_headers();
$response=apache_response_headers();
$response=headers_list();

#ob_end_flush();
#flush();

$gmt_mtime = gmdate('D, d M Y H:i:s', time() ) . ' GMT';
header("Last-Modified: " . $gmt_mtime );


$menu[]="<div id=\"menu\">Test cases:<ul>";
$menu[]="<li><a href=\"?test=private\">private</a></li>";
$menu[]="<li><a href=\"?test=public\">public</a></li>";
$menu[]="<li><a href=\"?test=nocache\">nocache</a></li>";
$menu[]="<li><a href=\"?test=private_noexpire\">private_noexpire</a></li>";
$menu[]="<li><a href=\"?test=esi-include-dynamic\">ESI with dynamic box</a></li>";
$menu[]="</ul></div>";

# To test ESI I need to output a block inside a page
# and set different expiration time between the page and the box itself
if ($_GET['test']!='box')
    $content[]=implode("\n", $menu);

switch($_GET['test'])
{
  case 'private':
    session_cache_limiter('private');
    $content[]="<h2>This page can be cached in private cache (means browser)</h2>";
    break;

  case 'public':
    session_cache_limiter('public');
    $content[]="<h2>This page can be cached in public cache (means varnish)</h2>";
    break;

  case 'private_noexpire':
    session_cache_limiter('private_noexpire');
    $content[]="<h2>This page can be cached in private cache (means browser) and the content does not expire</h2>";
    break;

  case 'nocache':
    session_cache_limiter('nocache');
    $content[]="<h2>This page cannot be cached in any cache</h2>";
    break;

  case 'esi-include-dynamic':
    session_cache_limiter('public');
    $content[]="<h2>This page can be cached in public cache</h2>";
    $content[]="Next line will be included by ESI:";
    $content[]="<esi:include src=\"$_SERVER[SCRIPT_NAME]?test=box\" />";
    break;

  case 'box':
    session_cache_limiter('nocache');
    $content[]="I'm a box included by ESI";
    break;

  default:
    session_cache_limiter('nocache');
    $content[]="<h2>Choose a test to perform from MENU</h2>";
}

$cache_limiter = session_cache_limiter();
$cache_expire = session_cache_expire();

$content[]="Test created on <b>$date</b> from <b>$request[Host]</b>";
$content[]="The cache limiter is set to <b>$cache_limiter</b>";
$content[]="If cached this content will expire in <b>$cache_expire</b> minutes";

/* start the session */
session_start();

if ($_GET['test']=='box')
{
  echo "<div id=\"box\">\n";
  echo implode("<br />\n", $content);
  echo "</div>\n";
}
else
{
?>
<html>
<head>
<title>Cache tests</title>

<style type="text/css">
hr {color:sienna;}
p {margin-left:20px;}
#box { border: 1px solid red; padding: 5px; }
</style>

</head>
<body>

<?php
echo implode("<br />\n", $content);
?>

<hr />
<table>
<tr>
<th>Request headers</th>
<th>Response headers</th>
</tr>
<tr>
<td>
<pre>
<?php
print_r($request);
echo "</pre></td>\n<td><pre>\n";
print_r($response);
echo "</pre></td>\n";
?>
</tr>
<tr>
<th>$_COOKIE</th>
<th>$_REQUEST</th>
</tr>
<tr>
<td><pre><?php print_r($_COOKIE); ?></pre><td>
<td><pre><?php print_r($_REQUEST); ?></pre><td>
</tr>
</table>

<?php
#phpinfo();
?>

</pre>
</body>
</html>

<?php
}
?>
