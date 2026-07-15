<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->string('street_address')->nullable()->after('address');
            $table->string('barangay', 120)->nullable()->after('street_address');
            $table->string('city_municipality', 120)->nullable()->after('barangay');
            $table->string('contact_last_name', 120)->nullable()->after('contact_person');
            $table->string('contact_first_name', 120)->nullable()->after('contact_last_name');
            $table->string('contact_middle_initial', 4)->nullable()->after('contact_first_name');
            $table->string('contact_name_extension', 10)->nullable()->after('contact_middle_initial');
        });

        DB::table('companies')
            ->select(['id', 'address', 'contact_person'])
            ->orderBy('id')
            ->lazy()
            ->each(function ($company): void {
                $address = trim((string) ($company->address ?? ''));
                $contact = trim((string) ($company->contact_person ?? ''));

                DB::table('companies')
                    ->where('id', $company->id)
                    ->update([
                        'street_address' => $address !== '' ? $address : null,
                        'contact_last_name' => $contact !== '' ? $contact : null,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->dropColumn([
                'street_address',
                'barangay',
                'city_municipality',
                'contact_last_name',
                'contact_first_name',
                'contact_middle_initial',
                'contact_name_extension',
            ]);
        });
    }
};
