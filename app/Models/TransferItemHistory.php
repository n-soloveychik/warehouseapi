<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferItemHistory extends Model
{
    protected $table = 'transfer_item_history';
    protected $primaryKey = 'transfer_id';
    protected $guarded = [];

    public function item(){

    }
}
