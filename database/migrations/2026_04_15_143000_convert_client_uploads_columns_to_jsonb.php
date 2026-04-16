<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, changing types requires a 'USING' clause if types are incompatible.
        // We use raw SQL to ensure 'text[]' or other types are converted correctly to 'jsonb'.
        DB::statement('ALTER TABLE "ClientUploads" ALTER COLUMN "file_paths" TYPE jsonb USING to_jsonb("file_paths")');
        DB::statement('ALTER TABLE "ClientUploads" ALTER COLUMN "output_categories" TYPE jsonb USING to_jsonb("output_categories")');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to native arrays if needed
        DB::statement('ALTER TABLE "ClientUploads" ALTER COLUMN "file_paths" TYPE text[] USING array_to_json("file_paths")::text[]');
        DB::statement('ALTER TABLE "ClientUploads" ALTER COLUMN "output_categories" TYPE text[] USING array_to_json("output_categories")::text[]');
    }
};
