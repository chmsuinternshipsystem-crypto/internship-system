<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->string('face_verification_status', 20)
                ->default('unavailable')
                ->after('resolution_status');
            $table->unsignedTinyInteger('face_verification_confidence')
                ->nullable()
                ->after('face_verification_status');
            $table->string('face_verification_reference', 120)
                ->nullable()
                ->after('face_verification_confidence');
            $table->timestamp('face_verified_at')
                ->nullable()
                ->after('face_verification_reference');

            $table->string('time_out_face_verification_status', 20)
                ->nullable()
                ->after('time_out_geofence_status');
            $table->unsignedTinyInteger('time_out_face_verification_confidence')
                ->nullable()
                ->after('time_out_face_verification_status');
            $table->string('time_out_face_verification_reference', 120)
                ->nullable()
                ->after('time_out_face_verification_confidence');
            $table->timestamp('time_out_face_verified_at')
                ->nullable()
                ->after('time_out_face_verification_reference');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropColumn([
                'face_verification_status',
                'face_verification_confidence',
                'face_verification_reference',
                'face_verified_at',
                'time_out_face_verification_status',
                'time_out_face_verification_confidence',
                'time_out_face_verification_reference',
                'time_out_face_verified_at',
            ]);
        });
    }
};
