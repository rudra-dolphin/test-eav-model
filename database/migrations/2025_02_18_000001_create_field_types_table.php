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
        Schema::create('field_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique(); // text, number, date, radio, checkbox, dropdown
            $table->string('slug', 50)->unique()->index();
            $table->string('description', 255)->nullable();
            $table->boolean('supports_options')->default(false);
            $table->boolean('allows_multiple')->default(false);
            $table->string('value_column', 30)->nullable(); // which attribute_values column to use
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_types');
    }
};
