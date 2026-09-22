<?php

$publicPath = __DIR__.'/public';
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

/** Keep router and PHP connection logs on one stream so split pipe reads cannot corrupt timestamps. */
file_put_contents('php://stderr', sprintf(
    "[%s] %s:%s [%s] URI: %s\n",
    date('D M j H:i:s Y'),
    $_SERVER['REMOTE_ADDR'],
    $_SERVER['REMOTE_PORT'],
    $_SERVER['REQUEST_METHOD'],
    $uri,
));

require_once $publicPath.'/index.php';
