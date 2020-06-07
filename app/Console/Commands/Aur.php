<?php

namespace App\Console\Commands;

use App\Models\Item;
use Illuminate\Console\Command;

class Aur extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aur';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dev command';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Item::with('claims')->get()->each(function ($item){
            if($item->claims->isNotEmpty()){
                $item->status_id = 4;
                $item->save();
                return true;
            }
            if ($item->status_id == 2){
                $item->status_id = 3;
                $item->save();
            }
            if ($item->status_id == 1 && $item->count_in_stock > 0){
                $item->status_id = 2;
                $item->save();
            }

        });

//        $items = Item::where('status_id', 2)->get();
//        $items->each(function ($item){
//            //dd($item);
//            $item->count_in_stock = $item->count;
//            $item->save();
//        });
    }
}
