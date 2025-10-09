<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceRequestDraftsTable extends Migration
{
    public function up()
    {
        Schema::create('service_request_drafts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('data'); // stores the JSON snapshot
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            // optional foreign key if you want:
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_request_drafts');
    }
};

