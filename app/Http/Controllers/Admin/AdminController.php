<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\CoverLetter;
use App\Models\Cv;
use App\Models\CvTemplate;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function dashboard(): Response
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('onboarding_completed', true)->count(),
            'total_cvs' => Cv::count(),
            'total_cover_letters' => CoverLetter::count(),
            'total_applications' => JobApplication::count(),
            'total_chat_sessions' => ChatSession::count(),
            'users_today' => User::whereDate('created_at', today())->count(),
            'cvs_today' => Cv::whereDate('created_at', today())->count(),
            'applications_today' => JobApplication::whereDate('created_at', today())->count(),
        ];

        $recentUsers = User::latest()
            ->take(5)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'joined' => $user->created_at->diffForHumans(),
                'onboarding_completed' => $user->onboarding_completed,
            ]);

        return Inertia::render('admin/Dashboard', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
        ]);
    }

    /**
     * Show all users.
     */
    public function users(Request $request): Response
    {
        $query = User::query();

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->get('role') !== 'all') {
            $query->where('role', $request->get('role'));
        }

        $users = $query->latest()
            ->paginate(20)
            ->through(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'onboarding_completed' => $user->onboarding_completed,
                'cvs_count' => $user->cvs()->count(),
                'applications_count' => $user->jobApplications()->count(),
                'cover_letters_count' => $user->coverLetters()->count(),
                'created_at' => $user->created_at->format('M d, Y'),
            ]);

        return Inertia::render('admin/Users', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    /**
     * Show all CVs.
     */
    public function cvs(Request $request): Response
    {
        $query = Cv::with('user');

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $cvs = $query->latest()
            ->paginate(20)
            ->through(fn ($cv) => [
                'id' => $cv->id,
                'name' => $cv->name,
                'template' => $cv->template,
                'is_primary' => $cv->is_primary,
                'user' => [
                    'id' => $cv->user->id,
                    'name' => $cv->user->name,
                    'email' => $cv->user->email,
                ],
                'created_at' => $cv->created_at->format('M d, Y'),
            ]);

        return Inertia::render('admin/Cvs', [
            'cvs' => $cvs,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show all cover letters.
     */
    public function coverLetters(Request $request): Response
    {
        $query = CoverLetter::with('user');

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $coverLetters = $query->latest()
            ->paginate(20)
            ->through(fn ($letter) => [
                'id' => $letter->id,
                'name' => $letter->name,
                'tone' => $letter->tone,
                'user' => [
                    'id' => $letter->user->id,
                    'name' => $letter->user->name,
                    'email' => $letter->user->email,
                ],
                'created_at' => $letter->created_at->format('M d, Y'),
            ]);

        return Inertia::render('admin/CoverLetters', [
            'coverLetters' => $coverLetters,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show all job applications.
     */
    public function applications(Request $request): Response
    {
        $query = JobApplication::with(['user', 'company', 'cv', 'coverLetter']);

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('company', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->get('status') !== 'all') {
            $query->where('status', $request->get('status'));
        }

        $applications = $query->latest()
            ->paginate(20)
            ->through(fn ($app) => [
                'id' => $app->id,
                'title' => $app->title,
                'status' => $app->status,
                'user' => [
                    'id' => $app->user->id,
                    'name' => $app->user->name,
                    'email' => $app->user->email,
                ],
                'company' => $app->company ? [
                    'id' => $app->company->id,
                    'name' => $app->company->name,
                ] : null,
                'created_at' => $app->created_at->format('M d, Y'),
            ]);

        return Inertia::render('admin/Applications', [
            'applications' => $applications,
            'filters' => $request->only(['search', 'status']),
            'statuses' => JobApplication::STATUSES,
        ]);
    }

    /**
     * Show all chat sessions.
     */
    public function chatSessions(Request $request): Response
    {
        $query = ChatSession::with('user');

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $sessions = $query->latest()
            ->paginate(20)
            ->through(fn ($session) => [
                'id' => $session->id,
                'title' => $session->title,
                'user' => [
                    'id' => $session->user->id,
                    'name' => $session->user->name,
                    'email' => $session->user->email,
                ],
                'messages_count' => $session->messages()->count(),
                'created_at' => $session->created_at->format('M d, Y H:i'),
            ]);

        return Inertia::render('admin/ChatSessions', [
            'sessions' => $sessions,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show templates management.
     */
    public function templates(Request $request): Response
    {
        $query = CvTemplate::query();

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('is_active', $status === 'active');
        }

        $templates = $query->latest()->paginate(12)->withQueryString();

        return Inertia::render('admin/Templates', [
            'templates' => $templates,
            'categories' => CvTemplate::categories(),
            'filters' => $request->only(['search', 'category', 'status']),
        ]);
    }

    /**
     * Show analytics.
     */
    public function analytics(): Response
    {
        // User growth over time
        $userGrowth = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => [
                'date' => $item->date,
                'count' => $item->count,
            ]);

        // CVs created over time
        $cvGrowth = Cv::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => [
                'date' => $item->date,
                'count' => $item->count,
            ]);

        // Applications by status
        $applicationsByStatus = JobApplication::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->map(fn ($item) => [
                'status' => $item->status,
                'count' => $item->count,
            ]);

        // Top templates
        $topTemplates = Cv::selectRaw('template, COUNT(*) as count')
            ->groupBy('template')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'template' => $item->template,
                'count' => $item->count,
            ]);

        return Inertia::render('admin/Analytics', [
            'userGrowth' => $userGrowth,
            'cvGrowth' => $cvGrowth,
            'applicationsByStatus' => $applicationsByStatus,
            'topTemplates' => $topTemplates,
        ]);
    }

    /**
     * Show settings.
     */
    public function settings(): Response
    {
        return Inertia::render('admin/Settings', [
            'settings' => \App\Models\SiteSetting::getAllForAdmin(),
        ]);
    }
}
