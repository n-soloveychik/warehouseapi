<?php


namespace App\Internal\ResponseFormatters;


use Illuminate\Support\Collection;

class ClaimsResponse
{
    public static function format(Collection $claims){
        return $claims->map(function ($claim){
            return array_merge(
                $claim->only('claim_id','item_id', 'claim_description'),
                [
                    'images' => $claim->images->map(function ($img){
//                        return array_merge($img->only('claim_image_id', 'claim_id'), ['img' => url($img->claim_image_path, [], true)]);
                        return url($img->claim_image_path, [], true);
                    })
                ]);
        });
    }
}
