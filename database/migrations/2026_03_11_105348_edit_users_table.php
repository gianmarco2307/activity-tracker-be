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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->string('username');
            $table->string('firstName');
            $table->string('lastName');
            $table->string('role')->default('User');
            $table->string('codiceFiscale')->nullable(true)->default(null);
            $table->date('birthDate')->nullable(true)->default(null);
            $table->string('birthPlace')->nullable(true)->default(null);
            $table->string('residence')->nullable(true)->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'firstName', 'lastName', 'role', 'codiceFiscale', 'birthDate', 'birthPlace', 'residence']);
        });
    }
};
