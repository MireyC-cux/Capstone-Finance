<?php

// database/migrations/2025_10_15_000002_create_invoice_sequences_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(){
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->id();
            $table->year('year')->unique();
            $table->unsignedInteger('counter')->default(0);
            $table->timestamps();
        });
    }
    public function down(){
        Schema::dropIfExists('invoice_sequences');
    }
};

