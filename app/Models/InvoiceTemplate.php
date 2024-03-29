<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceTemplate extends Model
{
    protected $table = 'invoice_templates';
    protected $primaryKey = 'invoice_id';
    protected $guarded = [];

    public function rawItems(){
        return $this->belongsToMany(ItemTemplate::class, 'item_templates_invoice_templates', 'invoice_id', 'item_id');
    }

    public function items(){
        return $this->rawItems()->orderBy('category_id')->withPivot(['count','lot']);
    }
}
