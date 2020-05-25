<?php

namespace App\Http\Controllers\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function getWarehouses(Request $request){
        return Warehouse::select('warehouse_id', 'address', 'photo')->get();
    }
}
