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
        // Add composite index on Files table
        if (Schema::hasTable('files')) {
            Schema::table('files', function (Blueprint $table) {
                // Composite index for post_id + file_path lookups
                if (! $this->hasIndex('files', 'files_post_id_file_path_idx')) {
                    $table->index(['post_id', 'file_path'], 'files_post_id_file_path_idx');
                }
            });
        }

        // Add composite indexes and ensure foreign keys on pivot tables
        // posts_categories table
        if (Schema::hasTable('posts_categories')) {
            Schema::table('posts_categories', function (Blueprint $table) {
                // Composite index for category_id + post_id lookups
                if (! $this->hasIndex('posts_categories', 'posts_categories_category_post_idx')) {
                    $table->index(['category_id', 'post_id'], 'posts_categories_category_post_idx');
                }

                // Ensure foreign keys exist (they should already exist, but verify)
                // Foreign keys are already defined in the original migration, so we just ensure indexes
            });
        }

        // posts_tags table
        if (Schema::hasTable('posts_tags')) {
            Schema::table('posts_tags', function (Blueprint $table) {
                // Composite index for tag_id + post_id lookups
                if (! $this->hasIndex('posts_tags', 'posts_tags_tag_post_idx')) {
                    $table->index(['tag_id', 'post_id'], 'posts_tags_tag_post_idx');
                }

                // Ensure foreign keys exist (they should already exist, but verify)
                // Foreign keys are already defined in the original migration, so we just ensure indexes
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove composite index from Files table
        if (Schema::hasTable('files')) {
            Schema::table('files', function (Blueprint $table) {
                $table->dropIndex('files_post_id_file_path_idx');
            });
        }

        // Remove composite indexes from pivot tables
        if (Schema::hasTable('posts_categories')) {
            Schema::table('posts_categories', function (Blueprint $table) {
                $table->dropIndex('posts_categories_category_post_idx');
            });
        }

        if (Schema::hasTable('posts_tags')) {
            Schema::table('posts_tags', function (Blueprint $table) {
                $table->dropIndex('posts_tags_tag_post_idx');
            });
        }
    }

    /**
     * Check if an index exists on a table
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        $tablePrefix = $connection->getTablePrefix();
        $fullTableName = $tablePrefix.$table;

        if ($connection->getDriverName() === 'mysql') {
            $result = $connection->select(
                'SELECT COUNT(*) as count FROM information_schema.statistics
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                [$database, $table, $indexName]
            );

            return $result[0]->count > 0;
        } elseif ($connection->getDriverName() === 'pgsql') {
            $result = $connection->select(
                "SELECT COUNT(*) as count FROM pg_indexes
                 WHERE schemaname = 'public' AND tablename = ? AND indexname = ?",
                [$table, $indexName]
            );

            return $result[0]->count > 0;
        }

        // For SQLite, try to get index list
        try {
            $indexes = $connection->select("PRAGMA index_list({$fullTableName})");
            foreach ($indexes as $index) {
                if ($index->name === $indexName) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            // If we can't check, assume it doesn't exist
        }

        return false;
    }
};
