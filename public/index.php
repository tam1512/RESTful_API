<?php 
use System\Core\Model;
require_once ("../vendor/autoload.php");

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

use Pecee\SimpleRouter\SimpleRouter as Route;
// Route::setDefaultNamespace('App\Controllers\V1');

//Start the routing
Route::start();   