<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\GeminiService;

class DashboardController extends Controller
{
    public function __construct(
        private readonly GeminiService $geminiService
    ) {}

    public function index(Request $request): Response
    {
        // Redirect admins to admin dashboard
        if ($request->user()?->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $user = Auth::user();

        // Get recent job applications
        $recentApplications = $user->jobApplications()
            ->with('company')
            ->latest()
            ->take(5)
            ->get();

        // Get statistics
        $stats = [
            'total_applications' => $user->jobApplications()->count(),
            'active_applications' => $user->jobApplications()
                ->whereIn('status', [
                    JobApplication::STATUS_APPLIED,
                    JobApplication::STATUS_INTERVIEWING,
                ])
                ->count(),
            'interviews' => $user->jobApplications()
                ->where('status', JobApplication::STATUS_INTERVIEWING)
                ->count(),
            'offers' => $user->jobApplications()
                ->where('status', JobApplication::STATUS_OFFERED)
                ->count(),
            'cvs_count' => $user->cvs()->count(),
            'cover_letters_count' => $user->coverLetters()->count(),
        ];

        // Get application status breakdown
        $statusBreakdown = $user->jobApplications()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Get recent activity (last 7 days)
        $weeklyActivity = $user->jobApplications()
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return Inertia::render('Dashboard', [
            'recentApplications' => $recentApplications,
            'stats' => $stats,
            'statusBreakdown' => $statusBreakdown,
            'weeklyActivity' => $weeklyActivity,
        ]);
    }

    /**
     * Get dashboard statistics (for deferred loading)
     *
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        $user = Auth::user();

        return [
            'total_applications' => $user->jobApplications()->count(),
            'active_applications' => $user->jobApplications()
                ->whereIn('status', [
                    JobApplication::STATUS_APPLIED,
                    JobApplication::STATUS_INTERVIEWING,
                ])
                ->count(),
            'interviews' => $user->jobApplications()
                ->where('status', JobApplication::STATUS_INTERVIEWING)
                ->count(),
            'offers' => $user->jobApplications()
                ->where('status', JobApplication::STATUS_OFFERED)
                ->count(),
        ];
    }

    /**
     * General AI Chat
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array',
        ]);

        $result = $this->geminiService->chatWithAi($validated['message'], $validated['history'] ?? []);

        if (!$result) {
            return response()->json(['success' => false, 'message' => 'AI failed to respond.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }
}
