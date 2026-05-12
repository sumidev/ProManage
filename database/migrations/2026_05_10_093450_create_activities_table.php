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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');

            // Polymorphic Fields: Kis cheez par action hua? (Task? Project? Comment?)
            $table->nullableMorphs('activitable');

            $table->string('type'); // e.g., 'task_moved', 'task_created', 'project_updated'
            $table->text('description'); // Human readable message: "Sumit moved task to Done"
            $table->json('properties')->nullable(); // Old values vs New values (for debugging)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
