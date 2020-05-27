<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Warehouse
 * @package App\Models
 */
class Warehouse extends Model
{
    protected $table = 'warehouses';
    protected $primaryKey = 'warehouse_id';
    protected $guarded = [];
}
