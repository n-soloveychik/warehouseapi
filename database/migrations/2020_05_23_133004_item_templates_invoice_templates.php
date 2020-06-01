<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ItemTemplatesInvoiceTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_templates_invoice_templates', function (Blueprint $table) {
            $table->id();
            $table->integer('item_id');
            $table->integer('invoice_id');
            $table->integer('count');
            $table->string('lot');

            $table->index(['item_id', 'invoice_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('item_templates_invoice_templates');
    }
}
