<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('proprietaire_id')->constrained('utilisateur')->onDelete('cascade');
    $table->string('title');
    $table->text('message');
    $table->string('type')->default('rappel'); // rappel, alerte, info
    $table->json('data')->nullable();         // infos supplémentaires (id_evenement, etc.)
    $table->timestamp('read_at')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
