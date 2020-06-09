<?php


namespace App\Internal\TemplateMaster;


use App\Internal\ResponseFormatters\Template\InvoiceItemsResponse;
use App\Models\InvoiceTemplate;
use App\Models\ItemTemplate;

class InvoiceTemplateMaster
{
    public static function updatePivots(InvoiceTemplate $invoice, $item_id, array $pivots){
        ItemTemplate::findOrFail($item_id);
        $invoice->items()->updateExistingPivot($item_id, $pivots);
        return InvoiceItemsResponse::format($invoice->items()->find($item_id));
    }
}
