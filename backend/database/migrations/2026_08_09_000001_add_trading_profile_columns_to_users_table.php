<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('timezone', 64)->default('UTC')->after('password');
            $table->char('default_currency', 3)->default('USD')->after('timezone');
            $table->string('theme', 16)->default('system')->after('default_currency');
            $table->jsonb('preferences')->default('{}')->after('theme');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['timezone', 'default_currency', 'theme', 'preferences']);
        });
    }
};
