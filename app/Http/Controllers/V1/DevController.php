<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Internal\OrderMaster\InvoiceMaster;
use App\Models\InvoiceTemplate;
use App\Models\ItemTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DevController extends Controller
{
    public function createItemTemplate(Request $request){
        $request->validate([
            'num'=>'required',
        ]);


        $invoice = InvoiceTemplate::where('invoice_code', $request->get('invoice'))->first();
        if (empty($invoice)){
            $invoice = InvoiceTemplate::create([
                'invoice_code' => $request->get('invoice')
            ]);
        }

        $count = (int)$request->get('count');
        if ($count > 0) {
            $w = (float)str_replace(',', '.', $request->get('weight')) / $count;
        }else{
            $w = (float)str_replace(',', '.', $request->get('weight'));
        }
        $lot = $request->get('lot');

        $itd = [
            'image' => 'http://via.placeholder.com/640x360',
            'category_id' => $request->get('category'),
            'item_num' => $request->get('num'),
            'weight' => $w,
            'size' => $request->get('size') ?? 0,
        ];

//        return $itd;

        $itemTemplate = ItemTemplate::where('item_num', $request->get('num'))->first();
        if (empty($itemTemplate)) {
            $itemTemplate = ItemTemplate::create($itd);
        }

        if (empty($invoice->items()->where('item_num', $request->get('num'))->where('lot', $lot)->first())) {
            $invoice->items()->attach($itemTemplate, ['count' => $count, 'lot' => $lot]);
        }
        return [$invoice, $count];
    }
}
