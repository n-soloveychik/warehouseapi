<?php


namespace App\Internal\OrderMaster;


use App\Models\Item;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderMaster
{
    public function updateItemStatus($item_id, $status_id){
        $item = Item::with('invoice.items')->find($item_id);
        if (empty($item))
            throw new NotFoundHttpException("Unknown item_id");

        switch ($status_id){
            case 1:
                break;
            case 2:
                break;
        }
    }
}
