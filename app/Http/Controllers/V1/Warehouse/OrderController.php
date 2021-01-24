<?php

namespace App\Http\Controllers\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Internal\OrderMaster\OrderMaster;
use App\Internal\ResponseFormatters\Formatter\InvoiceFormatter;
use App\Internal\ResponseFormatters\Formatter\ItemClaimFormatter;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{

    /**
     * @param Request $request
     */
    public function create(Request $request)
    {
        $request->validate([
            'order_num' => 'required|string|unique:App\Models\Order|min:3|max:50',
            'invoices.*.count' => 'required|numeric',
            'invoices.*.invoice_id' => 'required|numeric|exists:App\Models\InvoiceTemplate',
            'invoices.*.mount_id' => 'required|numeric|exists:App\Models\MountingType'
        ]);

        $order = OrderMaster::make(1, $request->get('order_num'));
        foreach ($request->get('invoices') as $invoice) {
            $mountID = $invoice['mount_id'];
            /**
             * @var $invoiceModel InvoiceTemplate
             */
            $invoiceModel = InvoiceTemplate::with(['items' => function(Builder $q) use ($mountID){
                $q->whereNull('mount_id')->orWhere('mount_id', $mountID);
            }])->find($invoice['invoice_id']);
            OrderMaster::addInvoice($order, $invoiceModel, $invoice['count']);
        }
    }

    /**
     * @param $order_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete($order_id)
    {
        OrderMaster::delete(Order::findOrFail($order_id));
        return response(null, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param $orderId
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function getInvoices(Request $request, $order_id)
    {
        return InvoiceFormatter::formatMany(
            Invoice::with('status', 'items.status', 'items.category', 'items.claims.images')
                ->where('order_id', $order_id)
                ->get()
        );
    }

    /**
     * @param Request $request
     * @param $order_id
     * @param $invoice_id
     * @return \Illuminate\Support\Collection
     */
    public function getItemsByInvoiceID(Request $request, $order_id, $invoice_id)
    {
        return InvoiceFormatter::formatMany(
            Invoice::with('status', 'items.status', 'items.category', 'items.claims.images')
                ->where('order_id', $order_id)
                ->where('invoice_id', $invoice_id)
                ->get()
        );

    }

    /**
     * @param $order_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function claims($order_id)
    {
        $result = collect();
        // Вытащить все притензии по заказу...
        $order = Order::with('invoices.items.claims.images')->findOrFail($order_id);
        $order->invoices->each(function ($invoice) use ($result) {
            $invoice->items->each(function ($item) use ($result, $invoice) {
                $item->claims->each(function ($claim) use ($result, $invoice, $item) {
                    $result->push(array_merge(['invoice_code' => $invoice->invoice_code], ItemClaimFormatter::format($claim)));
                });
            });
        });

        return response($result->groupBy('invoice_code'));
    }

}
