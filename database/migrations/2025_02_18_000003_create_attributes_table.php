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
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('field_type_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100)->index(); // machine name e.g. fullName
            $table->string('label');
            $table->string('placeholder', 255)->nullable();
            $table->text('help_text')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_required')->default(false);
            $table->json('validation_config')->nullable(); // e.g. {"minLength":3,"maxLength":50}
            $table->json('conditional_logic')->nullable(); // e.g. {"showIf":{"field":"department","operator":"equals","value":"IT"}}
            $table->timestamps();

            $table->unique(['form_id', 'name']);
            $table->index(['form_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
