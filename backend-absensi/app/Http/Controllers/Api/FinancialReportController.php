<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialReport;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    /**
     * GET: Menampilkan daftar laporan keuangan & Kalkulasi Saldo (Admin & Direktur)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Hanya Admin dan Direktur yang boleh mengakses data keuangan
        if (!in_array($user->role, ['admin', 'direktur'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $reports = FinancialReport::latest('transaction_date')->get();

        // Kalkulasi Summary di sisi Server
        $totalIncome = $reports->where('type', 'income')->sum('amount');
        $totalExpense = $reports->where('type', 'expense')->sum('amount');
        $balance = $totalIncome - $totalExpense;

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_income' => $totalIncome,
                    'total_expense' => $totalExpense,
                    'balance' => $balance
                ],
                'reports' => $reports
            ]
        ], 200);
    }

    /**
     * POST: Menambah rekap keuangan (Khusus Admin)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Hanya Admin yang dapat membuat laporan keuangan.'], 403);
        }

        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
        ]);

        $report = FinancialReport::create([
            'admin_id' => $user->id,
            'transaction_date' => $validated['transaction_date'],
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan keuangan berhasil ditambahkan.',
            'data' => $report
        ], 201);
    }

    /**
     * PUT: Mengedit data keuangan (Khusus Admin)
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $report = FinancialReport::findOrFail($id);

        $validated = $request->validate([
            'transaction_date' => 'date',
            'type' => 'in:income,expense',
            'amount' => 'numeric|min:0',
            'description' => 'string',
        ]);

        $report->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Laporan keuangan berhasil diperbarui.',
            'data' => $report
        ], 200);
    }

    /**
     * DELETE: Menghapus data keuangan (Khusus Admin)
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $report = FinancialReport::findOrFail($id);
        $report->delete();

        return response()->json([
            'success' => true,
            'message' => 'Laporan keuangan berhasil dihapus.'
        ], 200);
    }
}