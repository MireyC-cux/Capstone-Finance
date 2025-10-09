<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('finances', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->date('date');
            $table->string('reference')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
            
            $table->index('type');
            $table->index('date');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('finances');
    }
};
