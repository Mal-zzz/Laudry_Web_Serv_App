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





// Rute Publik (Bisa diakses siapa saja tanpa login)

// Routes for Pelanggan
$router->get('/pelanggan', 'PelangganController@getPelanggan');
$router->get('/pelanggan/{id}', 'PelangganController@getPelanggan');
    
// Routes for Pesanan Laundry
$router->get('/pesanan-laundry', 'PesananLaundryController@getPesananLaundry');
$router->get('/pesanan-laundry/{id}', 'PesananLaundryController@getPesananLaundry');




// Rute Terproteksi (Wajib Basic Auth menggunakan email & password pelanggan)
$router->group(['middleware' => 'pelanggan.auth'], function () use ($router) {
    $router->post('/pelanggan', 'PelangganController@store');
    $router->put('/pelanggan/{id}', 'PelangganController@update');
    $router->delete('/pelanggan/{id}', 'PelangganController@destroy');
});


$router->group(['middleware' => 'admin.hardcore'], function () use ($router) {
    // pesanan laundry routes
    $router->post('pesanan-laundry', 'PesananLaundryController@storePesananLaundry');
    $router->put('pesanan-laundry/{id}', 'PesananLaundryController@updatePesananLaundry');
    $router->delete('pesanan-laundry/{id}', 'PesananLaundryController@destroyPesananLaundry');
});
