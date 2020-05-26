<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'items';
    protected $primaryKey = 'item_id';
    protected $guarded = [];

    public function status(){
        return $this->belongsTo(ItemStatus::class, 'status_id', 'status_id');
    }

    public function category(){
        return $this->belongsTo(ItemCategory::class, 'category_id', 'category_id');
    }

    public function invoice(){
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }
}
