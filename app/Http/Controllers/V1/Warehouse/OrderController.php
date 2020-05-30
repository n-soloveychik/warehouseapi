<?php

namespace App\Http\Controllers\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Internal\OrderMaster\OrderMaster;
use App\Internal\ResponseFormatters\InvoiceWithItemsResponse;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OrderController extends Controller
{

    /**
     * @param Request $request
     */
    public function create(Request $request){
        $request->validate([
            'order_num' => 'required|string|unique:App\Models\Order|min:3|max:50',
            'invoices.*.count' => 'required|numeric',
            'invoices.*.invoice_id' => 'required|numeric|exists:App\Models\InvoiceTemplate',
        ]);

        $order = OrderMaster::make(1, $request->get('order_num'));
        foreach ($request->get('invoices') as $invoice) {
            for ($i=0; $i< $invoice['count']; $i++){
                OrderMaster::addInvoice($order, InvoiceTemplate::with('items')->find($invoice['invoice_id']));
            }
        }
    }

    /**
     * @param $order_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete($order_id){
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
        return InvoiceWithItemsResponse::format(
            Invoice::with('status', 'items.status', 'items.category','items.claims.images')
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
        return InvoiceWithItemsResponse::format(
            Invoice::with('status', 'items.status', 'items.category', 'items.claims.images')
                ->where('order_id', $order_id)
                ->where('invoice_id', $invoice_id)
                ->get()
        );

    }

}
