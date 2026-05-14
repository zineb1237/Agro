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
        if (!Schema::hasTable('produit')) {
            Schema::create('produit', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('fournisseur_id');
                $table->string('nom');
                $table->string('type');
                $table->string('sous_categorie')->nullable();
                $table->decimal('prixUnitaire', 10, 2);
                $table->integer('stock');
                $table->string('unite_vente')->default('kg');
                $table->text('description')->nullable();
                $table->longText('images')->nullable();
                $table->timestamp('dateAjout')->nullable();
                $table->boolean('promotion')->default(false);
                $table->boolean('certificationBio')->default(false);
                $table->timestamps();

                $table->foreign('fournisseur_id')->references('id')->on('utilisateur')->onDelete('cascade');
            });
        } else {
            Schema::table('produit', function (Blueprint $table) {
                if (!Schema::hasColumn('produit', 'sous_categorie')) {
                    $table->string('sous_categorie')->after('type')->nullable();
                }
                if (!Schema::hasColumn('produit', 'unite_vente')) {
                    $table->string('unite_vente')->after('stock')->default('kg');
                }
                if (!Schema::hasColumn('produit', 'images')) {
                    $table->longText('images')->after('description')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // On ne supprime pas la table si elle existait déjà avant
        // Mais pour une migration standard Laravel :
        // Schema::dropIfExists('produit');
    }
};
