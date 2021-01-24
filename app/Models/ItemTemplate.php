<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemTemplate extends Model
{
    protected $table = 'item_templates';
    protected $primaryKey = 'item_id';
    protected $guarded = [];

    public function invoices(){
        return $this->belongsToMany(InvoiceTemplate::class, 'item_templates_invoice_templates', 'item_id', 'invoice_id');
    }

    public function category(){
        return $this->belongsTo(ItemCategory::class, 'category_id', 'category_id');
    }

    public function mountingType()
    {
        return $this->belongsTo(MountingType::class, 'mount_id', 'id');
    }

}
