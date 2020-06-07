<?php


namespace App\Internal\ResponseFormatters\Formatter;


use App\Internal\ResponseFormatters\ClaimsResponse;
use App\Models\Item;
use Illuminate\Support\Collection;

class ItemFormatter
{
    public static function format(Item $item){
        return array_merge(
            $item->only('item_id', 'status_id', 'category_id', 'category_id', 'invoice_id', 'count', 'count_in_stock', 'weight','item_num', 'lot', 'image', 'size', 'description'),
            [
                'status' => $item->status->status,
                'category' => $item->category->category_name,
                'claims' => ClaimsResponse::format($item->claims)
            ]
        );
    }


    public static function formatItems(Collection $items){
        return $items->map(function ($item){
            return self::format($item);
        })->sortBy('lot')->values();
    }
}
