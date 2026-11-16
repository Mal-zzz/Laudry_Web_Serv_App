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





$router->group([], function () use ($router) {
    // Routes for Pelanggan
    $router->get('pelanggan', 'PelangganController@index');
    $router->get('pelanggan/{id}', 'PelangganController@show');
    $router->post('pelanggan', 'PelangganController@store');
    $router->put('pelanggan/{id}', 'PelangganController@update');
    $router->delete('pelanggan/{id}', 'PelangganController@destroy');

    // Routes for Pesanan Laundry
    $router->get('pesanan-laundry', 'PesananLaundryController@index');
    $router->get('pesanan-laundry/{id}', 'PesananLaundryController@show');
    $router->post('pesanan-laundry', 'PesananLaundryController@store');
    $router->put('pesanan-laundry/{id}', 'PesananLaundryController@update');
    $router->delete('pesanan-laundry/{id}', 'PesananLaundryController@destroy');
});
