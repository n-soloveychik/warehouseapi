<?php


namespace App\Internal\ResponseFormatters\Formatter;


use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderFormatter
{
    public static function format(Order $order){
        return array_merge(
            $order->only('order_id', 'warehouse_id', 'order_num', 'status_id'),
            [
                'status' => $order->status->status,
                'invoices' => $order->invoices->map(function ($invoice) {
                    return array_merge($invoice->only('invoice_id', 'invoice_code', 'status_id', 'count'), ['status' => $invoice->status->status]);
                })->sortBy('invoice_code')->values()
            ]
        );
    }



    public static function formatMany(Collection $orders){
        return $orders->map(function ($o){
            return self::format($o);
        })->sortBy('order_num')->values();
    }
}
