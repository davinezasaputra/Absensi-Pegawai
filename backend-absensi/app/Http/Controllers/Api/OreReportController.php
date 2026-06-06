<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OreReport;
use App\Models\ReportComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OreReportController extends Controller
{
    /**
     * GET: Menampilkan daftar laporan
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = OreReport::with('comments'); // Load relasi komentar

        // Jika user adalah pegawai, dia hanya boleh melihat laporannya sendiri
        if ($user->role === 'pegawai') {
            $query->where('user_id', $user->id);
        }
        // Direktur dan Admin akan melewati blok if di atas, sehingga bisa melihat semua laporan

        $reports = $query->latest('report_date')->get();

        return response()->json([
            'success' => true,
            'data' => $reports
        ], 200);
    }

    /**
     * POST: Membuat laporan baru (Khusus Pegawai)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'pegawai') {
            return response()->json(['message' => 'Hanya pegawai yang dapat membuat laporan.'], 403);
        }

        $validated = $request->validate([
            'report_date' => 'required|date',
            'wet_weight_kg' => 'required|numeric|min:0',
            'dry_weight_kg' => 'required|numeric|min:0',
            'sn_grade_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $report = OreReport::create([
            'user_id' => $user->id,
            'report_date' => $validated['report_date'],
            'wet_weight_kg' => $validated['wet_weight_kg'],
            'dry_weight_kg' => $validated['dry_weight_kg'],
            'sn_grade_percentage' => $validated['sn_grade_percentage'],
            'status' => 'pending' // Status default
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan setoran bijih timah berhasil dibuat.',
            'data' => $report
        ], 201);
    }

    /**
     * PUT: Mengedit laporan (Khusus Pegawai, jika status masih 'pending')
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $report = OreReport::findOrFail($id);

        // Validasi Otorisasi
        if ($user->role !== 'pegawai' || $report->user_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Validasi Status Bisnis
        if ($report->status === 'reviewed') {
            return response()->json(['message' => 'Laporan yang sudah dievaluasi (reviewed) tidak dapat diedit.'], 400);
        }

        $validated = $request->validate([
            'wet_weight_kg' => 'numeric|min:0',
            'dry_weight_kg' => 'numeric|min:0',
            'sn_grade_percentage' => 'numeric|min:0|max:100',
        ]);

        $report->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil diperbarui.',
            'data' => $report
        ], 200);
    }

    /**
     * DELETE: Menghapus laporan (Khusus Pegawai, jika status 'pending')
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $report = OreReport::findOrFail($id);

        if ($user->role !== 'pegawai' || $report->user_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($report->status === 'reviewed') {
            return response()->json(['message' => 'Laporan yang sudah dievaluasi tidak dapat dihapus.'], 400);
        }

        $report->delete();

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dihapus.'
        ], 200);
    }

    /**
     * POST: Menambahkan Komentar & Evaluasi (Khusus Direktur)
     */
    public function addComment(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role !== 'direktur') {
            return response()->json(['message' => 'Hanya Direktur yang berwenang memberikan evaluasi.'], 403);
        }

        $request->validate([
            'comment_text' => 'required|string',
            'change_status_to_reviewed' => 'boolean'
        ]);

        $report = OreReport::findOrFail($id);

        // Gunakan DB Transaction agar pembuatan komentar dan perubahan status aman
        DB::beginTransaction();
        try {
            $comment = ReportComment::create([
                'ore_report_id' => $report->id,
                'director_id' => $user->id,
                'comment_text' => $request->comment_text
            ]);

            // Jika direktur mencentang opsi "Tandai sudah dievaluasi"
            if ($request->change_status_to_reviewed && $report->status !== 'reviewed') {
                $report->update(['status' => 'reviewed']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Komentar evaluasi berhasil ditambahkan.',
                'data' => $comment
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan sistem.'], 500);
        }
    }
}