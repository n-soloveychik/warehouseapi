<?php

namespace App\Http\Controllers\V1\Warehouse\Template;

use App\Http\Controllers\Controller;
use App\Internal\ResponseFormatters\Template\InvoiceItemsResponse;
use App\Models\InvoiceTemplate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InvoiceTemplateController extends Controller
{
    public function invoices(){
        return InvoiceTemplate::select('invoice_id', 'invoice_code')->orderBy('invoice_id', 'desc')->get();
    }

    public function createInvoice(Request $request){
        $request->validate([
            'invoice_code' => 'required|string|unique:App\Models\InvoiceTemplate|max:50|min:3'
        ]);

        InvoiceTemplate::create([
            'invoice_code' => $request->get('invoice_code')
        ]);

        return response(null, Response::HTTP_CREATED);
    }

    public function items($invoice_id){
        $invoiceTemplate = InvoiceTemplate::with('items.category')->findOrFail($invoice_id);
        return InvoiceItemsResponse::format($invoiceTemplate->items);
    }

    public function createItem(Request $request){
        $request->validate([
            'category_id' => 'required|numeric|exists:App\Models\ItemCategory',
            'item_num' => 'required|string|'
        ]);

        return response(null, Response::HTTP_CREATED);
    }
}
