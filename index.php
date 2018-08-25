<?
/*ütf-8*/
set_time_limit(0);
ob_start();
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
define("DS", DIRECTORY_SEPARATOR);

if(file_exists(__DIR__ . '/vendor/autoload.php')) {
    $loader = require __DIR__ . '/vendor/autoload.php';
    //$loader->addPsr4('ComposerPack\\', __DIR__ . '/');
}

if(!empty($_FILES))
{
    restructure_files($_FILES);
    $_POST = array_merge_recursive($_POST,$_FILES);
}

$run = \ComposerPack\System\Run::getInstance(__DIR__);
echo $run->render();