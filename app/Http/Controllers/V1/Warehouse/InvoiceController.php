<?php

namespace App\Http\Controllers\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Internal\OrderMaster\InvoiceMaster;
use App\Internal\OrderMaster\ItemMaster;
use App\Internal\ResponseFormatters\Formatter\ItemFormatter;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InvoiceController extends Controller
{
    /**
     * @param $invoice_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete($invoice_id){
        InvoiceMaster::delete(Invoice::findOrFail($invoice_id));
        return response(null, Response::HTTP_OK);
    }

    /**
     * @param $invoice_id
     * @param $category_id
     * @return array
     */
    public function shipmentCategory($invoice_id, $category_id){
        $inv = Invoice::with(['items'=>function($q) use ($category_id){
            $q->where('category_id', $category_id);
        }])
            ->where('invoice_id', $invoice_id)
            ->first();

        if (empty($inv)){
            throw new NotFoundHttpException('Invoice undefined');
        }

        // Отгрузка
        $inv->items->each(function ($item){
            if ($item->count == $item->count_in_stock) {
                ItemMaster::shipment($item, $item->count);
            }
        });

        return [
            'category_id' => (int)$category_id,
            'invoice_id' => (int)$invoice_id,
            'items' => ItemFormatter::formatMany($inv->items),
        ];
    }
}
