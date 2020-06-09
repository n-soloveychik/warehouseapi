<?php


namespace App\Internal\OrderMaster;


use App\Internal\OrderMaster\Exceptions\OrderMasterException;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\ItemClaim;
use App\Models\ItemClaimImage;
use App\Models\ItemTemplate;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

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
            'weight' => $itemTemplate->weight * $count,
            'item_num' => $itemTemplate->item_num,
            'image' => $itemTemplate->image,
            'size' => $itemTemplate->size,
            'description' => $itemTemplate->description,
        ]);
    }

    public static function delete(Item $item) : bool {
        return $item->delete();
    }

    public static function updateStatus(Item $item, $count=null) : int {
        $m = new ItemMaster($item);
        if ($count === null){
            $count = $item->count_in_stock;
        }

        if ($count != $item->count_in_stock){
            $m->updateCount($count);
        }

        if (!self::hasClaims($item)) {
            if ($item->count == $item->count_in_stock) {
                $m->writeStatus(3);
            } else if($item->count_in_stock > 0){
                $m->writeStatus(2);
            }else{
                $m->writeStatus(1);
            }
        }else{
            $m->writeStatus(4);
        }

        return $item->status_id;
    }

    public static function shipment($item, int $count){
        if ($item->count_in_stock < $count){
            throw new \Exception("count_in_stock < count_shipment");
        }

        $item->count_shipment = $count;

        if ($item->count_shipment == $item->count){
            $item->status_id = 5;
        }else{
            self::updateStatus($item, $item->count_in_stock);
        }
        $item->save();

    }

    public static function newClaim(Item $item, Iterable $images, string $description){
        $imgModels = [];
        foreach ($images as $image){

            $imgModels[] = $imgModel = new ItemClaimImage([
                'claim_image_path' => Arr::get(parse_url($image), 'path')
            ]);
        }

        $claim = ItemClaim::create([
            'item_id' => $item->item_id,
            'claim_description' => $description,
        ]);
        $claim->images()->saveMany($imgModels);

        $m = new ItemMaster($item);
        if($item->count_in_stock == 0){
            $m->updateCount(1);
        }
        $m->setStatusClaim();
    }

    public function writeStatus(int $statusId){
        $this->item->status_id = $statusId;
        $this->item->save();
        $this->invoiceMaster->updateInvoiceStatus();
    }

    public function setStatusClaim(){
        $this->item->load('invoice.order');
        // Ставим заказу, счету, item статус claim
        $this->writeStatus(4);
    }

    public function updateCount(int $count){
        if ($count > $this->item->count){
            $count = $this->item->count;
        }

        if (self::hasClaims($this->item) && $count == 0){
            $count = 1;
        }

        if ($this->item->count_shipment > $count){
            $count = $this->item->count_in_stock;
        }

        $this->item->count_in_stock = $count;
        $this->item->save();
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
