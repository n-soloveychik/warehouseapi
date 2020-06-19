<?php


namespace App\Internal\ResponseFormatters\Formatter;


use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class OrderFormatter
{
    public static function format(Order $order){
        return array_merge(
            $order->only('order_id', 'warehouse_id', 'order_num', 'status_id'),
            [
                'status' => $order->status->status,
                'invoices' => $order->invoices->map(function ($invoice) {
                    return array_merge($invoice->only('invoice_id', 'invoice_code', 'status_id', 'count'), ['status' => $invoice->status->status]);
                })->sortBy('invoice_code')->values()->all()
            ]
        );
    }



    public static function formatMany(Collection $orders){
        return $orders->map(function ($o){
            $formatted = self::format($o);
            $hasShipment = count(array_filter($formatted['invoices'], function ($inv){return $inv['status_id'] == 5;})) > 0;
            return array_merge($formatted, ['has_shipment' => (int)$hasShipment]);
        })->sortBy('order_num')->values();
    }


    public static function ordersWithClaimsResponse(Collection $orders){
        return $orders->map(function (Order $o){
            return array_merge(
                $o->only('order_id', 'warehouse_id', 'order_num', 'status_id'),
                [
                    'status' => $o->status->status,
                    'count_claims' => DB::table('invoices')
                        ->join('items', 'invoices.invoice_id', '=','items.invoice_id')
                        ->join('item_claims', 'item_claims.item_id', '=', 'items.item_id')
                        ->where('invoices.order_id', '=', $o->order_id)
                        ->where('item_claims.closed', '0')
                        ->select(DB::raw('COUNT(item_claims.claim_id)'))->first()->count,
                ]
            );
        })->sortBy('order_num')->values();
    }
}
