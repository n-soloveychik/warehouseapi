<?php


namespace App\Internal\ResponseFormatters\Formatter;


use App\Internal\OrderMaster\ItemMaster;
use App\Models\Item;
use \Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class ItemFormatter
{
    /**
     * @param Item $item
     * @return array
     */
    public static function format(Item $item)
    {
        return array_merge(
            $item->only('item_id', 'status_id', 'category_id', 'category_id', 'invoice_id', 'count', 'count_in_stock', 'count_shipment', 'weight', 'item_num', 'lot', 'image', 'size', 'description'),
            [
                'status' => $item->status->status,
                'category' => $item->category->category_name,
                'claims' => ItemClaimFormatter::formatMany($item->claims)
            ]
        );
    }


    /**
     * @param Collection $items
     * @return Collection|\Illuminate\Support\Collection
     */
    public static function formatMany(Collection $items)
    {
        return $items->map(function ($item) {
            return self::format($item);
        })->sortBy('lot')->values();
    }

    /**
     * @param Collection $items
     * @return array
     */
    public static function formatAvailableToTransfer(Collection $items)
    {
        $orders = [];
        $invoices = [];

        return [
            'orders' => $items->groupBy(function ($itm) use (&$orders, &$invoices) {
                $orders[$itm->invoice->order->order_id] = $itm->invoice->order;
                $invoices[$itm->invoice->invoice_id] = $itm->invoice;
                return $itm->invoice->order->order_id;
            })->map(function ($order, $order_id) use ($orders, $invoices) {
                return [
                    'order_id' => (int)$order_id,
                    'order_num' => $orders[$order_id]->order_num,
                    'invoices' => $order->groupBy(function ($itm) {
                        return $itm->invoice->invoice_id;
                    })->map(function ($inv, $invID) use($invoices) {
                        $countAvailable = 0;
                        $inv->each(function ($itm) use (&$countAvailable){
                            $countAvailable += ItemMaster::calcTransferAvailable($itm);
                        });
                        return [
                            'invoice_id' => (int)$invID,
                            'invoice_code' => $invoices[$invID]->invoice_code,
                            'count_available' => $countAvailable,
                        ];
                    })->groupBy(function ($inv){
                        return $inv['invoice_code'];
                    })->map(function ($itms){
                        $itm = $itms[0];
                        for($i=1; $i < count($itms);$i++){
                            $itm['count_available'] += $itms[$i]['count_available'];
                        }
                        return $itm;
                    })->values(),
                ];
            })->values()
        ];

    }
}
