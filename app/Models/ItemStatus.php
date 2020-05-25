<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemStatus extends Model
{
    protected $table = 'item_statuses';
    protected $primaryKey = 'status_id';
    protected $guarded = [];
}
