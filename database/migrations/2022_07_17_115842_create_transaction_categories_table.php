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
        Schema::create('transaction_categories', static function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->constrained('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_categories');
    }
};
