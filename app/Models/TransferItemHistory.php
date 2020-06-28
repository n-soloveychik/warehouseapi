<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferItemHistory extends Model
{
    protected $table = 'transfer_item_history';
    protected $primaryKey = 'transfer_id';
    protected $guarded = [];

    public function item(){
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    public function fromInvoice(){
        return $this->belongsTo(Invoice::class, 'invoice_id','from_invoice_id');
    }

    public function toInvoice(){
        $this->belongsTo(Invoice::class, 'invoice_id', 'to_invoice_id');
    }
}
