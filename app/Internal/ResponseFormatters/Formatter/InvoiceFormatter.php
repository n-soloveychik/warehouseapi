<?php


namespace App\Internal\ResponseFormatters\Formatter;


use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;

class InvoiceFormatter
{
    public static function format(Invoice $invoice){
        return array_merge(
            $invoice->only('invoice_id', 'order_id', 'invoice_code', 'status_id'),
            array_merge([
                'status' => $invoice->status->status,
                'items'=>ItemFormatter::formatMany($invoice->items),
            ])
        );
    }

    public static function formatMany(Collection $invoices){
        return $invoices->map(function ($inv){
            return self::format($inv);
        });
    }

}
