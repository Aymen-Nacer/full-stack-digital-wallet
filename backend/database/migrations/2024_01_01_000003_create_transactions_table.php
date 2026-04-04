<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_wallet_id')->nullable();
            $table->unsignedBigInteger('to_wallet_id');
            $table->decimal('amount', 19, 4);
            $table->string('status', 10);
            $table->string('idempotency_key', 64)->unique()->nullable();
            $table->string('failure_reason', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('from_wallet_id')->references('id')->on('wallets');
            $table->foreign('to_wallet_id')->references('id')->on('wallets');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
