<?php

namespace App\Http\Controllers\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
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

    public function statusStock(Request $request, $item_id){
        $request->validate([
            'images.*' => 'required|string|min:5|max:200'
        ]);
        $item = Item::find($item_id);
        if (empty($item))
            throw new NotFoundHttpException("Unknown item_id");



        foreach ($request->get('images') as $image){

        }
    }

}
