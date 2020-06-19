<?php

namespace App\Http\Controllers\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Internal\OrderMaster\Exceptions\OrderMasterException;
use App\Internal\OrderMaster\ItemMaster;
use App\Internal\ResponseFormatters\Formatter\ItemFormatter;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ItemsController
 * @package App\Http\Controllers\V1\Warehouse
 */
class ItemsController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function statusInStock(Request $request)
    {
        $request->validate([
            'item_ids' => 'required',
            'item_ids.*' => 'required|numeric|exists:App\Models\Item,item_id',
        ]);

        $result = [];
        foreach ($request->get('item_ids') as $itemID) {
            $item = Item::find($itemID);
            if ($item->status_id != 5) {
                ItemMaster::updateStatus($item, $item->count);
            }
            $result[] = ItemFormatter::format($item);
        }

        return response(collect($result)->values(), Response::HTTP_OK);
    }
}
