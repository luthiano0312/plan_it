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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('items')->cascadeOnDelete();
            $table->date('due_date')->nullable();
            $table->unsignedTinyInteger('effort')->default(3);
            $table->decimal('manual_priority', 8, 2)->nullable();
            $table->string('status')->default('pendente')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
