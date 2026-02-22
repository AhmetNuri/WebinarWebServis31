<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('device_id', 128)->nullable();
            $table->string('serial_number', 64)->unique();
            $table->datetime('starts_at');
            $table->datetime('last_checked_date')->nullable();
            $table->string('last_checked_device_id', 128)->nullable();
            $table->boolean('emergency')->default(false);
            $table->datetime('expires_at')->nullable();
            $table->enum('license_type', ['demo', 'monthly', 'yearly', 'lifetime'])->default('demo');
            $table->string('product_package', 64)->default('Basic');
            $table->boolean('user_enable')->default(true);
            $table->unsignedInteger('max_connection_count')->default(1);
            $table->timestamps();

            $table->index(['user_id', 'serial_number']);
            $table->index('serial_number');
            $table->index('device_id');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
