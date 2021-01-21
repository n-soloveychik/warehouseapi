<?php

namespace App\Console\Commands;

use App\Models\InvoiceTemplate;
use App\Models\Item;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        $bar = $this->output->createProgressBar(Item::count());
        Item::with('invoice')->chunk(100, function ($items) use ($bar){
            /** @var Item $item */
            foreach ($items as $item){

                if (empty($item->invoice)){
                    //$this->warn("emptyInvoice");
                    $item->delete();
                }
                $bar->advance();
            }

        });
//        $templates = InvoiceTemplate::where('created_at', '>', '2020-10-19 19:12:57')->get()->each(function (InvoiceTemplate $t){
//            $t->items()->delete();
//            $t->delete();
//        });
//
//        dd($templates);

        //        Item::with(claims')->get()->each(function ($item){
//            if($item->claims->isNotEmpty()){
//                $item->status_id = 4;
//                $item->save();
//                return true;
//            }
//            if ($item->status_id == 2){
//                $item->status_id = 3;
//                $item->save();
//            }
//            if ($item->status_id == 1 && $item->count_in_stock > 0){
//                $item->status_id = 2;
//                $item->save();
//            }
//
//        });

//        $items = Item::where('status_id', 2)->get();
//        $items->each(function ($item){
//            //dd($item);
//            $item->count_in_stock = $item->count;
//            $item->save();
//        });
        return null;
    }


}
