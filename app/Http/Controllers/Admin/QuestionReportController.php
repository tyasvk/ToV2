<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionReport;
use Illuminate\Http\Request;

class QuestionReportController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Mengambil data laporan beserta relasi User dan Question
        $reports = QuestionReport::with(['user', 'question'])
            ->when($search, function ($query) use ($search) {
                $query->where('reason', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Format data agar bersih saat diterima Vue
        $reports->getCollection()->transform(function ($report) {
            return [
                'id' => $report->id,
                'user_name' => $report->user->name ?? 'User Dihapus',
                'user_email' => $report->user->email ?? '-',
                'question_id' => $report->question_id,
                'question_content' => $report->question->content ?? 'Soal telah dihapus',
                'tryout_id' => $report->question->tryout_id ?? null,
                'reason' => $report->reason,
                'description' => $report->description,
                'status' => $report->status,
                'created_at' => $report->created_at->format('d M Y H:i'),
            ];
        });

        return inertia('Admin/QuestionReport/Index', [
            'reports' => $reports,
            'filters' => $request->only(['search'])
        ]);
    }

    public function updateStatus(Request $request, QuestionReport $report)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,resolved'
        ]);

        $report->update(['status' => $request->status]);

        return back()->with('success', 'Status laporan berhasil diperbarui.');
    }

    public function destroy(QuestionReport $report)
    {
        $report->delete();

        return back()->with('success', 'Data laporan berhasil dihapus.');
    }
}