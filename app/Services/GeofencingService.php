<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class GeofencingService
{
    /**
     * @return array{geofence_status:string,review_required:bool,resolution_status:string,is_within_campus:?bool,location_unavailable:bool,distance_meters:?float}
     */
    public function checkPoint(?float $latitude, ?float $longitude, Setting $campus): array
    {
        if ($latitude === null || $longitude === null) {
            return $this->decision('location_unavailable', null, true);
        }

        $hasPolygon = $campus->campus_boundary !== null;

        if ($hasPolygon) {
            return $this->checkPointWithPolygon($latitude, $longitude, $campus);
        }

        return $this->checkPointWithCircle($latitude, $longitude, $campus);
    }

    private function checkPointWithPolygon(float $latitude, float $longitude, Setting $campus): array
    {
        $bufferMeters = (int) ($campus->campus_boundary_buffer_meters ?? 20);

        $result = DB::selectOne("
            SELECT ST_Within(ST_GeomFromText(?), campus_boundary) AS is_inside
            FROM settings WHERE id = ?
        ", [
            "POINT($longitude $latitude)",
            $campus->id,
        ]);

        $isInside = ! empty($result->is_inside);

        if ($isInside) {
            $decision = $this->decision('inside_pass', true, false);
            $decision['distance_meters'] = 0.0;
            return $decision;
        }

        $vertices = $campus->campus_boundary_vertices;
        if (empty($vertices)) {
            return $this->decision('outside_flagged', false, true);
        }

        $cntLat = array_sum(array_column($vertices, 'lat')) / count($vertices);
        $cntLng = array_sum(array_column($vertices, 'lng')) / count($vertices);

        $distanceMeters = self::haversineDistanceMeters($cntLat, $cntLng, $latitude, $longitude);

        if ($distanceMeters <= $bufferMeters) {
            $decision = $this->decision('near_boundary_review', false, true);
            $decision['distance_meters'] = $distanceMeters;
            return $decision;
        }

        $decision = $this->decision('outside_flagged', false, true);
        $decision['distance_meters'] = $distanceMeters;
        return $decision;
    }

    private function checkPointWithCircle(float $latitude, float $longitude, Setting $campus): array
    {
        $radiusMeters = $campus->studentGeofencePassMeters();
        $bufferMeters = $campus->studentGeofenceBufferMeters();

        $distanceMeters = self::haversineDistanceMeters(
            (float) $campus->campus_lat,
            (float) $campus->campus_lng,
            $latitude,
            $longitude
        );

        if ($distanceMeters <= $radiusMeters) {
            $decision = $this->decision('inside_pass', true, false);
            $decision['distance_meters'] = $distanceMeters;
            return $decision;
        }

        if ($distanceMeters <= ($radiusMeters + $bufferMeters)) {
            $decision = $this->decision('near_boundary_review', false, true);
            $decision['distance_meters'] = $distanceMeters;
            return $decision;
        }

        $decision = $this->decision('outside_flagged', false, true);
        $decision['distance_meters'] = $distanceMeters;
        return $decision;
    }

    private function decision(string $geofenceStatus, ?bool $isWithinCampus, bool $reviewRequired): array
    {
        return [
            'geofence_status' => $geofenceStatus,
            'review_required' => $reviewRequired,
            'resolution_status' => $reviewRequired ? 'pending' : 'not_needed',
            'is_within_campus' => $isWithinCampus,
            'location_unavailable' => $geofenceStatus === 'location_unavailable',
            'distance_meters' => null,
        ];
    }

    public static function haversineDistanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public static function verticesToPolygonWkt(array $vertices): string
    {
        $points = [];
        foreach ($vertices as $v) {
            $points[] = ($v['lng'] ?? $v[1]) . ' ' . ($v['lat'] ?? $v[0]);
        }
        if (count($points) >= 3) {
            $points[] = $points[0];
        }
        return 'POLYGON((' . implode(',', $points) . '))';
    }

    public static function polygonWktToVertices(?string $wkt): array
    {
        if (!$wkt) {
            return [];
        }
        $wkt = trim((string) $wkt);
        if (!str_starts_with($wkt, 'POLYGON')) {
            return [];
        }
        preg_match('/\(\(([^)]+)\)\)/', $wkt, $m);
        if (empty($m[1])) {
            return [];
        }
        $pairs = explode(',', $m[1]);
        $vertices = [];
        foreach ($pairs as $pair) {
            $pair = trim($pair);
            $parts = preg_split('/\s+/', $pair);
            if (count($parts) >= 2) {
                $lng = (float) $parts[0];
                $lat = (float) $parts[1];
                $vertices[] = ['lat' => $lat, 'lng' => $lng];
            }
        }
        // Remove the closing duplicate
        if (count($vertices) > 1) {
            $last = $vertices[count($vertices) - 1];
            $first = $vertices[0];
            if (abs($last['lat'] - $first['lat']) < 0.0000001 && abs($last['lng'] - $first['lng']) < 0.0000001) {
                array_pop($vertices);
            }
        }
        return $vertices;
    }
}
