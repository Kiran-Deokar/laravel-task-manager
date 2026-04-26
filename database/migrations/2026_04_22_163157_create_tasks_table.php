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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id('task_id');
            
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['TODO', 'IN_PROGRESS', 'DONE', 'OVERDUE'])->default('TODO');
            $table->enum('priority', ['LOW', 'MEDIUM', 'HIGH'])->default('MEDIUM');
            $table->date('due_date');

            $table->foreignId('project_id')->constrained('projects', 'project_id')->onDelete('cascade');
            $table->foreignId('assigned_to_user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users', 'user_id')->onDelete('cascade');

            $table->timestamps();
            
            $table->index(['status', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};