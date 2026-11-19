<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_id')->constrained('portals')->cascadeOnDelete();
            // 0 is reserved for the portal owner; positive IDs map to tenant.users.id.
            $table->unsignedInteger('tenant_user_id')->default(0);
            $table->boolean('is_portal_owner')->default(false);
            $table->string('username', 50)->nullable();
            $table->string('user_full_name', 150)->nullable();
            $table->string('user_bn_name', 50)->nullable();
            $table->string('user_address', 250)->nullable();
            $table->integer('user_type')->nullable();
            $table->string('email', 100)->nullable();
            $table->string('user_mobile', 100)->nullable();
            $table->string('user_web')->nullable();
            $table->text('user_bio')->nullable();
            $table->string('user_designation', 50)->nullable();
            $table->string('img_ext')->nullable();
            $table->boolean('activated')->default(true);
            $table->boolean('banned')->default(false);
            $table->string('ban_reason')->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->dateTime('last_login')->nullable();
            $table->dateTime('tenant_created_at')->nullable();
            $table->timestamps();

            $table->unique(['portal_id', 'tenant_user_id']);
            $table->index(['portal_id', 'activated', 'banned']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_users');
    }
};
