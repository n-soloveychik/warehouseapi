<?php

namespace App\Http\Controllers\V1\Warehouse\Template;

use App\Http\Controllers\Controller;
use App\Internal\ResponseFormatters\Template\InvoiceItemsResponse;
use App\Models\InvoiceTemplate;
use App\Models\ItemTemplate;
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

    public function attach(Request $request, $invoice_id, $item_id){
        $request->validate([
            'count' => 'required|numeric',
            'lot' => 'required|string|max:50|min:1',
        ]);

        $invoice = InvoiceTemplate::findOrFail($invoice_id);
        if ($invoice->items->filter(function ($item) use ($item_id) {return $item->item_id == $item_id;})->count() > 0) {
            return response(null, Response::HTTP_CONFLICT);
        }

        $item = ItemTemplate::findOrFail($item_id);
        $invoice->items()->attach($item, ['count' => $request->get('count'), 'lot' => $request->get('lot')]);

        return response(null, Response::HTTP_CREATED);
    }

    public function detach($invoice_id, $item_id){
        $invoice = InvoiceTemplate::findOrFail($invoice_id);
        if ($invoice->items->filter(function ($item) use ($item_id) {return $item->item_id == $item_id;})->count() < 1) {
            return response(null, Response::HTTP_NOT_FOUND);
        }

        $item = ItemTemplate::findOrFail($item_id);
        $invoice->items()->detach($item);

        return response(null, Response::HTTP_OK);
    }
}
