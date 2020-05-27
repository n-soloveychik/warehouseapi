<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ItemClaimImage
 * @package App\Models
 */
class ItemClaimImage extends Model
{
    protected $table = 'item_claim_images';
    protected $primaryKey = 'claim_image_id';
    protected $guarded = [];

    public function claim(){
        $this->belongsTo(ItemClaim::class, 'claim_id','claim_id');
    }
}
