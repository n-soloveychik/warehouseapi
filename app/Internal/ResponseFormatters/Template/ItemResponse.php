<?php


namespace App\Internal\ResponseFormatters\Template;


use App\Models\ItemTemplate;
use Illuminate\Support\Collection;

class ItemResponse
{
    public static function format(Collection $items){
        return $items->map(function (ItemTemplate $item){
            return array_merge(
                $item->only('item_id', 'category_id', 'item_num', 'image', 'size', 'weight', 'description'),
                ['category' => $item->category->category_name]
            );
        });
    }
}
