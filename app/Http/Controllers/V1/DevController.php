<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Internal\OrderMaster\InvoiceMaster;
use App\Models\InvoiceTemplate;
use App\Models\ItemCategory;
use App\Models\ItemTemplate;
use App\Models\MountingType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DevController extends Controller
{
    public function createItemTemplate(Request $request)
    {
        $request->validate([
            'num' => 'required',
        ]);


        $invoice = InvoiceTemplate::where('invoice_code', $request->get('invoice'))->first();
        if (empty($invoice)) {
            $invoice = InvoiceTemplate::create([
                'invoice_code' => $request->get('invoice')
            ]);
        }

        $count = (int)$request->get('count');
        if ($count > 0) {
            $w = (float)str_replace(',', '.', $request->get('weight')) / $count;
        } else {
            $w = (float)str_replace(',', '.', $request->get('weight'));
        }
        $w = round($w, 2, PHP_ROUND_HALF_UP);
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

    public function jsParserItemTemplate(Request $request)
    {
        $request->validate([
            'invoice' => 'required',
            'data' => 'required|array',
            'data.*.DT_RowData' => 'required|array',
            'data.*.DT_RowData.pack' => 'required',
            'data.*.detailArt' => 'required',
            'data.*.detailSize' => 'required',
            'data.*.detailSizeType' => 'required',
//            'data.*.detailWeightOne'=>'numeric',
            'data.*.detail' => 'required',
            'data.*.detailImg' => 'string|nullable',
            'data.*.typeName' => 'required',
            'data.*.count' => 'required',
            'data.*.mountName' => 'nullable',
        ]);
        $mountTypes = MountingType::all();

        foreach ($request->get('data') as $item) {
            $item['mountID'] = $this->getMountID($item['mountName'], $mountTypes);
            $this->handleTemplate($request->get('invoice'), $item);
        }


    }


    public function handleTemplate($invoiceCode, $item)
    {
        $invoice = InvoiceTemplate::where('invoice_code', $invoiceCode)->first();
        if (empty($invoice)) {
            $invoice = InvoiceTemplate::create([
                'invoice_code' => $invoiceCode
            ]);
        }

        $count = (int)Arr::get($item, 'count', 1);
        $wone = (float)Arr::get($item, 'detailWeightOne', 1000);
        if ($wone <= 0) {
            $wone = 1000;
        }
        $w = $wone / 1000;
        $w = round($w, 2, PHP_ROUND_HALF_UP);
        $lot = Arr::get($item['DT_RowData'], 'pack');
        $dImg = empty($item['detailImg']) ? Carbon::now()->timestamp : Carbon::parse($item['detailImg'])->timestamp;
        $image = "https://i.nash-dvor.com/files/detail/{$item['detail']}.jpg?_=" . $dImg;

        $category = ItemCategory::where('category_name', $item['typeName'])->first();
        $categoryID = 1;
        if (!empty($category)) {
            $categoryID = $category->category_id;
        }
        $itd = [
            'mount_id' => $item['mountID'],
            'image' => $image,
            'category_id' => $categoryID,
            'item_num' => $item['detailArt'],
            'weight' => $w,
            'size' => $item['detailSize'] . " " . $item['detailSizeType'],
        ];

//        return $itd;

        $itemTemplate = ItemTemplate::where('item_num', $item['detailArt'])->first();
        if (empty($itemTemplate)) {
            $itemTemplate = ItemTemplate::create($itd);
        } elseif ($itemTemplate->image == "http://via.placeholder.com/640x360") {
            $itemTemplate->image = $image;
            $itemTemplate->save();
        }

        if (empty($invoice->items()->where('item_num', $item['detailArt'])->where('lot', $lot)->first())) {
            $invoice->items()->attach($itemTemplate, ['count' => $count, 'lot' => $lot]);
        }

        return "OK!";
    }

    public function getMountID($mountName, Collection &$mountTypes)
    {
        if (empty($mountName)) {
            return null;
        }
        $htxt = function ($s) {
            return strtolower(trim($s));
        };
        $m = $mountTypes->first(function (MountingType $m) use ($mountName, $htxt) {
            return $htxt($m->type) == $htxt($mountName);
        });

        if (empty($m)) {
            $mountTypes = MountingType::all();
            $m = $mountTypes->first(function (MountingType $m) use ($mountName, $htxt) {
                return $htxt($m->type) == $htxt($mountName);
            });
            if (empty($m)) {
                $m = MountingType::create([
                    'type' => trim($mountName)
                ]);
                $mountTypes = MountingType::all();
            }
        }

        return $m->id;
    }
}
