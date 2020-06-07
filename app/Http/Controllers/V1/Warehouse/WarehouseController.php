<?php

namespace App\Http\Controllers\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Internal\ResponseFormatters\Formatter\OrderFormatter;
use App\Models\Order;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function getWarehouses(Request $request){
        return Warehouse::select('warehouse_id', 'address', 'photo')->get();
    }

    public function availableOrders(Request $request, $warehouse_id)
    {
        return OrderFormatter::formatMany(Order::with('status', 'invoices.status')
            ->where('status_id', '<', 5)
            ->where('warehouse_id', $warehouse_id)
            ->get());

    }

    public function orders($warehouse_id){
        return OrderFormatter::formatMany(Order::with('status', 'invoices.status')
            ->where('warehouse_id', $warehouse_id)
            ->get()
        );
    }
}
