<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\JobApplication;
use App\Services\GeminiService;
use App\Services\JobCrawlerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class JobApplicationController extends Controller
{
    public function __construct(
        private readonly JobCrawlerService $crawlerService,
        private readonly GeminiService $geminiService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $query = $user->jobApplications()->with('company');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('company', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $applications = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('jobs/Index', [
            'applications' => $applications,
            'filters' => $request->only(['status', 'search']),
            'statuses' => JobApplication::STATUSES,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $user = Auth::user();

        return Inertia::render('jobs/Create', [
            'cvs' => $user->cvs()->select('id', 'name')->get(),
            'statuses' => JobApplication::STATUSES,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|array',
            'skills' => 'nullable|array',
            'salary_range' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'work_type' => 'nullable|string|in:remote,hybrid,onsite',
            'experience_level' => 'nullable|string|in:entry,mid,senior,lead',
            'source_url' => 'nullable|url',
            'status' => 'nullable|string|in:'.implode(',', JobApplication::STATUSES),
            'deadline' => 'nullable|date',
            'notes' => 'nullable|string',
            'cv_id' => 'nullable|exists:cvs,id',
        ]);

        $user = Auth::user();

        // Create or find company
        $companyId = null;
        if (! empty($validated['company_name'])) {
            $company = Company::firstOrCreate(
                ['name' => $validated['company_name']],
                ['name' => $validated['company_name']]
            );
            $companyId = $company->id;
        }

        $application = $user->jobApplications()->create([
            'company_id' => $companyId,
            'cv_id' => $validated['cv_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'requirements' => $validated['requirements'] ?? null,
            'skills' => $validated['skills'] ?? null,
            'salary_range' => $validated['salary_range'] ?? null,
            'location' => $validated['location'] ?? null,
            'work_type' => $validated['work_type'] ?? null,
            'experience_level' => $validated['experience_level'] ?? null,
            'source_url' => $validated['source_url'] ?? null,
            'status' => $validated['status'] ?? JobApplication::STATUS_SAVED,
            'deadline' => $validated['deadline'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('jobs.show', $application)
            ->with('success', 'Job application saved successfully!');
    }

    /**
     * Crawl a job URL and extract information
     */
    public function crawl(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        try {
            $data = $this->crawlerService->crawlJobUrl($request->url);

            if (! $data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to extract job information. Please enter details manually.',
                ], 422);
            }

            // Try to get company info if we can extract the domain
            $companyDomain = $this->crawlerService->extractCompanyDomain($request->url);
            if ($companyDomain && ! empty($data['company'])) {
                $companyInfo = $this->geminiService->researchCompany($data['company'], $companyDomain);
                if ($companyInfo) {
                    $data['company_info'] = $companyInfo;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 408); // 408 Request Timeout
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(JobApplication $job): Response
    {
        $job->load(['company', 'cv', 'coverLetter', 'cvVersions']);

        $user = Auth::user();

        return Inertia::render('jobs/Show', [
            'application' => $job,
            'cvs' => $user->cvs()->select('id', 'name')->get(),
            'coverLetters' => $user->coverLetters()->select('id', 'name')->get(),
            'statuses' => JobApplication::STATUSES,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobApplication $job): Response
    {
        $job->load(['company', 'cv', 'coverLetter']);

        $user = Auth::user();

        return Inertia::render('jobs/Edit', [
            'application' => $job,
            'cvs' => $user->cvs()->select('id', 'name')->get(),
            'statuses' => JobApplication::STATUSES,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobApplication $job): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|array',
            'skills' => 'nullable|array',
            'salary_range' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'work_type' => 'nullable|string|in:remote,hybrid,onsite',
            'experience_level' => 'nullable|string|in:entry,mid,senior,lead',
            'source_url' => 'nullable|url',
            'status' => 'nullable|string|in:'.implode(',', JobApplication::STATUSES),
            'applied_at' => 'nullable|date',
            'deadline' => 'nullable|date',
            'notes' => 'nullable|string',
            'cv_id' => 'nullable|exists:cvs,id',
            'cover_letter_id' => 'nullable|exists:cover_letters,id',
        ]);

        // Handle company
        $companyId = $job->company_id;
        if (! empty($validated['company_name'])) {
            $company = Company::firstOrCreate(
                ['name' => $validated['company_name']],
                ['name' => $validated['company_name']]
            );
            $companyId = $company->id;
        }

        $job->update([
            'company_id' => $companyId,
            'cv_id' => $validated['cv_id'] ?? null,
            'cover_letter_id' => $validated['cover_letter_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'requirements' => $validated['requirements'] ?? null,
            'skills' => $validated['skills'] ?? null,
            'salary_range' => $validated['salary_range'] ?? null,
            'location' => $validated['location'] ?? null,
            'work_type' => $validated['work_type'] ?? null,
            'experience_level' => $validated['experience_level'] ?? null,
            'source_url' => $validated['source_url'] ?? null,
            'status' => $validated['status'] ?? $job->status,
            'applied_at' => $validated['applied_at'] ?? null,
            'deadline' => $validated['deadline'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('jobs.show', $job)
            ->with('success', 'Job application updated successfully!');
    }

    /**
     * Update just the status
     */
    public function updateStatus(Request $request, JobApplication $job): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:'.implode(',', JobApplication::STATUSES),
        ]);

        $job->update([
            'status' => $validated['status'],
            'applied_at' => $validated['status'] === JobApplication::STATUS_APPLIED && ! $job->applied_at
                ? now()
                : $job->applied_at,
        ]);

        return response()->json(['success' => true, 'status' => $job->status]);
    }

    /**
     * Analyze application with AI
     */
    public function analyze(JobApplication $job): JsonResponse
    {
        $user = Auth::user();
        $cv = $job->cv ?? $user->primaryCv();

        if (! $cv) {
            return response()->json([
                'success' => false,
                'message' => 'Please create or attach a CV first.',
            ], 422);
        }

        $cvData = [
            'personal_info' => $cv->personal_info,
            'experience' => $cv->experience,
            'education' => $cv->education,
            'skills' => $cv->skills,
            'summary' => $cv->summary,
        ];

        $jobData = [
            'title' => $job->title,
            'description' => $job->description,
            'requirements' => $job->requirements,
            'skills' => $job->skills,
        ];

        $analysis = $this->geminiService->generateCvSuggestions($cvData, $jobData);

        if (! $analysis) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze application. Please try again.',
            ], 500);
        }

        $job->update(['ai_analysis' => $analysis]);

        return response()->json([
            'success' => true,
            'analysis' => $analysis,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobApplication $job): RedirectResponse
    {
        $job->delete();

        return redirect()->route('jobs.index')
            ->with('success', 'Job application deleted successfully!');
    }
}
