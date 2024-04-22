<?php
use App\Middlewares\RateLimitMiddleware;
use Pecee\SimpleRouter\SimpleRouter as Route;

Route::group(['prefix'=>'api'], function() {
   Route::group(['prefix'=>'v1', 'namespace'=>'App\Controllers\V1'], function() {
      Route::group(['middleware'=>RateLimitMiddleware::class], function() {
         Route::get('/users', 'User@index');
         Route::get('/users/{id}', 'User@find');
         Route::post('/users', 'User@store');
         Route::put('/users/{id}', 'User@update');
         Route::patch('/users/{id}', 'User@update');
         Route::delete('/users/{id}', 'User@delete');
         Route::delete('/users', 'User@deletes');
      });
   });
});