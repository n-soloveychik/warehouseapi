<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'order_id';
    protected $guarded = [];

    public function status(){
        return $this->belongsTo(OrderStatus::class, 'status_id', 'status_id');
    }

    public function invoices(){
        return $this->hasMany(Invoice::class, 'order_id', 'order_id');
    }
}
