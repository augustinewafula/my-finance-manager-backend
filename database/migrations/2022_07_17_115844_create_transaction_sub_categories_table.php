<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('transaction_sub_categories', static function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('transaction_category_id');
            $table->foreign('transaction_category_id', 'transaction_sub_categories_transaction_category_id_foreign')->references('id')->on('transaction_categories')->cascadeOnDelete();
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
        Schema::dropIfExists('transaction_sub_categories');
    }
};
