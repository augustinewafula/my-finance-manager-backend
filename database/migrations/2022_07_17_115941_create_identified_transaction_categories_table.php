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
        Schema::create('identified_transaction_categories', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->uuid('transaction_category_id');
            $table->uuid('transaction_sub_category_id')->nullable();
            $table->timestamps();
            $table->foreign('transaction_category_id', 'identified_categories_transaction_category_id_foreign')->references('id')->on('transaction_categories')->cascadeOnDelete();
            $table->foreign('transaction_sub_category_id', 'identified_categories_transaction_sub_category_id_foreign')->references('id')->on('transaction_sub_categories')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('identified_transaction_categories');
    }
};
