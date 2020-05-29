<?php


namespace App\Internal\OrderMaster;


use App\Models\Invoice;

class InvoiceMaster
{
    protected $invoice;
    public function __construct(Invoice $invoice)
    {
        //$invoice->refresh();
        $this->invoice = $invoice;
    }

    public function updateInvoiceStatus(){
        $this->invoice->load('items.claims', 'order');

        $this->invoice->status_id = $this->getStatus();
        $this->invoice->save();
        OrderMaster::updateOrderStatus($this->invoice->order);

    }

    protected function getStatus() : int {
        if ($this->invoice->items->filter(function ($item){return $item->status_id == 3;})->count() > 0)
            return 3;

        if ($this->invoice->items->filter(function ($item){return $item->status_id != 2;})->count() == 0)
            return 4;

        if ($this->invoice->items->filter(function ($item){return $item->status_id != 1 && $item->status_id != 3;})->count() >0)
            return 2;

        return 1;
    }

}
