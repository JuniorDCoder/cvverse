<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OnboardingController;
use App\Models\ChatSession;
use App\Models\CoverLetter;
use App\Models\Cv;
use App\Models\CvTemplate;
use App\Models\JobApplication;
use App\Models\PricingPlan;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
    public function analytics(Request $request): Response
    {
        [$period, $startDate, $endDate] = $this->resolveAnalyticsDateRange($request);

        $availableCurrencies = PricingPlan::query()
            ->where('is_active', true)
            ->whereNotNull('currency')
            ->distinct()
            ->orderBy('currency')
            ->pluck('currency')
            ->map(fn ($currency) => strtoupper((string) $currency))
            ->values()
            ->all();

        $selectedCurrency = strtoupper((string) $request->input('currency', $this->preferredCurrency($availableCurrencies)));
        if ($selectedCurrency !== 'ALL' && ! in_array($selectedCurrency, $availableCurrencies, true)) {
            $selectedCurrency = $this->preferredCurrency($availableCurrencies);
        }

        $usersQuery = User::query();
        $this->applyDateRange($usersQuery, 'created_at', $startDate, $endDate);

        $cvsQuery = Cv::query();
        $this->applyDateRange($cvsQuery, 'created_at', $startDate, $endDate);

        $coverLettersQuery = CoverLetter::query();
        $this->applyDateRange($coverLettersQuery, 'created_at', $startDate, $endDate);

        $applicationsQuery = JobApplication::query();
        $this->applyDateRange($applicationsQuery, 'created_at', $startDate, $endDate);

        $newSignups = (clone $usersQuery)->count();
        $totalCvs = (clone $cvsQuery)->count();
        $totalCoverLetters = (clone $coverLettersQuery)->count();
        $totalApplications = (clone $applicationsQuery)->count();

        $onboardedUsersInRange = User::query()
            ->where('onboarding_completed', true)
            ->whereNotNull('onboarding_completed_at');
        $this->applyDateRange($onboardedUsersInRange, 'onboarding_completed_at', $startDate, $endDate);
        $onboardingCompletionRate = $newSignups > 0
            ? round(($onboardedUsersInRange->count() / $newSignups) * 100, 1)
            : 0.0;

        $activeSubscriptions = $this->activeSubscriptionsQuery();
        $activeSubscribers = (clone $activeSubscriptions)->count();

        $newPaidSubscribersQuery = $this->activeSubscriptionsQuery();
        $this->applyDateRange($newPaidSubscribersQuery, 'users.updated_at', $startDate, $endDate);
        $newPaidSubscribers = (clone $newPaidSubscribersQuery)->count();

        $revenueByCurrency = $this->buildRevenueByCurrency($this->activeSubscriptionsQuery());
        $selectedRevenue = $this->selectedRevenueMetrics($revenueByCurrency, $selectedCurrency);
        $bookingsInRange = $this->calculateBookings($newPaidSubscribersQuery);

        $userGrowthSeries = $this->buildDailySeries(
            User::query(),
            'created_at',
            $startDate,
            $endDate
        );
        $cvGrowthSeries = $this->buildDailySeries(
            Cv::query(),
            'created_at',
            $startDate,
            $endDate
        );
        $applicationGrowthSeries = $this->buildDailySeries(
            JobApplication::query(),
            'created_at',
            $startDate,
            $endDate
        );

        $statusCounts = JobApplication::query();
        $this->applyDateRange($statusCounts, 'created_at', $startDate, $endDate);
        $statusCountMap = $statusCounts
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $applicationsByStatus = collect(JobApplication::STATUSES)->map(fn ($status) => [
            'status' => $status,
            'label' => Str::headline($status),
            'count' => (int) ($statusCountMap[$status] ?? 0),
        ])->values();

        $topTemplates = $this->buildTopTemplates($startDate, $endDate);
        $topIndustries = $this->buildTopIndustries($startDate, $endDate);
        $topInterests = $this->buildTopInterests($startDate, $endDate);

        $dateLabel = $this->formatDateLabel($period, $startDate, $endDate);

        return Inertia::render('admin/Analytics', [
            'filters' => [
                'period' => $period,
                'currency' => $selectedCurrency,
                'start_date' => $startDate?->toDateString(),
                'end_date' => $endDate?->toDateString(),
            ],
            'filterOptions' => [
                'periods' => [
                    ['value' => 'day', 'label' => 'Today'],
                    ['value' => 'week', 'label' => 'This Week'],
                    ['value' => 'month', 'label' => 'This Month'],
                    ['value' => 'year', 'label' => 'This Year'],
                    ['value' => 'all', 'label' => 'All Time'],
                    ['value' => 'custom', 'label' => 'Custom'],
                ],
                'currencies' => array_values(array_unique(array_merge(['ALL'], $availableCurrencies))),
            ],
            'dateRange' => [
                'label' => $dateLabel,
                'start' => $startDate?->toDateString(),
                'end' => $endDate?->toDateString(),
            ],
            'overview' => [
                'new_signups' => $newSignups,
                'onboarding_completion_rate' => $onboardingCompletionRate,
                'total_cvs' => $totalCvs,
                'total_cover_letters' => $totalCoverLetters,
                'total_applications' => $totalApplications,
                'active_subscribers' => $activeSubscribers,
                'new_paid_subscribers' => $newPaidSubscribers,
                'estimated_mrr' => round((float) $selectedRevenue['mrr'], 2),
                'estimated_arr' => round((float) $selectedRevenue['arr'], 2),
                'estimated_bookings' => round((float) (
                    $selectedCurrency === 'ALL'
                        ? ($bookingsInRange['ALL'] ?? 0)
                        : ($bookingsInRange['by_currency'][$selectedCurrency] ?? 0)
                ), 2),
                'currency' => $selectedCurrency,
                'currency_symbol' => $this->currencySymbol($selectedCurrency),
            ],
            'growth' => [
                'users' => $userGrowthSeries,
                'cvs' => $cvGrowthSeries,
                'applications' => $applicationGrowthSeries,
            ],
            'applicationsByStatus' => $applicationsByStatus,
            'topTemplates' => $topTemplates,
            'topIndustries' => $topIndustries,
            'topInterests' => $topInterests,
            'revenueByCurrency' => $revenueByCurrency->values(),
        ]);
    }

    private function resolveAnalyticsDateRange(Request $request): array
    {
        $period = strtolower((string) $request->input('period', 'year'));
        $now = now();

        return match ($period) {
            'day' => ['day', $now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week' => ['week', $now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'month' => ['month', $now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'year' => ['year', $now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'all' => ['all', null, null],
            'custom' => $this->resolveCustomDateRange($request),
            default => ['year', $now->copy()->startOfYear(), $now->copy()->endOfYear()],
        };
    }

    private function resolveCustomDateRange(Request $request): array
    {
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        if (! $start || ! $end) {
            $now = now();

            return ['year', $now->copy()->startOfYear(), $now->copy()->endOfYear()];
        }

        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->endOfDay();

        if ($endDate->lt($startDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        return ['custom', $startDate, $endDate];
    }

    private function applyDateRange(Builder $query, string $column, ?CarbonInterface $startDate, ?CarbonInterface $endDate): void
    {
        if (! $startDate || ! $endDate) {
            return;
        }

        $query->whereBetween($column, [$startDate, $endDate]);
    }

    private function formatDateLabel(string $period, ?CarbonInterface $startDate, ?CarbonInterface $endDate): string
    {
        return match ($period) {
            'day' => 'Today',
            'week' => 'This Week',
            'month' => 'This Month',
            'year' => 'This Year',
            'all' => 'All Time',
            default => $startDate && $endDate
                ? $startDate->format('M d, Y').' - '.$endDate->format('M d, Y')
                : 'Custom Range',
        };
    }

    private function activeSubscriptionsQuery(): Builder
    {
        return User::query()
            ->join('pricing_plans', 'pricing_plans.id', '=', 'users.pricing_plan_id')
            ->where('users.subscription_status', 'active')
            ->whereNotNull('users.pricing_plan_id')
            ->where(function ($query) {
                $query->whereNull('users.subscription_ends_at')
                    ->orWhere('users.subscription_ends_at', '>=', now());
            });
    }

    private function buildRevenueByCurrency(Builder $activeSubscriptions): Collection
    {
        $rows = (clone $activeSubscriptions)
            ->selectRaw('pricing_plans.currency, pricing_plans.interval, pricing_plans.price, COUNT(users.id) as subscribers')
            ->groupBy('pricing_plans.currency', 'pricing_plans.interval', 'pricing_plans.price')
            ->get();

        return $rows->groupBy(fn ($row) => strtoupper((string) $row->currency))
            ->map(function (Collection $items, string $currency) {
                $mrr = 0.0;
                $arr = 0.0;
                $activeSubscribers = 0;

                foreach ($items as $item) {
                    $count = (int) $item->subscribers;
                    $price = (float) $item->price;
                    $interval = (string) $item->interval;

                    $activeSubscribers += $count;

                    if ($interval === 'monthly') {
                        $mrr += $price * $count;
                        $arr += ($price * 12) * $count;
                    } elseif ($interval === 'yearly') {
                        $mrr += ($price / 12) * $count;
                        $arr += $price * $count;
                    } elseif ($interval === 'one_time') {
                        $arr += $price * $count;
                    }
                }

                return [
                    'currency' => $currency,
                    'active_subscribers' => $activeSubscribers,
                    'mrr' => round($mrr, 2),
                    'arr' => round($arr, 2),
                    'currency_symbol' => $this->currencySymbol($currency),
                ];
            });
    }

    private function selectedRevenueMetrics(Collection $revenueByCurrency, string $selectedCurrency): array
    {
        if ($selectedCurrency !== 'ALL') {
            return $revenueByCurrency->get($selectedCurrency, ['mrr' => 0, 'arr' => 0]);
        }

        return [
            'mrr' => $revenueByCurrency->sum('mrr'),
            'arr' => $revenueByCurrency->sum('arr'),
        ];
    }

    private function calculateBookings(Builder $newPaidSubscribersQuery): array
    {
        $rows = (clone $newPaidSubscribersQuery)
            ->selectRaw('pricing_plans.currency, pricing_plans.price, COUNT(users.id) as subscribers')
            ->groupBy('pricing_plans.currency', 'pricing_plans.price')
            ->get();

        $bookingsByCurrency = $rows->groupBy(fn ($row) => strtoupper((string) $row->currency))
            ->map(fn (Collection $items) => $items->sum(fn ($item) => (float) $item->price * (int) $item->subscribers));

        return [
            'ALL' => round((float) $bookingsByCurrency->sum(), 2),
            'by_currency' => $bookingsByCurrency->map(fn ($value) => round((float) $value, 2)),
        ];
    }

    private function buildDailySeries(Builder $query, string $dateColumn, ?CarbonInterface $startDate, ?CarbonInterface $endDate): array
    {
        $seriesStart = $startDate?->copy() ?? now()->copy()->subDays(29)->startOfDay();
        $seriesEnd = $endDate?->copy() ?? now()->copy()->endOfDay();

        $cloned = clone $query;
        $this->applyDateRange($cloned, $dateColumn, $seriesStart, $seriesEnd);

        $rows = $cloned
            ->selectRaw('DATE('.$dateColumn.') as day, COUNT(*) as count')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('count', 'day');

        $period = CarbonPeriod::create($seriesStart->toDateString(), $seriesEnd->toDateString());

        $series = [];
        foreach ($period as $date) {
            $day = $date->toDateString();
            $series[] = [
                'date' => $day,
                'label' => $date->format('M d'),
                'count' => (int) ($rows[$day] ?? 0),
            ];
        }

        return $series;
    }

    private function buildTopTemplates(?CarbonInterface $startDate, ?CarbonInterface $endDate): array
    {
        $templateRows = Cv::query();
        $this->applyDateRange($templateRows, 'created_at', $startDate, $endDate);

        $top = $templateRows
            ->selectRaw('template, COUNT(*) as usage_count')
            ->groupBy('template')
            ->orderByDesc('usage_count')
            ->limit(8)
            ->get();

        if ($top->isEmpty() && $startDate && $endDate) {
            $top = Cv::query()
                ->selectRaw('template, COUNT(*) as usage_count')
                ->groupBy('template')
                ->orderByDesc('usage_count')
                ->limit(8)
                ->get();
        }

        $names = CvTemplate::query()->pluck('name', 'slug');

        if ($top->isEmpty()) {
            $fromTemplateStats = CvTemplate::query()
                ->select('slug', 'name', 'downloads_count', 'views_count')
                ->where('is_active', true)
                ->orderByDesc(DB::raw('downloads_count + views_count'))
                ->limit(8)
                ->get();

            return $fromTemplateStats->map(fn ($template) => [
                'slug' => $template->slug,
                'name' => $template->name,
                'count' => (int) $template->downloads_count + (int) $template->views_count,
            ])->values()->all();
        }

        return $top->map(fn ($row) => [
            'slug' => $row->template,
            'name' => $names[$row->template] ?? Str::headline(str_replace(['_', '-'], ' ', (string) $row->template)),
            'count' => (int) $row->usage_count,
        ])->values()->all();
    }

    private function buildTopIndustries(?CarbonInterface $startDate, ?CarbonInterface $endDate): array
    {
        $industryLabels = OnboardingController::getIndustries();
        $industryQuery = User::query()
            ->where('onboarding_completed', true)
            ->whereNotNull('industry')
            ->where('industry', '!=', '');

        if ($startDate && $endDate) {
            $industryQuery->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('onboarding_completed_at', [$startDate, $endDate])
                    ->orWhere(function ($inner) use ($startDate, $endDate) {
                        $inner->whereNull('onboarding_completed_at')
                            ->whereBetween('created_at', [$startDate, $endDate]);
                    });
            });
        }

        $rows = $industryQuery
            ->selectRaw('industry, COUNT(*) as count')
            ->groupBy('industry')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        if ($rows->isEmpty() && $startDate && $endDate) {
            $rows = User::query()
                ->where('onboarding_completed', true)
                ->whereNotNull('industry')
                ->where('industry', '!=', '')
                ->selectRaw('industry, COUNT(*) as count')
                ->groupBy('industry')
                ->orderByDesc('count')
                ->limit(8)
                ->get();
        }

        $total = max(1, (int) $rows->sum('count'));

        return $rows->map(fn ($row) => [
            'industry' => $row->industry,
            'label' => $industryLabels[$row->industry] ?? Str::headline(str_replace('_', ' ', (string) $row->industry)),
            'count' => (int) $row->count,
            'percentage' => round(((int) $row->count / $total) * 100, 1),
        ])->values()->all();
    }

    private function buildTopInterests(?CarbonInterface $startDate, ?CarbonInterface $endDate): array
    {
        $interestLabels = OnboardingController::getInterests();
        $interestsQuery = User::query()
            ->where('onboarding_completed', true)
            ->whereNotNull('interests');

        if ($startDate && $endDate) {
            $interestsQuery->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('onboarding_completed_at', [$startDate, $endDate])
                    ->orWhere(function ($inner) use ($startDate, $endDate) {
                        $inner->whereNull('onboarding_completed_at')
                            ->whereBetween('created_at', [$startDate, $endDate]);
                    });
            });
        }

        $interestUsers = $interestsQuery->get(['interests']);

        if ($interestUsers->isEmpty() && $startDate && $endDate) {
            $interestUsers = User::query()
                ->where('onboarding_completed', true)
                ->whereNotNull('interests')
                ->get(['interests']);
        }

        $counts = [];
        foreach ($interestUsers as $user) {
            $interests = is_array($user->interests) ? $user->interests : [];
            foreach ($interests as $interest) {
                if (! is_string($interest) || trim($interest) === '') {
                    continue;
                }

                $counts[$interest] = ($counts[$interest] ?? 0) + 1;
            }
        }

        arsort($counts);
        $top = array_slice($counts, 0, 8, true);
        $total = max(1, array_sum($top));

        return collect($top)->map(fn ($count, $key) => [
            'key' => $key,
            'label' => $interestLabels[$key] ?? Str::headline(str_replace('_', ' ', (string) $key)),
            'count' => (int) $count,
            'percentage' => round(((int) $count / $total) * 100, 1),
        ])->values()->all();
    }

    private function preferredCurrency(array $availableCurrencies): string
    {
        if (in_array('XAF', $availableCurrencies, true)) {
            return 'XAF';
        }

        return $availableCurrencies[0] ?? 'ALL';
    }

    private function currencySymbol(string $currency): string
    {
        return match (strtoupper($currency)) {
            'USD' => '$',
            'EUR' => 'EUR',
            'GBP' => 'GBP',
            'XAF' => 'XAF',
            'ALL' => '',
            default => strtoupper($currency),
        };
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
