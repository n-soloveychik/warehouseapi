<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ItemClaim
 * @package App\Models
 */
class ItemClaim extends Model
{
    protected $table = 'item_claims';
    protected $primaryKey = 'claim_id';
    protected $guarded = [];

    public function images(){
        return $this->hasMany(ItemClaimImage::class, 'claim_id', 'claim_id');
    }

    public function item(){
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }
}
