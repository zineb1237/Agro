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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proprietaire_id')->constrained('utilisateur')->onDelete('cascade');
            $table->foreignId('parcelle_id')->nullable()->constrained('parcelle')->onDelete('cascade');
            $table->string('title');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('type')->default('note'); // note, irrigation, traitement, fertilisation, recolte...
            $table->text('description')->nullable();
            $table->string('background_color')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
