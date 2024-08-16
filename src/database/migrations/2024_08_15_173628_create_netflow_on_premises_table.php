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
        Schema::create('netflow_on_premises', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->ipAddress('ip');
            $table->dateTime('last_processed_timestamp')->nullable();
            $table->string('last_processed_filename')->nullable();
            $table->integer('last_processed_size')->nullable();
            $table->json('statistics')->nullable();
            $table->timestamps(6);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('netflow_on_premises');
    }
};
