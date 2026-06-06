<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Location;
use App\Traits\GeofenceTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    use GeofenceTrait;

    /**
     * Endpoint untuk Absen Masuk (Check-in)
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'selfie_image' => 'required|image|max:2048', // Maks 2MB
            'is_fake_gps' => 'required|boolean'
        ]);

        $user = $request->user();
        if ($request->is_fake_gps) {
            return response()->json(['message' => 'Sistem mendeteksi penggunaan aplikasi Lokasi Palsu (Mock Location).'], 403);
        }
        $userLocation = $user->locations()->where('location_id', $request->location_id)->first();
        if (!$userLocation) {
            return response()->json(['message' => 'Anda tidak ditugaskan untuk absen di lokasi ini.'], 403);
        }
        $currentTime = Carbon::now()->format('H:i:s');
        $startTime = Carbon::parse($userLocation->pivot->start_time)->subMinutes(30)->format('H:i:s');
        
        if ($currentTime < $startTime) {
            return response()->json(['message' => 'Jadwal shift Anda belum dimulai.'], 400);
        }
        $location = Location::find($request->location_id);
        $distance = $this->calculateDistance(
            $request->latitude, 
            $request->longitude, 
            $location->latitude, 
            $location->longitude
        );

        if ($distance > $location->radius_meters) {
            return response()->json([
                'message' => 'Anda berada di luar jangkauan area absen.',
                'distance_meters' => round($distance, 2),
                'allowed_radius' => $location->radius_meters
            ], 403);
        }
        $imagePath = $request->file('selfie_image')->store('attendances', 'public');
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'location_id' => $location->id,
            'type' => 'masuk',
            'datetime' => Carbon::now(),
            'lat_recorded' => $request->latitude,
            'long_recorded' => $request->longitude,
            'selfie_image_path' => $imagePath,
            'is_fake_gps' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen masuk berhasil.',
            'data' => $attendance
        ], 201);
    }
}