<?php


namespace App\Internal\ResponseFormatters\Formatter;


use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;

class TransferHistoryFormatter
{
    public static function format(Collection $notes){
        $itemIds = [];
        // Need item ids
        $notes->each(function ($note) use (&$itemIds){
            $itemIds[$note->from_item_id] = $note->from_item_id;
            $itemIds[$note->to_item_id] = $note->to_item_id;
        });
        $items = Item::with('invoice.order')->whereIn('item_id', $itemIds)->get();

        return $notes->map(function ($note) use ($items){
            $itemFrom = $items->firstWhere('item_id', $note->from_item_id);
            $itemTo = $items->firstWhere('item_id', $note->to_item_id);
            if (empty($itemFrom) || empty($itemTo)){
                return;
            }
            return array_merge($note->only(['from_item_id', 'to_item_id', 'count']),
                [
                    'from_order_num' => $itemFrom->invoice->order->order_num,
                    'to_order_num'=>$itemTo->invoice->order->order_num,
                    'from_invoice_code' => $itemFrom->invoice->invoice_code,
                    'to_invoice_code' => $itemTo->invoice->invoice_code,
                ]
            );
        })->filter()->values()->all();
    }
}
