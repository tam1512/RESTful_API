<?php
use App\Middlewares\AuthMiddleware;
use App\Middlewares\AuthorMiddleware;
use App\Middlewares\RateLimitMiddleware;
use Pecee\SimpleRouter\SimpleRouter as Route;

Route::group(['prefix'=>'api'], function() {
   Route::group(['prefix'=>'v1', 'namespace'=>'App\Controllers\V1'], function() {
      Route::post('auth/login', 'Auth@login');
      Route::post('auth/refresh-token', 'Auth@refresh');
      Route::group(['middleware'=>AuthorMiddleware::class], function() {
         Route::get('auth/profile', 'Auth@profile');
         //update ở trường hợp này vì có file nên PATCH VÀ PUT không hoạt động phải sử dụng POST
         Route::post('auth/profile', 'Auth@updateProfile');
         Route::get('/my-courses', 'User@courses');
         Route::get('auth/logout', 'Auth@logout');

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