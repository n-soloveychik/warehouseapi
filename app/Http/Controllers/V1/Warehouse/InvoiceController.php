<?php

namespace App\Http\Controllers\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Internal\OrderMaster\InvoiceMaster;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function delete($invoice_id){
        InvoiceMaster::delete(Invoice::findOrFail($invoice_id));
        return response(null, Response::HTTP_OK);
    }

    public function shipmentCategory($invoice_id, $category_id){
        Invoice::with('items');
    }
}
