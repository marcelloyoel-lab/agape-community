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
        Schema::create('bot_sessions', function (Blueprint $table) {
            $table->id();

            $table->string('phone_number')->unique();

            $table->foreignId('schedule_id')
                ->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('state');

            $table->foreignId('current_ministry_id')
                ->nullable()
                ->constrained('ministries')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->json('temp_data')->nullable();

            $table->timestamp('last_activity_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_sessions');
    }
};
