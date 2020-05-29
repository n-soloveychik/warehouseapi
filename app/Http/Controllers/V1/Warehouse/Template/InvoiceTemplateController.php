<?php

namespace App\Http\Controllers\V1\Warehouse\Template;

use App\Http\Controllers\Controller;
use App\Internal\ResponseFormatters\Template\ItemsResponse;
use App\Models\InvoiceTemplate;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InvoiceTemplateController extends Controller
{
    public function invoices(){
        return InvoiceTemplate::select('invoice_id', 'invoice_code')->orderBy('invoice_id', 'desc')->get();
    }

    public function items($invoice_id){
        $invoiceTemplate = InvoiceTemplate::with('items.category')->findOrFail($invoice_id);
        return ItemsResponse::format($invoiceTemplate->items);
    }
}
