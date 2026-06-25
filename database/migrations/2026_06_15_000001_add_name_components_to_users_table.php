<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('last_name', 255)->nullable()->after('name');
            $table->string('first_name', 255)->nullable()->after('last_name');
            $table->string('middle_name', 255)->nullable()->after('first_name');
            $table->string('name_extension', 50)->nullable()->after('middle_name');
        });

        // Parse existing single-field names into components
        User::query()->whereNotNull('name')->where('name', '!=', '')->each(function (User $user): void {
            $parts = preg_split('/\s+/', trim($user->name));

            if (count($parts) >= 2) {
                $user->last_name = array_pop($parts);
                $user->first_name = array_shift($parts);
                $user->middle_name = ! empty($parts) ? implode(' ', $parts) : null;
            } elseif (count($parts) === 1) {
                $user->last_name = $parts[0];
                $user->first_name = $parts[0];
            }

            $user->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['last_name', 'first_name', 'middle_name', 'name_extension']);
        });
    }
};
