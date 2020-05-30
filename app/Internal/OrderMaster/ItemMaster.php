<?php


namespace App\Internal\OrderMaster;


use App\Internal\OrderMaster\Exceptions\OrderMasterException;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\ItemTemplate;
use Symfony\Component\HttpFoundation\Response;

class ItemMaster
{
    protected $item;
    protected $invoiceMaster;

    public function __construct(Item $item)
    {
        $item->load('invoice', 'claims');
        // $item->refresh();
        $this->item = $item;
        $this->invoiceMaster = new InvoiceMaster($item->invoice);
    }

    public static function make(Invoice $invoice, ItemTemplate $itemTemplate, int $count, string $lot) : Item{
        return Item::create([
            'status_id' => 1,
            'invoice_id' => $invoice->invoice_id,
            'category_id' => $itemTemplate->category_id,
            'count' => $count,
            'lot' => $lot,
            'weight' => $itemTemplate->weight,
            'item_num' => $itemTemplate->item_num,
            'image' => $itemTemplate->image,
            'size' => $itemTemplate->size,
            'description' => $itemTemplate->description,
        ]);
    }

    public static function delete(Item $item) : bool {
        return $item->delete();
    }

    public static function updateStatus(Item $item, $status_id){
        $m = new ItemMaster($item);
        if ($status_id != 3)
            $m->failIfItemHaveClaims();

        switch ($status_id){
            case 1:
                $m->setStatusAwaitDelivery();
                break;
            case 2:
                $m->setStatusInStock();
                break;
            case 3:
                $m->setStatusClaim();
                break;
            default:
                throw new \Exception("Unknown status_id");
        }
    }

    public function setStatusAwaitDelivery(){
        $this->item->status_id = 1;
        $this->item->save();
        $this->invoiceMaster->updateInvoiceStatus();
    }

    public function setStatusInStock(){
        $this->item->status_id = 2;
        $this->item->save();
        $this->invoiceMaster->updateInvoiceStatus();
    }

    public function setStatusClaim(){
        $this->item->load('invoice.order');
        // Ставим заказу, счету, item статус claim
        $this->item->status_id = 3;
        $this->item->save();

        $this->invoiceMaster->updateInvoiceStatus();
    }

    public static function hasClaims(Item $item) : bool {
        foreach ($item->claims as $claim){
            if($claim->closed == 0)
                return true;
        }
        return false;
    }



    protected function failIfItemHaveClaims(){
        if (self::hasClaims($this->item))
            throw new OrderMasterException('Item has unclosed claims', Response::HTTP_CONFLICT);
    }
}
