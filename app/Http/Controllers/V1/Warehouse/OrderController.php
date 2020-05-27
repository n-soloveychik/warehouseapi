<?php

namespace App\Http\Controllers\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * @param OrderRequest $request
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function getAvailable(OrderRequest $request)
    {
        return Order::with('status', 'invoices.status')
            ->where('status_id', '<', 5)
            ->where('warehouse_id', $request->get('warehouse_id'))
            ->get()
            ->map(function ($o) {
                return array_merge(
                    $o->only('order_id','warehouse_id','order_num','status_id'),
                    [
                        'status'=>$o->status->status,
                        'invoices' => $o->invoices->map(function ($invoice){
                            return array_merge($invoice->only('invoice_id', 'invoice_code', 'status_id'), ['status' => $invoice->status->status]);
                        })
                    ]
                );
            });
    }

    /**
     * @param Request $request
     * @param $orderId
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function getInvoices(Request $request, $orderId){
        return Invoice::with('items.status', 'items.category')
            ->where('order_id', $orderId)
            ->get()
            ->map(function ($inv){
                return array_merge(
                    $inv->only('invoice_id', 'order_id', 'invoice_code'),
                    array_merge([
                        'items'=>$inv->items->map(function ($item){
                            return array_merge(
                                $item->only('item_id', 'status_id', 'category_id', 'category_id', 'invoice_id', 'count', 'weight','item_num', 'lot', 'image', 'size', 'description'),
                                ['status' => $item->status->status,'category' => $item->category->category_name]);
                        })
                    ])
                );
            });
    }
}
