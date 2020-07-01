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
            $table->integer('count_in_stock')->default(0);
            $table->integer('count_shipment')->default(0);
            $table->float('weight',4,1);
            $table->string('item_num');
            $table->string('lot');
            $table->string('image');
            $table->string('size');
            $table->string('description')->nullable();
            $table->tinyInteger('has_transfer')->default(0);
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
