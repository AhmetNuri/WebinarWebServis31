<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liclogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->nullable()->constrained('licenses')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('level', ['info', 'debug', 'error'])->default('info');
            $table->string('event', 128);
            $table->text('message');
            $table->string('device_id', 128)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('level');
            $table->index('event');
            $table->index('user_id');
            $table->index('license_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liclogs');
    }
};
