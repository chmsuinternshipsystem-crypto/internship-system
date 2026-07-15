<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendances')) {
            return;
        }

        $columns = [
            'face_verification_status',
            'face_verification_confidence',
            'face_verification_reference',
            'face_verified_at',
            'time_out_face_verification_status',
            'time_out_face_verification_confidence',
            'time_out_face_verification_reference',
            'time_out_face_verified_at',
        ];

        $existing = array_values(array_filter($columns, fn (string $col): bool => Schema::hasColumn('attendances', $col)));
        if ($existing === []) {
            return;
        }

        Schema::table('attendances', function (Blueprint $table) use ($existing): void {
            $table->dropColumn($existing);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('attendances')) {
            return;
        }

        Schema::table('attendances', function (Blueprint $table): void {
            if (! Schema::hasColumn('attendances', 'face_verification_status')) {
                $table->string('face_verification_status', 20)->nullable()->after('resolution_status');
            }
            if (! Schema::hasColumn('attendances', 'face_verification_confidence')) {
                $table->unsignedTinyInteger('face_verification_confidence')->nullable()->after('face_verification_status');
            }
            if (! Schema::hasColumn('attendances', 'face_verification_reference')) {
                $table->string('face_verification_reference', 120)->nullable()->after('face_verification_confidence');
            }
            if (! Schema::hasColumn('attendances', 'face_verified_at')) {
                $table->timestamp('face_verified_at')->nullable()->after('face_verification_reference');
            }
            if (! Schema::hasColumn('attendances', 'time_out_face_verification_status')) {
                $table->string('time_out_face_verification_status', 20)->nullable()->after('time_out_geofence_status');
            }
            if (! Schema::hasColumn('attendances', 'time_out_face_verification_confidence')) {
                $table->unsignedTinyInteger('time_out_face_verification_confidence')->nullable()->after('time_out_face_verification_status');
            }
            if (! Schema::hasColumn('attendances', 'time_out_face_verification_reference')) {
                $table->string('time_out_face_verification_reference', 120)->nullable()->after('time_out_face_verification_confidence');
            }
            if (! Schema::hasColumn('attendances', 'time_out_face_verified_at')) {
                $table->timestamp('time_out_face_verified_at')->nullable()->after('time_out_face_verification_reference');
            }
        });
    }
};
