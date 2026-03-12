<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('moyasar_id')->nullable()->index();
            $table->integer('amount')->comment('Amount in halalas (smallest currency unit)');
            $table->string('currency', 3)->default('SAR');
            $table->string('status')->default('initiated')->index();
            $table->string('payment_type')->nullable()->comment('mada, creditcard, applepay, stcpay');
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->string('callback_url')->nullable();
            $table->json('source_data')->nullable()->comment('Raw Moyasar source/payment object');
            $table->text('error_message')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
