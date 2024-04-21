<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaction_data', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text('account_number')->nullable();
            $table->text('bank_name')->nullable();
            $table->text('note')->nullable();
            $table->text('postage')->nullable();
            $table->text('transaction_type')->nullable();
            $table->text('value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_data');
    }
};
