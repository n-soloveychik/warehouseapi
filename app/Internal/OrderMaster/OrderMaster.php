<?php


namespace App\Internal\OrderMaster;

use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\Order;

class OrderMaster
{
    protected $order;
    public function __construct(Order $order)
    {
        //$order->refresh();
        $this->order = $order;
    }

    public static function make(int $warehouseId, string $orderNum): Order{
        return Order::create([
            'warehouse_id' => $warehouseId,
            'status_id' => 1,
            'order_num' => $orderNum,
        ]);
    }

    public static function delete(Order $order) : bool {
        foreach ($order->invoices as $invoice){
            InvoiceMaster::delete($invoice);
        }
        return $order->delete();
    }

    public static function addInvoice(Order $order, InvoiceTemplate $invoiceTemplate, $count = 1){
        // Create invoice
        $invoice = InvoiceMaster::make($order->order_id, $invoiceTemplate->invoice_code, $count);
        // add items to invoice
        foreach ($invoiceTemplate->items as $item){
            ItemMaster::make($invoice, $item, $item->pivot->count * $count, $item->pivot->lot);
        }
    }

    public static function updateOrderStatus(Order $order){
        $m = new self($order);
        $newStatus = $m->getStatus();
        if ($newStatus != $order->status_id){
            $order->status_id = $m->getStatus();
            $order->save();
        }
    }

    public function getStatus(){
        if ($this->order->invoices->filter(function ($invoice){return $invoice->status_id != 5;})->count() == 0)
            return 5;

        if ($this->order->invoices->filter(function ($invoice){return $invoice->status_id == 3;})->count() > 0)
            return 3;

        if ($this->order->invoices->filter(function ($invoice){return $invoice->status_id != 4;})->count() == 0)
            return 4;

        if ($this->order->invoices->filter(function ($invoice){return $invoice->status_id != 1 && $invoice->status_id != 3;})->count() >0)
            return 2;

        return 1;
    }


}
