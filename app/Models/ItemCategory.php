<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ItemCategory
 * @package App\Models
 */
class ItemCategory extends Model
{
    protected $table = 'item_categories';
    protected $primaryKey = 'category_id';
    protected $guarded = [];
}
