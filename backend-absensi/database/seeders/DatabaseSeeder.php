<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Data Pegawai
        $pegawai = User::create([
            'name' => 'Davin',
            'email' => 'davin@tambang.com',
            'password' => Hash::make('password123'),
            'role' => 'pegawai',
        ]);

        // 2. Buat Data Lokasi (Contoh Area Operasional Pangkalpinang)
        $lokasi = Location::create([
            'location_name' => 'Posko Tambang Pangkalpinang',
            'latitude' => -2.12920000,
            'longitude' => 106.11050000,
            'radius_meters' => 200, // Radius toleransi 200 meter
        ]);

        // 3. Hubungkan Pegawai dengan Lokasi beserta Jadwal Shift (Tabel Pivot)
        $pegawai->locations()->attach($lokasi->id, [
            'start_time' => '00:00:00', // Dibuat 00:00 agar Anda bisa test absen kapan saja
            'end_time' => '23:59:59',
        ]);
        
        // Opsional: Buat Data Direktur untuk testing komentar nanti
        User::create([
            'name' => 'Direktur Utama',
            'email' => 'direktur@tambang.com',
            'password' => Hash::make('password123'),
            'role' => 'direktur',
        ]);

        // Opsional: Buat Data Developer untuk testing komentar nanti
        User::create([
            'name' => 'Developer',
            'email' => 'davin-eza@mahasiswa.ubb.ac.id',
            'password' => Hash::make('@DavinEza1213'),
            'role' => 'developer',
        ]);
    }
}