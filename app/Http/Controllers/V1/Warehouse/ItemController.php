<?php

namespace App\Http\Controllers\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Internal\OrderMaster\Exceptions\OrderMasterException;
use App\Internal\OrderMaster\ItemMaster;
use App\Internal\ResponseFormatters\ClaimsResponse;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemClaim;
use App\Models\ItemClaimImage;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
            'category_name' => $request->get('category_name')
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
    public function statusAwaitDelivery(Request $request, $item_id){
        $item = Item::findOrFail($item_id);
        ItemMaster::updateStatus($item, 1);

        return response($item->only('item_id', 'status_id'), Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param $item_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function statusInStock(Request $request, $item_id){
        $request->validate([
            'count' => 'numeric'
        ]);
        $item = Item::findOrFail($item_id);
        $count = $request->get('count');
        if (empty($count) || $request->get('count') > $item->count){
            $item->count_in_stock = $item->count;
            $item->save();
        }else{
            $item->count_in_stock = $count;
            $item->save();
        }

        if ($item->count_in_stock == $item->count) {
            try {
                ItemMaster::updateStatus($item, 2);
            } catch (OrderMasterException $e) {
                throw new HttpException($e->getCode(), $e->getMessage());
            }
        }
        return response($item->only('item_id', 'status_id'), Response::HTTP_OK);
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
            //'claim_description' => 'required|string|min:3|max:300',
        ]);

        $item = Item::findOrFail($item_id);

        $imgModels = [];
        foreach ($request->get('images') as $image){

            $imgModels[] = $imgModel = new ItemClaimImage([
                'claim_image_path' => Arr::get(parse_url($image), 'path')
            ]);
        }

        $claim = ItemClaim::create([
            'item_id' => $item_id,
            'claim_description' => $request->get('claim_description'),
        ]);
        $claim->images()->saveMany($imgModels);

        ItemMaster::updateStatus($item, 3);

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
            ItemMaster::updateStatus($claim->item, 2);
        }catch (\Exception $e){}

        return response(null, Response::HTTP_OK);
    }

    /**
     * @param $item_id
     * @return \Illuminate\Support\Collection
     */
    public function claims($item_id){
        $item = Item::with('claims.images')->findOrFail($item_id);
        return ClaimsResponse::format($item->claims);
    }

}
