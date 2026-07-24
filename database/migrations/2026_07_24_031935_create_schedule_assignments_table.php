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
        Schema::create('schedule_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('schedule_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('ministry_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('member_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unsignedInteger('display_order')->default(1);

            $table->timestamps();

            $table->unique([
                'schedule_id',
                'ministry_id',
                'member_id'
            ]);

            $table->index('schedule_id');
            $table->index('ministry_id');
            $table->index('member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_assignments');
    }
};
