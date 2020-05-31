<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Internal\OrderMaster\InvoiceMaster;
use App\Models\InvoiceTemplate;
use App\Models\ItemTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DevController extends Controller
{
    public function createItemTemplate(Request $request){
        $invoice = InvoiceTemplate::where('invoice_code', $request->get('invoice'))->first();
        if (empty($invoice)){
            InvoiceTemplate::create([
                'invoice_code' => $request->get('invoice')
            ]);
        }

        $itd = [
            'image' => 'http://via.placeholder.com/640x360',
            'category_id' => $request->get('category'),
            'item_num' => $request->get('num'),
            'weight' => $request->get('weight'),
            'size' => $request->get('size'),
        ];

        return $itd;

//        $itemTemplate = ItemTemplate::create();

        return $invoice;
    }
}
