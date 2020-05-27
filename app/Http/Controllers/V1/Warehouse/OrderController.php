<?php

namespace App\Http\Controllers\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Internal\ResponseFormatters\InvoiceWithItemsResponse;
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

    /**
     * @param Request $request
     * @param $orderId
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function getInvoices(Request $request, $order_id)
    {
        return InvoiceWithItemsResponse::format(
            Invoice::with('items.status', 'items.category')
                ->where('order_id', $order_id)
                ->get()
        );

    }

    public function getItemsByInvoiceID(Request $request, $order_id, $invoice_id)
    {
        return InvoiceWithItemsResponse::format(
            Invoice::with('items.status', 'items.category')
                ->where('order_id', $order_id)
                ->where('invoice_id', $invoice_id)
                ->get()
        );

    }
}
