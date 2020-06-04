<?php


namespace App\Internal\ResponseFormatters;


use Illuminate\Support\Collection;

class InvoiceWithItemsResponse
{
    public static function format(Collection $invoices){
        return $invoices->map(function ($inv){
            return array_merge(
                $inv->only('invoice_id', 'order_id', 'invoice_code', 'status_id'),
                array_merge([
                    'status' => $inv->status->status,
                    'items'=>$inv->items->map(function ($item){
                        return array_merge(
                            $item->only('item_id', 'status_id', 'category_id', 'category_id', 'invoice_id', 'count', 'count_in_stock', 'weight','item_num', 'lot', 'image', 'size', 'description'),
                            [
                                'status' => $item->status->status,
                                'category' => $item->category->category_name,
                                'claims' => ClaimsResponse::format($item->claims)
                            ]
                        );
                    })->sortBy('lot')->values(),
                ])
            );
        });
    }
}
