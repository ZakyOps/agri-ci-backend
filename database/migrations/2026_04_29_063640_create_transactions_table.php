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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('operator_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('total_fcfa');
            $table->string('payment_method');
            $table->decimal('interest_rate', 8, 4)->default(0);
            $table->unsignedInteger('interest_amount_fcfa')->default(0);
            $table->unsignedInteger('credited_total_fcfa')->default(0);
            $table->string('status')->default('closed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
