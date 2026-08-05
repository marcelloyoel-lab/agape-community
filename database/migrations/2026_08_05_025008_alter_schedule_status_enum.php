<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE schedules
            MODIFY COLUMN status ENUM(
                'draft',
                'generated',
                'published',
                'cancelled'
            ) NOT NULL DEFAULT 'draft'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE schedules
            MODIFY COLUMN status ENUM(
                'draft',
                'approved',
                'published',
                'cancelled',
                'rejected'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};
