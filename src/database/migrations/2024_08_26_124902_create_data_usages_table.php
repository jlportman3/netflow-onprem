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
        Schema::create("data_usages", function (Blueprint $table) {
            $table->id();
            $table->dateTime("end_time");
            $table->unsignedBigInteger("bytes_in");
            $table->unsignedBigInteger("bytes_out");
            $table->unsignedBigInteger("account_id");
            $table->foreign("account_id")
                ->references("id")
                ->on("accounts")
                ->onDelete("cascade");
            $table->timestamps(6);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("data_usages");
    }
};
