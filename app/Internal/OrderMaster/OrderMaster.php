<?php


namespace App\Internal\OrderMaster;

use App\Models\Order;

class OrderMaster
{
    protected $order;
    public function __construct(Order $order)
    {
        //$order->refresh();
        $this->order = $order;
    }

    public static function updateOrderStatus(Order $order){
        $m = new self($order);
        $order->status_id = $m->getStatus();
        $order->save();
    }

    public function getStatus(){
        if ($this->order->invoices->filter(function ($invoice){return $invoice->status_id == 3;})->count() > 0)
            return 3;

        if ($this->order->invoices->filter(function ($invoice){return $invoice->status_id != 2;})->count() == 0)
            return 4;

        if ($this->order->invoices->filter(function ($invoice){return $invoice->status_id != 1 && $invoice->status_id != 3;})->count() >0)
            return 2;

        return 1;
    }


}
