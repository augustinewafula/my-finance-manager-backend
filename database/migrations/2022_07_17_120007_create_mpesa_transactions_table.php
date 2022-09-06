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
        Schema::create('mpesa_transactions', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference_code');
            $table->text('message');
            $table->tinyInteger('type');
            $table->decimal('amount', 10);
            $table->string('subject');
            $table->foreignUuid('transaction_category_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('transaction_sub_category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps(6);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('mpesa_transactions');
    }
};
