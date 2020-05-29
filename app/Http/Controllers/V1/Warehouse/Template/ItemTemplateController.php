<?php

namespace App\Http\Controllers\V1\Warehouse\Template;

use App\Http\Controllers\Controller;
use App\Internal\ResponseFormatters\Template\ItemResponse;
use App\Models\ItemTemplate;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ItemTemplateController extends Controller
{
    public function items(){
        return ItemResponse::format(
            QueryBuilder::for(ItemTemplate::class)
                ->allowedFilters([
                    AllowedFilter::exact('category_id'),
                ])
                ->with('category')
                ->get()
        );
    }
}
