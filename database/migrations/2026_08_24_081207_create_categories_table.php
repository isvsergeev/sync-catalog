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
        Schema::create('categories', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->string('parent')->nullable();
            $table->boolean('active');
            $table->string('name');
            $table->string('url')->nullable();
            $table->string('alias')->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('synced_at')->nullable();

            $table->index('updated_at');
            $table->index('synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
