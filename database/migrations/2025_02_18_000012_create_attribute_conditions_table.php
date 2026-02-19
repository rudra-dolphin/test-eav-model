<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Stores when a field (attribute) should be shown based on another field's value.
     * One row per dependent attribute; operator "=" by default.
     */
    public function up(): void
    {
        Schema::create('attribute_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->string('operator', 10)->default('=');
            $table->string('trigger_value', 255);
            $table->timestamps();

            $table->unique('attribute_id');
            $table->index('parent_attribute_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_conditions');
    }
};
