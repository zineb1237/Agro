<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcelle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proprietaire_id')->constrained('utilisateur')->onDelete('cascade');
            $table->string('nom');
            $table->string('culture');
            $table->decimal('superficie', 10, 2);
            $table->string('stade')->nullable();
            $table->date('datePlantation')->nullable();
            $table->string('typeSol')->nullable();
            $table->string('localisation')->nullable();
            $table->string('statut')->default('actif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcelle');
    }
};