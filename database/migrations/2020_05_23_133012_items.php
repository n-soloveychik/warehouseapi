<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Items extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id('item_id');
            $table->integer('status_id')->index();
            $table->integer('category_id')->index();
            $table->integer('invoice_id')->index();
            $table->integer('count');
            $table->float('weight',4,1);
            $table->string('item_num');
            $table->string('lot');
            $table->string('image')->nullable();
            $table->string('size');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('items');
    }
}