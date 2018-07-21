<?php

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
//
// $router->get('/', function () use ($router) {
//     //return $router->app->version();
// });

$router->group(
    [
        'middleware' => 'auth',
    ],
    function () use ($router) {
        $router->group(
            [
                'middleware' => 'parse',
            ],
            function () use ($router) {
                $router->get(
                    '/',
                    [
                        'as' => 'get_micropub',
                        'uses' => 'GetMethodController@index',
                    ]
                );

                $router->post(
                    '/',
                    [
                        'as' => 'post_micropub',
                        'uses' => 'PostMethodController@index',
                    ]
                );
            }
        );

        $router->post(
            '/media',
            [
                'as' => 'media',
                'uses' => 'MediaController@index',
            ]
        );
    }
);
