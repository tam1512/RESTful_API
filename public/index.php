<?php 
define("_WEB_PATH_ROOT", __DIR__);
require_once ("../vendor/autoload.php");
date_default_timezone_set('Asia/Ho_Chi_Minh');
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

use Pecee\SimpleRouter\SimpleRouter as Route;
// Route::setDefaultNamespace('App\Controllers\V1');

//Start the routing
Route::start();   