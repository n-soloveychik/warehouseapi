<?php


namespace App\Internal\OrderMaster;


use App\Internal\Constants\InvoiceStatus;
use App\Internal\Constants\ItemStatus;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\ItemTemplate;

class InvoiceMaster
{
    protected $invoice;
    public function __construct(Invoice $invoice)
    {
        //$invoice->refresh();
        $this->invoice = $invoice;
    }
    public static function make(int $orderId, string $invoiceCode, int $count = 1):Invoice{
        return Invoice::create([
            'order_id' => $orderId,
            'status_id' => InvoiceStatus::AWAIT_DELIVERY,
            'invoice_code' => $invoiceCode,
            'count' => $count,
        ]);
    }

    public static function delete(Invoice $invoice) : bool{
        $invoice->items()->delete();
        return $invoice->delete();
    }

    public function updateInvoiceStatus(){
        $this->invoice->load('items.claims', 'order');
        $newStatus = $this->getStatus();
        if ($this->invoice->status_id != $newStatus) {
            $this->invoice->status_id = $newStatus;
            $this->invoice->save();
            OrderMaster::updateOrderStatus($this->invoice->order);
        }
    }

    protected function getStatus() : int {
        if ($this->invoice->items->filter(function ($item){return $item->status_id != ItemStatus::SHIPPED;})->count() == 0)
            return InvoiceStatus::COMPLAINT_WORK;

        if ($this->invoice->items->filter(function ($item){return $item->status_id == ItemStatus::CLAIMS;})->count() > 0)
            return InvoiceStatus::CLAIMS;

        if ($this->invoice->items->filter(function ($item){return $item->status_id != ItemStatus::IN_STOCK;})->count() == 0)
            return InvoiceStatus::IN_STOCK;

        if ($this->invoice->items->filter(function ($item){return $item->status_id != 1 && $item->status_id != ItemStatus::CLAIMS;})->count() >0)
            return InvoiceStatus::PARTIALLY_IN_STOCK;

        return InvoiceStatus::AWAIT_DELIVERY;
    }

}
