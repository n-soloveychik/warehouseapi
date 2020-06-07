<?php

namespace App\Http\Controllers\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Internal\OrderMaster\ItemMaster;
use App\Internal\ResponseFormatters\Formatter\ItemClaimFormatter;
use App\Internal\ResponseFormatters\Formatter\ItemFormatter;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemClaim;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    /**
     * @param $item_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete($item_id){
        ItemMaster::delete(Item::findOrFail($item_id));
        return response(null, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function createCategory(Request $request){
        $request->validate([
            'category_name' => 'required|min:3|max:100'
        ]);

        $ic = ItemCategory::select('category_name')->where('category_name', $request->get('category_name'))->first();
        if ($ic != null)
            return $ic;

        return ItemCategory::create([
            'category_name' => $request->get('category_name'),
        ])->only('category_name');

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getCategories(Request $request){
        return ItemCategory::select('category_id', 'category_name')->get();
    }

    /**
     * @param Request $request
     * @param $item_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function countInStock(Request $request, $item_id){
        $request->validate([
            'count_in_stock' => 'required|numeric'
        ]);
        $item = Item::findOrFail($item_id);
        ItemMaster::updateStatus($item, $request->get('count_in_stock'));
        return response(ItemFormatter::format($item), Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param $item_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function createClaim(Request $request, $item_id){
        $request->validate([
            'images.*' => 'required|url|min:5|max:200',
            'claim_description' => 'string|min:3|max:300',
        ]);

        $item = Item::findOrFail($item_id);
        ItemMaster::newClaim($item, $request->get('images'), $request->get('claim_description'));
        return response($item->only('item_id', 'status_id'), Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param $claim_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function closeClaim(Request $request, $claim_id){
        $claim = ItemClaim::with('item')->findOrFail($claim_id);
        $claim->closed = 1;
        $claim->save();

        try{
            ItemMaster::updateStatus($claim->item);
        }catch (\Exception $e){}

        return response(null, Response::HTTP_OK);
    }

    /**
     * @param $item_id
     * @return \Illuminate\Support\Collection
     */
    public function claims($item_id){
        $item = Item::with('claims.images')->findOrFail($item_id);
        return ItemClaimFormatter::formatMany($item->claims);
    }

}
