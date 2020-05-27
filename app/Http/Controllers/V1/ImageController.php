<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function insert(Request $request){

        $request->validate([
            '*' => 'required|image'
        ]);

        $dir = join('/', ['img', Carbon::now()->format("mY")]);

        $s = Storage::disk("public");
        if (!$s->exists($dir)){
            $s->makeDirectory($dir);
        }

        $names = [];
        foreach ($request->files as $file){
            $n = $s->putFile($dir, new File($file));
            $names[] = url(Storage::url($n),[], true);
         }

        return $names;
    }
}
