<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';
    protected $primaryKey = 'invoice_id';
    protected $guarded = [];

    public function status(){
        return $this->belongsTo(InvoiceStatus::class, 'status_id', 'status_id');
    }

    public function items(){
        return $this->hasMany(Item::class, 'invoice_id', 'invoice_id');
    }

    public function order(){
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
}
