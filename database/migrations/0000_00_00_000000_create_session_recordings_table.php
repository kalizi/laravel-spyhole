<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateSessionRecordingsTable extends Migration
{
    public function up()
    {
        Schema::create('session_recordings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('path');
            $table->string('session_id');
            $table->binary('recordings');
            $table->string('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('session_recordings');
    }
}
