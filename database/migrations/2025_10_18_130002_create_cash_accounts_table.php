<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_accounts', function (Blueprint $table) {
            $table->id('account_id');
            $table->string('account_name');
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->enum('type', ['Cash', 'Bank']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_accounts');
    }
};
