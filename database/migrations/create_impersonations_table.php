<?php

use Carbon\Unit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impersonations', function (Blueprint $table) {
            $table->id();
            $table->string('impersonator_type');
            $table->string('impersonator_id');
            $table->string('impersonated_type');
            $table->string('impersonated_id');
            $table->string('token');
            $table->boolean('used');
            $table->integer('expires_in');
            $table->enum('duration', Unit::cases());
            $table->text('abilities');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('impersonations');
    }
};
