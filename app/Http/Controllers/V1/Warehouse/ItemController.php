<?php

namespace App\Http\Controllers\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Internal\ResponseFormatters\ClaimsResponse;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemClaim;
use App\Models\ItemClaimImage;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ItemController extends Controller
{
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

    public function getCategories(Request $request){
        return ItemCategory::select('category_id', 'category_name')->get();
    }

    public function statusInStock(Request $request, $item_id){
        $item = Item::with('invoice.items')->find($item_id);
        if (empty($item))
            throw new NotFoundHttpException("Unknown item_id");

        $item->status_id = 2;
        $item->save();

        // @TODO Добавить проверку на статус order и статус invoice
        // проверить все ли items в invoice были отмечены статусом 1
        //

        return response(null, Response::HTTP_OK);
    }

    public function statusAwaitDelivery(Request $request, $item_id){
        $item = Item::with('invoice.items')->find($item_id);
        if (empty($item))
            throw new NotFoundHttpException("Unknown item_id");

        $item->status_id = 1;
        $item->save();

        return response(null, Response::HTTP_OK);
    }

    public function createClaim(Request $request, $item_id){
        $request->validate([
            'images.*' => 'required|url|min:5|max:200',
            //'claim_description' => 'required|string|min:3|max:300',
        ]);

        $item = Item::find($item_id);
        if (empty($item))
            throw new NotFoundHttpException("Unknown item_id");

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

        // Ставим заказу, счету, item статус claim
        $item->status_id = 3;
        $item->save();

        $invoice = $item->invoice;
        $invoice->status_id = 3;
        $invoice->save();

        $order = $invoice->order;
        $order->status_id = 3;
        $order->save();

        return response(null, Response::HTTP_CREATED);
    }

    public function claims($item_id){
        $item = Item::with('claims.images')->find($item_id);
        if (empty($item))
            throw new NotFoundHttpException("Unknown item_id");

        return ClaimsResponse::format($item->claims);
    }


}
