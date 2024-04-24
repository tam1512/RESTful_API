<?php
use App\Middlewares\AuthMiddleware;
use App\Middlewares\AuthorMiddleware;
use App\Middlewares\RateLimitMiddleware;
use Pecee\SimpleRouter\SimpleRouter as Route;

Route::group(['prefix'=>'api'], function() {
   Route::group(['prefix'=>'v1', 'namespace'=>'App\Controllers\V1'], function() {
      Route::post('auth/login', 'Auth@login');
      Route::group(['middleware'=>AuthorMiddleware::class], function() {
         Route::get('auth/profile', 'Auth@profile');
         Route::get('/my-courses', 'User@courses');
      });
      Route::group(['middleware'=>[RateLimitMiddleware::class, AuthMiddleware::class]], function() {
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