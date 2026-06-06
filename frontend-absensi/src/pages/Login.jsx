import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../services/api';

export default function Login() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [isLoading, setIsLoading] = useState(false);
    const navigate = useNavigate();

    const handleLogin = async (e) => {
        e.preventDefault();
        setError('');
        setIsLoading(true);

        try {
            const response = await api.post('/login', { email, password });
            localStorage.setItem('auth_token', response.data.access_token);
            navigate('/dashboard');
        } catch (err) {
            setError(err.response?.data?.message || 'Login gagal. Periksa email dan password Anda.');
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-100">
            <div className="bg-white p-8 rounded-xl shadow-xl w-full max-w-md">
                
                {/* Bagian Header Form */}
                <div className="text-center mb-8">
                    <h2 className="text-3xl font-extrabold text-gray-800">Sistem Absensi</h2>
                    <p className="text-gray-500 mt-2 font-medium">Area Operasional Tambang</p>
                </div>

                {/* Notifikasi Error */}
                {error && (
                    <div className="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-md text-sm font-medium">
                        {error}
                    </div>
                )}

                {/* Form Input */}
                <form onSubmit={handleLogin} className="space-y-6">
                    <div>
                        <label className="block text-sm font-bold text-gray-700 mb-1">Email Pegawai</label>
                        <input 
                            type="email" 
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            className="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50 text-gray-900"
                            placeholder="nama@tambang.com"
                            required 
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-bold text-gray-700 mb-1">Password</label>
                        <input 
                            type="password" 
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            className="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50 text-gray-900"
                            placeholder="••••••••"
                            required 
                        />
                    </div>
                    
                    {/* Tombol Login Animasi */}
                    <button 
                        type="submit" 
                        disabled={isLoading}
                        className={`w-full flex justify-center py-3.5 px-4 rounded-lg shadow-md text-white font-bold text-lg tracking-wide ${
                            isLoading 
                            ? 'bg-blue-400 cursor-not-allowed' 
                            : 'bg-blue-600 hover:bg-blue-700 hover:shadow-lg focus:ring-4 focus:ring-blue-300 active:scale-95'
                        } transition-all duration-200`}
                    >
                        {isLoading ? 'Memproses Data...' : 'Masuk ke Sistem'}
                    </button>
                </form>

            </div>
        </div>
    );
}