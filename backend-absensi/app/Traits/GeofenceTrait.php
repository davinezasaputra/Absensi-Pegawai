<?php

namespace App\Traits;

trait GeofenceTrait
{
    /**
     * Menghitung jarak antara dua titik koordinat menggunakan Haversine Formula.
     * Mengembalikan nilai jarak dalam satuan meter.
     *
     * @param float $lat1 Latitude titik 1 (User)
     * @param float $lon1 Longitude titik 1 (User)
     * @param float $lat2 Latitude titik 2 (Pusat Lokasi)
     * @param float $lon2 Longitude titik 2 (Pusat Lokasi)
     * @return float
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; 
    }
}