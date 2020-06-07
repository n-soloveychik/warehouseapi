<?php


namespace App\Internal\ResponseFormatters;


use App\Internal\ResponseFormatters\Formatter\ItemFormatter;
use Illuminate\Support\Collection;

class InvoiceWithItemsResponse
{
    public static function format(Collection $invoices){
        return $invoices->map(function ($inv){
            return array_merge(
                $inv->only('invoice_id', 'order_id', 'invoice_code', 'status_id'),
                array_merge([
                    'status' => $inv->status->status,
                    'items'=>ItemFormatter::formatItems($inv->items),
                ])
            );
        });
    }
}
