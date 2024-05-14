<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Phinx\Migration\AbstractMigration;

final class Timestamps extends AbstractMigration
{
    public function up(): void
    {
        $schema = Manager::schema();
        $schema->create(
            'mmx_twig_chunks_time',
            static function (Blueprint $table) {
                $table->unsignedInteger('id')->primary();
                $table->timestamp('timestamp')->useCurrent();

                $table->foreign('id')
                    ->references('id')
                    ->on('site_htmlsnippets')
                    ->cascadeOnDelete();
            }
        );

        $schema->create(
            'mmx_twig_templates_time',
            static function (Blueprint $table) {
                $table->unsignedInteger('id')->primary();
                $table->timestamp('timestamp')->useCurrent();

                $table->foreign('id')
                    ->references('id')
                    ->on('site_templates')
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        $schema = Manager::schema();
        $schema->drop('mmx_twig_chunks_time');
        $schema->drop('mmx_twig_templates_time');
    }
}
