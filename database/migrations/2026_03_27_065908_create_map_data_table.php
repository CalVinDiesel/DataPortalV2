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
        Schema::create('MapData', function (Blueprint $table) {
            $table->string('mapDataID', 64)->primary();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->double('xAxis')->nullable();
            $table->double('yAxis')->nullable();
            $table->string('3dTiles', 2048);
            $table->string('thumbNailUrl', 2048)->nullable();
            $table->decimal('purchase_price_tokens', 12, 2)->nullable();
            $table->timestamp('updateDateTime')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('MapData');
    }

};
