<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ItemClaims extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_claims', function (Blueprint $table) {
            $table->id('claim_id');
            $table->integer('item_id');
            $table->string('num')->nullable();
            $table->string('claim_description',300);
            $table->boolean('closed')->default(0);
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
        Schema::drop('item_claims');
    }
}
