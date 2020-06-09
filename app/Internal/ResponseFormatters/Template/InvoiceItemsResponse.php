<?php


namespace App\Internal\ResponseFormatters\Template;


use App\Models\ItemTemplate;
use Illuminate\Database\Eloquent\Collection;

class InvoiceItemsResponse
{
    public static function format(ItemTemplate $item){
        return array_merge(
            $item->only('item_id', 'category_id', 'item_num', 'image', 'size',  'description'),
            [
                'weight' => number_format($item->weight * $item->pivot->count,1),
                'category' => $item->category->category_name,
                'count' => $item->pivot->count,
                'lot' => $item->pivot->lot,
            ]
        );
    }

    public static function formatMany(Collection $items){
        return $items->map(function (ItemTemplate $item){
            return self::format($item);
        })->sortBy(function ($item){
            return $item['lot'];
        })->values();
    }
}
