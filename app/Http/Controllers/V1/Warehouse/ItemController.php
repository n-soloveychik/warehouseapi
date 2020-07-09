<?php

namespace App\Http\Controllers\V1\Warehouse;

use App\Http\Controllers\Controller;
use App\Internal\OrderMaster\ItemMaster;
use App\Internal\ResponseFormatters\Formatter\ItemClaimFormatter;
use App\Internal\ResponseFormatters\Formatter\ItemFormatter;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemClaim;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ItemController extends Controller
{
    /**
     * @param $item_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete($item_id)
    {
        ItemMaster::delete(Item::findOrFail($item_id));
        return response(null, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function createCategory(Request $request)
    {
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
    public function getCategories(Request $request)
    {
        return ItemCategory::select('category_id', 'category_name')->get();
    }

    /**
     * @param Request $request
     * @param $item_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function countInStock(Request $request, $item_id)
    {
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
     */
    public function countShipment(Request $request, $item_id)
    {
        $request->validate([
            'count_shipment' => 'required|numeric',
        ]);

        $item = Item::with('claims', 'category', 'status')->findOrFail($item_id);
        try {
            ItemMaster::shipment($item, (int)$request->get('count_shipment'));
        } catch (\Exception $e) {
            throw new ConflictHttpException("count_in_stock < count_shipment");
        }
        return response(ItemFormatter::format($item), Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @param $item_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function createClaim(Request $request, $item_id)
    {
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
    public function closeClaim(Request $request, $claim_id)
    {
        $claim = ItemClaim::with('item')->findOrFail($claim_id);
        $claim->closed = 1;
        $claim->save();

        try {
            ItemMaster::updateStatus($claim->item);
        } catch (\Exception $e) {
        }

        return response([
            'invoice_code' => $claim->item->invoice->invoice_code,
            'claim_id' => $claim->claim_id,
        ], Response::HTTP_OK);
    }

    /**
     * @param $item_id
     * @return \Illuminate\Support\Collection
     */
    public function claims($item_id)
    {
        $item = Item::with('claims.images')->findOrFail($item_id);
        return ItemClaimFormatter::formatMany($item->claims);
    }

    /**
     * @param $item_id
     * @return array
     */
    public function transferAvailable($item_id)
    {
        $item = Item::with('invoice.order.invoices')->findOrFail($item_id);
        $currInvoices = $item->invoice->order->invoices->map(function ($inv) {
            return $inv->invoice_id;
        })->values()->all();

        $needItems = Item::with('invoice.order')
            ->where('item_num', $item->item_num)
            ->where('count_in_stock', '>', '0')
            ->whereNotIn('invoice_id', $currInvoices)
            ->get();

        $needItems = $needItems->filter(function ($item) {
            return $item->count_in_stock > $item->count_shipment;
        });

        return array_merge(ItemFormatter::formatAvailableToTransfer($needItems),
            [
                'item' => array_merge(ItemFormatter::format($item),
                    [
                        'invoice_code' => $item->invoice->invoice_code,
                        'order_num' => $item->invoice->order->order_num])
            ]
        );
    }

    /**
     * @param Request $request
     * @param $item_id
     * @return int[]
     */
    public function supplement(Request $request, $item_id)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.order_id' => 'required|numeric|exists:App\Models\Order',
            'orders.*.invoices' => 'required|array',
            'orders.*.invoices.*.invoice_code' => 'required|string|exists:App\Models\Invoice',
            'orders.*.invoices.*.count' => 'required|numeric'
        ]);

        $itm = Item::with('invoice')->findOrFail($item_id);
        $countTransferred = 0;

        foreach ($request->get('orders') as $order) {
            $orderID = $order['order_id'];
            foreach ($order['invoices'] as $reqInvoice) {
                $invoices = Invoice::with(['items' => function ($q) use ($itm) {
                    $q->where('item_num', $itm->item_num);
                }])->where('invoice_code', $reqInvoice['invoice_code'])
                    ->where('order_id', $orderID)
                    ->get();

                foreach ($invoices as $invoice) {
                    foreach ($invoice->items as $item) {
                        $availableToTransfer = ItemMaster::calcTransferAvailable($item);
                        $countToTransfer = $availableToTransfer;

                        if ($availableToTransfer > $reqInvoice['count']) {
                            $countToTransfer = $reqInvoice['count'];
                        }
                        $countTransferred = ItemMaster::supplement($itm, $item, $countToTransfer);
                        if ($countTransferred >= $reqInvoice['count'])
                            break;
                    }
                    if ($countTransferred >= $reqInvoice['count'])
                        break;
                }

            }

        }
        return ['transferred' => $countTransferred];
    }

    /**
     * @param $item_id
     * @return array
     */
    public function transferHistory($item_id)
    {
        $itm = Item::with('invoice.order')->findOrFail($item_id);
        return [
            'transfer_history' => $itm->transferHistoryFormatted(),
            'item' => array_merge(
                ItemFormatter::format($itm),
                [
                    'order_num' => $itm->invoice->order->order_num,
                    'invoice_code' => $itm->invoice->invoice_code,
                ]
            ),

        ];
    }
}
