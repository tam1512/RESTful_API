<?php
use Pecee\SimpleRouter\SimpleRouter as Route;

Route::group(['prefix'=>'api'], function() {
   Route::group(['prefix'=>'v1', 'namespace'=>'App\Controllers\V1'], function() {
      Route::get('/users', 'User@index');
      Route::get('/users/{id}', 'User@find');
      Route::post('/users', 'User@store');
   });
});