<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {

    $router->post('/login', 'AuthController@login');
    $router->post('/register', 'AuthController@register');

    $router->group(['middleware' => 'auth'], function () use ($router) {
        
        $router->post('/logout', 'AuthController@logout');
        $router->get('/products', 'ProductController@index');
        //$router->post('/product-paginate', 'ProductController@filterPaginatedProduct');
        
        $router->get('/posts', 'PostController@index');
    });
});

// 24-05-2021 lumen_API

/* Encryption keys generated successfully.
   Personal access client created successfully.
   Client ID: 1
   Client secret: D7LmicdqbCnAwv4LzrgKhHTN8H86VKYP283zCYVx
   Password grant client created successfully.
   Client ID: 2
   Client secret: qo6bwcT0GnS6ix0rWPVaAjhRv6nBop7ZJLFwJEjV
*/

// LocalHost Run URL : http://localhost.lumenapi.com/api/documentation  (Swagger Lume API)

// https://www.youtube.com/watch?v=g_22EUfibJ8&t=1868s
