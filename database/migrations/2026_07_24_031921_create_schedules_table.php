<?php

use App\Enums\ScheduleStatus;
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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();

            $table->date('service_date');
            $table->time('service_time');

            $table->enum('status', array_column(
                ScheduleStatus::cases(),
                'value'
            ))->default(ScheduleStatus::DRAFT->value);

            $table->string('poster_path')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->timestamp('published_at')->nullable();

            $table->timestamps();
            
            $table->unique([
                'service_date',
                'service_time',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
