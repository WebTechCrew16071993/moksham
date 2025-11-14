<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // the user whose data was affected
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete(); // who performed the action
            $table->string('action'); // e.g., permissions.updated, invoice.saved
            $table->string('subject_type')->nullable(); // model class
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_label')->nullable();
            $table->text('description')->nullable();
            $table->json('changes')->nullable(); // before/after diff or snapshot
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
