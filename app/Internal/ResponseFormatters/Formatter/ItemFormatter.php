<?php


namespace App\Internal\ResponseFormatters\Formatter;


use App\Models\Item;
use \Illuminate\Database\Eloquent\Collection;

class ItemFormatter
{
    public static function format(Item $item){
        return array_merge(
            $item->only('item_id', 'status_id', 'category_id', 'category_id', 'invoice_id', 'count', 'count_in_stock', 'weight','item_num', 'lot', 'image', 'size', 'description'),
            [
                'status' => $item->status->status,
                'category' => $item->category->category_name,
                'claims' => ItemClaimFormatter::formatMany($item->claims)
            ]
        );
    }


    public static function formatMany(Collection $items){
        return $items->map(function ($item){
            return self::format($item);
        })->sortBy('lot')->values();
    }
}
