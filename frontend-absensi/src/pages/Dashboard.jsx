import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../services/api';

export default function Dashboard() {
    const navigate = useNavigate();
    const [location, setLocation] = useState({ lat: '', lng: '' });
    const [photo, setPhoto] = useState(null);
    const [status, setStatus] = useState('');
    const [isLoading, setIsLoading] = useState(false);

    // Proteksi Halaman: Jika tidak ada token, tendang kembali ke Login
    useEffect(() => {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            navigate('/login');
        }
    }, [navigate]);

    // Fungsi membaca GPS perangkat
    const getLocation = () => {
        setStatus('Mencari titik koordinat...');
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    setLocation({
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    });
                    setStatus('✅ Lokasi GPS berhasil dikunci!');
                },
                (error) => {
                    setStatus('❌ Gagal mendapatkan lokasi. Pastikan izin GPS aktif.');
                }
            );
        } else {
            setStatus('❌ Browser Anda tidak mendukung Geolocation.');
        }
    };

    // Fungsi kirim data ke Laravel
    const handleAbsen = async (e) => {
        e.preventDefault();
        if (!location.lat || !location.lng || !photo) {
            setStatus('⚠️ Harap kunci lokasi GPS dan masukkan foto selfie!');
            return;
        }

        setIsLoading(true);
        setStatus('Memproses absensi ke server...');

        // Menggunakan FormData karena kita mengirim file gambar
        const formData = new FormData();
        
        // PENTING: Ganti teks di bawah dengan UUID dari tabel locations di database Anda!
        formData.append('location_id', '019e9c61-2360-7047-bed5-75094cbc555d'); 
        
        formData.append('latitude', location.lat);
        formData.append('longitude', location.lng);
        formData.append('is_fake_gps', '0');
        formData.append('selfie_image', photo); // File gambar

        try {
            const response = await api.post('/attendances/check-in', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            setStatus('🎉 Absen Masuk Berhasil! Status: ' + response.status);
        } catch (err) {
            setStatus('❌ ' + (err.response?.data?.message || 'Terjadi kesalahan saat absen.'));
        } finally {
            setIsLoading(false);
        }
    };

    const handleLogout = () => {
        localStorage.removeItem('auth_token');
        navigate('/login');
    };

    return (
        <div className="min-h-screen bg-gray-100 flex flex-col items-center py-10 px-4">
            <div className="bg-white w-full max-w-md p-6 rounded-2xl shadow-lg relative">
                
                <button onClick={handleLogout} className="absolute top-4 right-4 text-sm text-red-500 font-bold hover:underline">
                    Keluar
                </button>

                <h2 className="text-2xl font-extrabold text-gray-800 mb-6 border-b pb-4">Panel Absensi</h2>

                <div className="space-y-6">
                    {/* Seksi 1: GPS */}
                    <div className="bg-blue-50 p-4 rounded-xl border border-blue-100">
                        <p className="text-sm font-bold text-gray-700 mb-2">1. Titik Lokasi Anda</p>
                        <button 
                            onClick={getLocation}
                            className="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg w-full transition"
                        >
                            📍 Kunci Lokasi GPS Saat Ini
                        </button>
                        <div className="mt-2 text-xs text-gray-500 grid grid-cols-2 gap-2">
                            <span className="bg-white p-2 rounded border">Lat: {location.lat || '-'}</span>
                            <span className="bg-white p-2 rounded border">Lng: {location.lng || '-'}</span>
                        </div>
                    </div>

                    {/* Seksi 2: Foto */}
                    <div className="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <p className="text-sm font-bold text-gray-700 mb-2">2. Foto Bukti (Selfie)</p>
                        <input 
                            type="file" 
                            accept="image/*"
                            capture="user" 
                            onChange={(e) => setPhoto(e.target.files[0])}
                            className="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                        />
                    </div>

                    {/* Notifikasi Status */}
                    {status && (
                        <div className={`p-3 rounded-lg text-sm font-bold text-center ${status.includes('Berhasil') ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'}`}>
                            {status}
                        </div>
                    )}

                    {/* Tombol Eksekusi */}
                    <button 
                        onClick={handleAbsen}
                        disabled={isLoading}
                        className={`w-full py-4 rounded-xl shadow-md text-white font-bold text-lg ${
                            isLoading ? 'bg-green-400 cursor-wait' : 'bg-green-600 hover:bg-green-700 active:scale-95'
                        } transition`}
                    >
                        {isLoading ? 'Mengirim Data...' : 'Kirim Absen Masuk'}
                    </button>
                </div>
            </div>
        </div>
    );
}