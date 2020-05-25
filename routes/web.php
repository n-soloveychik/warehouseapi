<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/





Route::group(['namespace' => 'V1', 'prefix' => 'v1'], function (\Illuminate\Routing\Router $r){

    $r->group(['namespace' => 'Auth'], function (\Illuminate\Routing\Router $r){
        $r->post('login', 'LoginController@login');
    });

    // SecureGroup
    $r->group(['middleware' => ['auth:sanctum'], 'namespace' => 'Warehouse'], function (\Illuminate\Routing\Router $r){
        $r->post('item-category', 'ItemController@createCategory');
        $r->get('item-categories', 'ItemController@getCategories');

        $r->get('warehouses', 'WarehouseController@getWarehouses');

        $r->group(['prefix' => 'order'], function (\Illuminate\Routing\Router $r){
            $r->get('available', 'OrderController@getAvailable');
            $r->get('{order_id}/invoices', 'OrderController@getInvoices');
            //$r->get('/{order_id}/invoice/{invoice_id}/items', '');
        });

        //$r->get('');

        $r->get('user', function (\Illuminate\Http\Request $r){
            dd($r->user());
        });

    });



});

