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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained('grades')->cascadeOnDelete();
            $table->foreignId('month_id')->constrained('months')->cascadeOnDelete();
            $table->string('img')->nullable();
            $table->string('nameOfCourse');
            $table->decimal('price');
            $table->text('description');
            $table->integer('numOfLessons')->nullable();
            $table->integer('numOfExams')->nullable();
            $table->date('creationDate')->nullable();
            $table->enum('status', ['active', 'notActive'])->default('active')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
