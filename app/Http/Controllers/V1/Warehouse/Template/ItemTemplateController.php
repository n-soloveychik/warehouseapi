<?php

namespace App\Http\Controllers\V1\Warehouse\Template;

use App\Http\Controllers\Controller;
use App\Internal\ResponseFormatters\Template\ItemResponse;
use App\Models\ItemTemplate;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Response;

class ItemTemplateController extends Controller
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function items(){
        return ItemResponse::format(
            QueryBuilder::for(ItemTemplate::class)
                ->allowedFilters([
                    AllowedFilter::exact('category_id'),
                ])
                ->orderBy('item_id')
                ->with('category')
                ->get()
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create(Request $request){
        $request->validate([
            'category_id' => 'required|numeric|exists:App\Models\ItemCategory',
            'item_num' => 'required|unique:App\Models\ItemTemplate|string|min:3|max:50',
            'count' => 'required|numeric',
            'image' => 'url|max:200',
            'size' => 'required|string|min:1|max:100',
            'weight' => 'required|numeric'
        ]);

        if (empty($request->get('image'))){
            $request->image = 'http://via.placeholder.com/640x360';
        }

        $item = ItemTemplate::create([
            'category_id' => $request->get('category_id'),
            'item_num' => $request->get('item_num'),
            'image' => $request->get('image'),
            'size' => $request->get('size'),
            'weight' => $request->get('weight'),
            'description' => $request->get('description')
        ]);

        return response(['item_id' => $item->item_id], Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param $item_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function updateImage(Request $request, $item_id){
        $request->validate([
            'image' => 'required|url|min:3|max:200'
        ]);

        $item = ItemTemplate::findOrFail($item_id);
        $item->image = $request->get('image');
        $item->save();

        return response(null, Response::HTTP_OK);
    }
}
