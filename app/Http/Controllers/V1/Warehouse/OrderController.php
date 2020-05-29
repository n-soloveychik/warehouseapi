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
     * @param Request $request
     * @param $orderId
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function getInvoices(Request $request, $order_id)
    {
        return InvoiceWithItemsResponse::format(
            Invoice::with('status', 'items.status', 'items.category','items.claims.images')
                ->where('order_id', $order_id)
                ->get()
        );

    }

    public function getItemsByInvoiceID(Request $request, $order_id, $invoice_id)
    {
        return InvoiceWithItemsResponse::format(
            Invoice::with('status', 'items.status', 'items.category', 'items.claims.images')
                ->where('order_id', $order_id)
                ->where('invoice_id', $invoice_id)
                ->get()
        );

    }
}
