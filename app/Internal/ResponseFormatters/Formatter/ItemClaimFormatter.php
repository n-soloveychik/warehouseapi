<?php


namespace App\Internal\ResponseFormatters\Formatter;


use App\Models\ItemClaim;
use \Illuminate\Database\Eloquent\Collection;

class ItemClaimFormatter
{
    public static function format(ItemClaim $claim){
        return array_merge(
            $claim->only('claim_id','item_id', 'claim_description', 'closed'),
            [
                'images' => $claim->images->map(function ($img){
//                        return array_merge($img->only('claim_image_id', 'claim_id'), ['img' => url($img->claim_image_path, [], true)]);
                    return url($img->claim_image_path, [], true);
                })
            ]);
    }

    public static function formatMany(Collection $claims){
        return $claims->map(function ($claim){
            return self::format($claim);
        })->sortBy('claim_id')->values();
    }
}
