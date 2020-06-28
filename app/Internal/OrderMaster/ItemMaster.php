<?php


namespace App\Internal\OrderMaster;


use App\Internal\Constants\ItemStatus;
use App\Internal\OrderMaster\Exceptions\OrderMasterException;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\ItemClaim;
use App\Models\ItemClaimImage;
use App\Models\ItemTemplate;
use App\Models\TransferItemHistory;
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

    /**
     * @param Invoice $invoice
     * @param ItemTemplate $itemTemplate
     * @param int $count
     * @param string $lot
     * @return Item
     */
    public static function make(Invoice $invoice, ItemTemplate $itemTemplate, int $count, string $lot) : Item{
        return Item::create([
            'status_id' => ItemStatus::AWAIT_DELIVERY,
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

    /**
     * @param Item $item
     * @return bool
     * @throws \Exception
     */
    public static function delete(Item $item) : bool {
        return $item->delete();
    }

    /**
     * @param Item $item
     * @param null $count
     * @return int
     */
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
                $m->writeStatus(ItemStatus::IN_STOCK);
            } else if($item->count_in_stock > 0){
                $m->writeStatus(ItemStatus::PARTIALLY_IN_STOCK);
            }else{
                $m->writeStatus(ItemStatus::AWAIT_DELIVERY);
            }
        }else{
            $m->writeStatus(ItemStatus::CLAIMS);
        }

        return $item->status_id;
    }

    /**
     * @param $item
     * @param int $count
     * @throws \Exception
     */
    public static function shipment($item, int $count){
        if ($item->count_in_stock < $count){
            throw new \Exception("count_in_stock < count_shipment");
        }
        $m = new self($item);

        $item->count_shipment = $count;
        $item->save();
        if ($item->count_shipment == $item->count){
            $m->writeStatus(ItemStatus::SHIPPED);
        }else{
            self::updateStatus($item, $item->count_in_stock);
        }

    }

    /**
     * @param Item $item
     * @param iterable $images
     * @param string $description
     */
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

    /**
     * @param int $statusId
     */
    public function writeStatus(int $statusId){
        $this->item->status_id = $statusId;
        $this->item->save();
        $this->invoiceMaster->updateInvoiceStatus();
    }

    public function setStatusClaim(){
        $this->item->load('invoice.order');
        // Ставим заказу, счету, item статус claim
        $this->writeStatus(ItemStatus::CLAIMS);
    }

    /**
     * @param int $count
     */
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

    /**
     * @param Item $item
     * @return bool
     */
    public static function hasClaims(Item $item) : bool {
        foreach ($item->claims as $claim){
            if($claim->closed == 0)
                return true;
        }
        return false;
    }

    /**
     * @throws OrderMasterException
     */
    protected function failIfItemHaveClaims(){
        if (self::hasClaims($this->item))
            throw new OrderMasterException('Item has unclosed claims', Response::HTTP_CONFLICT);
    }

    /**
     * @param Item $itemFrom
     * @param Item $itemTo
     * @param int $count
     * @return int
     */
    public static function supplement(Item $itemFrom, Item $itemTo, int $count) : int {
        $available = self::calcTransferAvailable($itemFrom);
        if ($available > $count){
            return 0;
        }

        $itemFrom->count_in_stock -= $count;
        $itemTo->count_in_stock += $count;
        TransferItemHistory::create([
            'from_item_id' => $itemFrom->item_id,
            'to_item_id' => $itemTo->item_id,
        ]);
        $itemTo->save();
        $itemFrom->save();

        return $count;
    }

    /**
     * @param Item $item
     * @return int
     */
    public static function calcTransferAvailable(Item $item) : int {
        return $item->count_in_stock - $item->count_shipment;
    }
}
