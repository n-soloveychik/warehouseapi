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


Route::group(['namespace' => 'V1', 'prefix' => 'v1', 'middleware' => ['json']], function (\Illuminate\Routing\Router $r){
    $r->group(['namespace' => 'Auth', 'prefix' => 'auth'], function (\Illuminate\Routing\Router $r){
        $r->post('login', 'LoginController@login');
        $r->post('check', 'LoginController@check')->middleware('auth:sanctum');
    });

    // SecureGroup
    $r->group(['middleware' => ['auth:sanctum']], function (\Illuminate\Routing\Router $r){
        $r->group(['prefix'=>'image'], function (\Illuminate\Routing\Router $r){
            $r->post('insert', 'ImageController@insert');
        });

        $r->group(['namespace' => 'Warehouse'], function(\Illuminate\Routing\Router $r){
            $r->get('warehouses', 'WarehouseController@getWarehouses');
            $r->group(['prefix' => 'order'], function (\Illuminate\Routing\Router $r){
                $r->get('available', 'OrderController@getAvailable');

                $r->group(['prefix' => '{order_id}', 'where' => ['order_id' => '[0-9]+']], function (\Illuminate\Routing\Router $r){
                    $r->get('/invoices', 'OrderController@getInvoices');
                    $r->get('/invoice/{invoice_id}/items', 'OrderController@getItemsByInvoiceID');
                    //$r->get('/{order_id}/invoice/{invoice_id}/items', '');
                });

            });
            $r->group(['prefix' => 'item'], function (\Illuminate\Routing\Router $r){
                $r->post('category', 'ItemController@createCategory');
                $r->get('categories', 'ItemController@getCategories');

                $r->group(['prefix' => '{item_id}','where' => ['item_id' => '[0-9]+'],], function (\Illuminate\Routing\Router $r){
                    $r->put('status-in-stock', 'ItemController@statusInStock');
                    $r->post('status-claim', 'ItemController@statusClaim');
                    $r->get('claims', 'ItemController@claims');
                });
            });

            //$r->get('');

            $r->get('user', function (\Illuminate\Http\Request $r){
                dd($r->user());
            });
        });

    });

});

