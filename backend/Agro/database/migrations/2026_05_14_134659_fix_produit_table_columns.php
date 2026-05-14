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
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE produit MODIFY type VARCHAR(255)');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE produit MODIFY sous_categorie VARCHAR(255)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting not easily possible if we don't know the original enum/length
    }
};
