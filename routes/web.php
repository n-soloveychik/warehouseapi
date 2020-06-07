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

    $r->post('create-item-template', 'DevController@createItemTemplate');

    // SecureGroup
    $r->group(['middleware' => ['auth:sanctum']], function (\Illuminate\Routing\Router $r){

        $r->group(['prefix'=>'image'], function (\Illuminate\Routing\Router $r){
            $r->post('insert', 'ImageController@insert');
        });

        $r->group(['namespace' => 'Warehouse'], function(\Illuminate\Routing\Router $r){
            $r->get('warehouses', 'WarehouseController@getWarehouses');
            $r->group(['prefix' => 'warehouse'], function (\Illuminate\Routing\Router $r){
                $r->get('{warehouse_id}/order/available', 'WarehouseController@availableOrders')->where('warehouse_id', '[0-9]+');
            });
            $r->delete('claim/{claim_id}', 'ItemController@closeClaim')->where('claim_id', '[0-9]+');
            $r->group(['prefix' => 'order'], function (\Illuminate\Routing\Router $r){
                // Create order
                $r->post('/','OrderController@create');
                $r->group(['prefix' => '{order_id}', 'where' => ['order_id' => '[0-9]+']], function (\Illuminate\Routing\Router $r){
                    $r->delete('/', 'OrderController@delete');
                    $r->get('/invoices', 'OrderController@getInvoices');
                    $r->group(['prefix'=>'invoice'],function (\Illuminate\Routing\Router $r){
                        $r->group(['prefix' => '{invoice_id}','where' => ['invoice_id' => '[0-9]+']], function (\Illuminate\Routing\Router $r){
                            //Delete invoice
                            $r->get('/items', 'OrderController@getItemsByInvoiceID');
                        });

                    });

                });

            });

            $r->group(['prefix' => 'invoice'], function (\Illuminate\Routing\Router $r){
                $r->group(['prefix' => '{invoice_id}','where' => ['invoice_id' => '[0-9]+']], function (\Illuminate\Routing\Router $r){
                    $r->delete('/', 'InvoiceController@delete');
                });


            });

            $r->group(['prefix' => 'item'], function (\Illuminate\Routing\Router $r){
                $r->post('category', 'ItemController@createCategory');
                $r->get('categories', 'ItemController@getCategories');

                $r->group(['prefix' => '{item_id}','where' => ['item_id' => '[0-9]+'],], function (\Illuminate\Routing\Router $r){
                    $r->get('claims', 'ItemController@claims');
                    $r->delete('/', 'ItemController@delete');
                    $r->post('claim', 'ItemController@createClaim');
                    $r->put('count-in-stock', 'ItemController@countInStock');
                });
            });

            $r->group(['prefix' => 'items'], function (\Illuminate\Routing\Router $r){
                $r->put('status-in-stock', 'ItemsController@statusInStock');
            });

            $r->get('user', function (\Illuminate\Http\Request $r){
                return $r->user();
            });

            $r->group(['prefix' => 'template', 'namespace' => 'Template'], function (\Illuminate\Routing\Router $r){
                $r->get('items', 'ItemTemplateController@items');
                $r->get('invoices', 'InvoiceTemplateController@invoices');
                $r->post('item', 'ItemTemplateController@create');

                $r->group(['prefix'=>'item'], function (\Illuminate\Routing\Router $r){
                    $r->group(['prefix' => '{item_id}','where' => ['item_id' => '[0-9]+']], function (\Illuminate\Routing\Router $r){
                        $r->put('image', 'ItemTemplateController@updateImage');
                    });

                });

                $r->group(['prefix' => 'invoice'], function (\Illuminate\Routing\Router $r){
                    $r->post('/', 'InvoiceTemplateController@create');

                    $r->group(['prefix' => '{invoice_id}', 'where' => ['invoice_id' => '[0-9]+']], function (\Illuminate\Routing\Router $r){
                        $r->get('items', 'InvoiceTemplateController@items');

                        $r->group(['prefix' => 'item'], function (\Illuminate\Routing\Router $r){
                            $r->group(['prefix'=>'{item_id}','where' => ['item_id' => '[0-9]+']], function (\Illuminate\Routing\Router $r){
                                $r->put('count', 'InvoiceTemplateController@updateCount');
                                $r->put('lot', 'InvoiceTemplateController@updateLot');
                                $r->post('/', 'InvoiceTemplateController@attach');
                                $r->delete('/', 'InvoiceTemplateController@detach');
                            });

                        });

                    });
                });

            });

        });

    });

});

