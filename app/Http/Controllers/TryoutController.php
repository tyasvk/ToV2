<?php

namespace App\Http\Controllers;

use App\Models\Tryout;
use App\Models\Question;
use App\Models\ExamSession;
use App\Models\TryoutRegistration; // Tambahan Model Registrasi
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TryoutController extends Controller
{
    // =========================================================================
    // BAGIAN ADMIN: MANAJEMEN TRYOUT
    // =========================================================================

    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        $tryouts = Tryout::query()
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage) 
            ->withQueryString();

        return Inertia::render('Admin/Tryout/Index', [
            'tryouts' => $tryouts,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1', 
            'description' => 'nullable|string',
            'is_paid' => 'required|boolean',
            'price' => 'required_if:is_paid,true|numeric|min:0',
            'started_at' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        if (!$validated['is_paid']) {
            $validated['price'] = 0;
        }

        Tryout::create($validated);
        return redirect()->back()->with('success', 'Tryout berhasil dibuat.');
    }

    public function update(Request $request, Tryout $tryout)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_paid' => 'required|boolean',
            'price' => 'required_if:is_paid,true|numeric|min:0',
            'started_at' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        if (!$validated['is_paid']) {
            $validated['price'] = 0;
        }

        $tryout->update($validated);
        return redirect()->back()->with('success', 'Tryout berhasil diperbarui.');
    }

    public function destroy(Tryout $tryout)
    {
        $tryout->delete();
        return redirect()->back()->with('success', 'Tryout berhasil dihapus.');
    }


    // =========================================================================
    // BAGIAN USER: PENGERJAAN UJIAN
    // =========================================================================

    public function userIndex()
    {
        $user = auth()->user();
        $tryouts = Tryout::all()->map(function ($t) use ($user) {
            $t->has_access = !$t->is_paid || $user->purchasedTryouts()->where('tryout_id', $t->id)->exists();
            return $t;
        });

        return Inertia::render('User/Tryout/Index', [
            'tryouts' => $tryouts,
            'stats' => []
        ]);
    }

    public function show(Tryout $tryout) 
    {
        return Inertia::render('User/Tryout/Show', [
            'tryout' => $tryout->loadCount('questions')
        ]);
    }

    // =========================================================================
    // FITUR BARU: PENDAFTARAN/UPLOAD PERSYARATAN (Nama Fungsi Baru)
    // =========================================================================

    /**
     * Menampilkan form upload persyaratan
     */
/**
     * Menampilkan halaman upload syarat pendaftaran
     */
    public function uploadSyarat(Tryout $tryout)
    {
        $now = now();

        // Validasi: Pastikan masa pendaftaran masih sesuai
        if ($tryout->registration_start_at && $now->lt($tryout->registration_start_at)) {
            return redirect()->route('tryout.show', $tryout->id)
                ->with('error', 'Pendaftaran untuk tryout ini belum dibuka.');
        }

        if ($tryout->registration_end_at && $now->gt($tryout->registration_end_at)) {
            return redirect()->route('tryout.show', $tryout->id)
                ->with('error', 'Pendaftaran untuk tryout ini telah ditutup.');
        }

        return Inertia::render('User/Tryout/UploadSyarat', [
            'tryout' => $tryout
        ]);
    }

    /**
     * Memproses simpan gambar screenshot
     */
    public function storeSyarat(Request $request, Tryout $tryout)
    {
        $request->validate([
            'proof_follow' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'proof_comment' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'proof_follow.required' => 'Bukti follow Instagram wajib diunggah.',
            'proof_follow.image' => 'Bukti follow harus berupa file gambar.',
            'proof_follow.max' => 'Ukuran gambar bukti follow maksimal 2MB.',
            'proof_comment.required' => 'Bukti komentar & tag teman wajib diunggah.',
            'proof_comment.image' => 'Bukti komentar harus berupa file gambar.',
            'proof_comment.max' => 'Ukuran gambar bukti komentar maksimal 2MB.',
        ]);

        // Simpan file ke folder storage/app/public/proofs
        $followPath = $request->file('proof_follow')->store('proofs', 'public');
        $commentPath = $request->file('proof_comment')->store('proofs', 'public');

        TryoutRegistration::create([
            'user_id' => auth()->id(),
            'tryout_id' => $tryout->id,
            'proof_follow' => $followPath,
            'proof_comment' => $commentPath,
            'status' => 'pending', // Default menunggu acc admin
        ]);

        return redirect()->route('tryout.status-syarat', $tryout->id);
    }

    /**
     * Menampilkan halaman status (Menunggu/Disetujui)
     */
    public function statusSyarat(Tryout $tryout)
    {
        $registration = TryoutRegistration::where('user_id', auth()->id())
            ->where('tryout_id', $tryout->id)
            ->firstOrFail();

        return Inertia::render('User/Tryout/StatusSyarat', [
            'tryout' => $tryout->only(['id', 'title']),
            'status' => $registration->status,
        ]);
    }


    // =========================================================================
    // FITUR LAMA: MULAI UJIAN, SUBMIT, & HASIL
    // =========================================================================

    public function start(Tryout $tryout)
    {
        $questions = Question::inRandomOrder()->get();

        $session = ExamSession::create([
            'user_id' => auth()->id(),
            'tryout_id' => $tryout->id,
            'started_at' => now(),
            'status' => 'ongoing'
        ]);

        return Inertia::render('Tryout/ExamSheet', [
            'tryout' => $tryout,
            'questions' => $questions,
            'sessionId' => $session->id,
            'duration' => $tryout->duration ?? $tryout->duration_minutes 
        ]);
    }

    public function submit(Request $request, ExamSession $session)
    {
        $answers = $request->answers; // JSON dari Vue: { question_id: 'a' }
        $scoreTwk = 0; $scoreTiu = 0; $scoreTkp = 0;

        foreach ($answers as $qId => $ans) {
            $question = Question::find($qId);
            if (!$question) continue;

            if ($question->type === 'TKP') {
                $scores = json_decode($question->tkp_scores, true);
                $scoreTkp += $scores[$ans] ?? 0;
            } else {
                if ($question->correct_answer === $ans) {
                    ($question->type === 'TWK') ? $scoreTwk += 5 : $scoreTiu += 5;
                }
            }
        }

        $session->update([
            'score_twk' => $scoreTwk,
            'score_tiu' => $scoreTiu,
            'score_tkp' => $scoreTkp,
            'score_total' => $scoreTwk + $scoreTiu + $scoreTkp,
            'status' => 'completed',
            'ended_at' => now()
        ]);

        return redirect()->route('tryout.results', $session->id);
    }

    public function results(ExamSession $session)
    {
        if ($session->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Tryout/Results', [
            'session' => $session->load('tryout')
        ]);
    }
}