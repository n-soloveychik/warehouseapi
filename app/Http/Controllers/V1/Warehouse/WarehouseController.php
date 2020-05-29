<?php

namespace App\Http\Controllers\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
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
        return Order::with('status', 'invoices.status')
            ->where('status_id', '<', 5)
            ->where('warehouse_id', $warehouse_id)
            ->get()
            ->map(function ($o) {
                return array_merge(
                    $o->only('order_id', 'warehouse_id', 'order_num', 'status_id'),
                    [
                        'status' => $o->status->status,
                        'invoices' => $o->invoices->map(function ($invoice) {
                            return array_merge($invoice->only('invoice_id', 'invoice_code', 'status_id'), ['status' => $invoice->status->status]);
                        })
                    ]
                );
            });
    }
}
