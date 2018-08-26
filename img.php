<?php
/*ütf-8*/
set_time_limit(0);
ob_start();
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
define("DS", DIRECTORY_SEPARATOR);

include_once ('Helpers/extends.php');

if(file_exists(__DIR__ . '/vendor/autoload.php')) {
    $loader = require __DIR__ . '/vendor/autoload.php';
    //$loader->addPsr4('Beacon\\', __DIR__ . '/');
}

$dir = __DIR__.'/public/';

$url = parse_url($_SERVER['REQUEST_URI']);
if(isset($url['query']))
{
    $get = [];
    parse_str($url['query'], $get);
    $_GET = array_merge($_GET, $get);
}

// But, a better approach is to use information from the request
if(count($_GET) == 1 && isset($_GET['file']))
{
    $file = $dir.$_GET['file'];
    if(file_exists($file) && strpos($file, $dir) === 0)
    {

        $filename = basename($file);
        $file_extension = strtolower(substr(strrchr($filename,"."),1));

        switch( $file_extension ) {
            case "gif": $ctype="image/gif"; break;
            case "png": $ctype="image/png"; break;
            case "bmp": $ctype="image/bmp"; break;
            case "jpeg":
            case "jpg": $ctype="image/jpeg"; break;
            default: $ctype="image/image";
        }

        header('Content-type: ' . $ctype);

        readfile($file);
    }
}
else if (!empty($_GET['file']))
{

    // Setup Glide server
    $server = League\Glide\ServerFactory::create([
        'max_image_size' => 2000*2000,
        'source' => $dir,
        'cache' => $dir.'cache/',
    ]);

    // You could manually pass in the image path and manipulations options
    //$server->outputImage('users/1.jpg', ['w' => 300, 'h' => 400]);

    $server->outputImage($_GET['file'], $_GET);
}