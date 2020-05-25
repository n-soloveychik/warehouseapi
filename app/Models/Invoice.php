<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';
    protected $primaryKey = 'invoice_id';
    protected $guarded = [];

    public function items(){
        return $this->hasMany(Item::class, 'invoice_id', 'invoice_id');
    }
}
