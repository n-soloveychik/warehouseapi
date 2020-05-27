<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderStatus
 * @package App\Models
 */
class OrderStatus extends Model
{
    protected $table = 'order_statuses';
    protected $primaryKey = 'status_id';
    protected $guarded = [];
}
