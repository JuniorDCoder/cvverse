<?php

namespace App\Http\Controllers;

use App\Models\Cv;
use App\Models\CvTemplate;
use App\Models\JobApplication;
use App\Services\GeminiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class CvController extends Controller
{
    public function __construct(
        private readonly GeminiService $geminiService
    ) {}

    /**
     * Generate a new CV using AI
     */
    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'template' => 'required|string|exists:cv_templates,slug',
            'job_application_id' => 'required|exists:job_applications,id',
        ]);

        $user = Auth::user();
        $job = JobApplication::findOrFail($validated['job_application_id']);

        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'location' => $user->location,
            'job_title' => $user->job_title,
            'industry' => $user->industry,
            'experience_level' => $user->experience_level,
            'bio' => $user->bio,
            'skills' => $user->skills ?? [], // assuming user has skills? No, user model doesn't have explicit skills column, strictly speaking. 'interests' is array.
            'interests' => $user->interests,
        ];

        $jobData = [
            'title' => $job->title,
            'company' => $job->company?->name,
            'description' => $job->description,
            'requirements' => $job->requirements,
            'skills' => $job->skills,
        ];

        $generatedCv = $this->geminiService->generateCvFromJob($userData, $jobData);

        if (! $generatedCv) {
            return back()->withErrors(['job_application_id' => 'Failed to generate CV. Please try again.']);
        }

        // Create the CV
        $cv = $user->cvs()->create([
            'name' => $validated['name'],
            'template' => $validated['template'],
            'is_primary' => false,
            'personal_info' => $generatedCv['personal_info'] ?? [],
            'experience' => $generatedCv['experience'] ?? [],
            'education' => $generatedCv['education'] ?? [],
            'skills' => $generatedCv['skills'] ?? [],
            'languages' => $generatedCv['languages'] ?? [],
            'summary' => $generatedCv['summary'] ?? null,
        ]);

        // Create initial version
        $cv->createVersion('Generated from Job Application: '.$job->title);

        return redirect()->route('cvs.show', $cv)
            ->with('success', 'CV generated successfully!');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $cvs = Auth::user()->cvs()
            ->withCount('versions')
            ->latest()
            ->paginate(10);

        return Inertia::render('cvs/Index', [
            'cvs' => $cvs,
            'templates' => CvTemplate::where('is_active', true)->pluck('slug'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('cvs/Create', [
            'templates' => CvTemplate::where('is_active', true)->pluck('slug'),
            'jobApplications' => Auth::user()->jobApplications()->select('id', 'title', 'company_id')->with('company:id,name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'template' => 'nullable|string|exists:cv_templates,slug',
            'is_primary' => 'nullable|boolean',
            'personal_info' => 'nullable|array',
            'experience' => 'nullable|array',
            'education' => 'nullable|array',
            'skills' => 'nullable|array',
            'projects' => 'nullable|array',
            'certifications' => 'nullable|array',
            'languages' => 'nullable|array',
            'summary' => 'nullable|string',
        ]);

        $user = Auth::user();

        // If this is set as primary, unset other primary CVs
        if ($validated['is_primary'] ?? false) {
            $user->cvs()->update(['is_primary' => false]);
        }

        $cv = $user->cvs()->create($validated);

        // Create initial version
        $cv->createVersion('Initial version');

        return redirect()->route('cvs.show', $cv)
            ->with('success', 'CV created successfully!');
    }

    /**
     * Upload and parse a CV file
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'name' => 'required|string|max:255',
            'template' => 'required|string|exists:cv_templates,slug',
        ]);

        $file = $request->file('file');
        $path = $file->store('cv-uploads', 'local');

        // If setting as primary, unset other primary CVs
        if ($request->boolean('is_primary')) {
            $request->user()->cvs()->where('is_primary', true)->update(['is_primary' => false]);
        }

        // Attempt to extract structured data from the uploaded file using GeminiService
        $extracted = null;
        try {
            $extracted = $this->geminiService->generateCvSuggestions([
                // Empty initial data, just parse the file
            ], [], $path);
        } catch (\Throwable $e) {
            $extracted = null;
        }

        $personal_info = $extracted['personal_info'] ?? null;
        $experience = $extracted['experience'] ?? null;
        $education = $extracted['education'] ?? null;
        $skills = $extracted['skills'] ?? null;
        $projects = $extracted['projects'] ?? null;
        $certifications = $extracted['certifications'] ?? null;
        $languages = $extracted['languages'] ?? null;
        $summary = $extracted['summary'] ?? null;
        $ai_suggestions = $extracted['ai_suggestions'] ?? null;

        $cv = $request->user()->cvs()->create([
            'name' => $request->name,
            'template' => $request->template,
            'is_primary' => $request->boolean('is_primary'),
            'file_path' => $path,
            'personal_info' => $personal_info,
            'experience' => $experience,
            'education' => $education,
            'skills' => $skills,
            'projects' => $projects,
            'certifications' => $certifications,
            'languages' => $languages,
            'summary' => $summary,
            'ai_suggestions' => $ai_suggestions,
        ]);

        return response()->json([
            'success' => true,
            'cv' => $cv,
            'message' => 'CV uploaded and parsed successfully!',
        ]);
    }

    /**
     * Serve the uploaded CV file
     */
    public function file(Cv $cv): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('view', $cv);

        if (! $cv->file_path || ! Storage::disk('local')->exists($cv->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->response($cv->file_path);
    }

    /**
     * Display the specified resource.
     */
    public function show(Cv $cv): Response
    {
        $this->authorize('view', $cv);

        $cv->load(['versions', 'comments.user', 'comments.share']);

        return Inertia::render('cvs/Show', [
            'cv' => $cv,
            'templates' => CvTemplate::where('is_active', true)->pluck('slug'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cv $cv): Response
    {
        $this->authorize('update', $cv);

        $cv->load('versions');

        return Inertia::render('cvs/Edit', [
            'cv' => $cv,
            'templates' => CvTemplate::where('is_active', true)->pluck('slug'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cv $cv): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $cv);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'template' => 'nullable|string|exists:cv_templates,slug',
            'is_primary' => 'nullable|boolean',
            'personal_info' => 'nullable|array',
            'experience' => 'nullable|array',
            'education' => 'nullable|array',
            'skills' => 'nullable|array',
            'projects' => 'nullable|array',
            'certifications' => 'nullable|array',
            'languages' => 'nullable|array',
            'summary' => 'nullable|string',
            'create_version' => 'nullable|boolean',
            'version_summary' => 'nullable|string',
        ]);

        $user = Auth::user();

        // If this is set as primary, unset other primary CVs
        if ($validated['is_primary'] ?? false) {
            $user->cvs()->where('id', '!=', $cv->id)->update(['is_primary' => false]);
        }

        // Create version before updating if requested
        if ($validated['create_version'] ?? false) {
            $cv->createVersion($validated['version_summary'] ?? 'Updated CV');
        }

        unset($validated['create_version'], $validated['version_summary']);

        $cv->update($validated);

        return redirect()->route('cvs.show', $cv)
            ->with('success', 'CV updated successfully!');
    }

    /**
     * Generate AI suggestions for the CV
     */
    public function suggestions(Cv $cv, Request $request): JsonResponse
    {
        $this->authorize('update', $cv);

        $jobApplicationId = $request->input('job_application_id');
        $jobData = null;

        if ($jobApplicationId) {
            $job = JobApplication::find($jobApplicationId);
            if ($job) {
                $jobData = [
                    'title' => $job->title,
                    'description' => $job->description,
                    'requirements' => $job->requirements,
                    'skills' => $job->skills,
                ];
            }
        }

        $cvData = [
            'personal_info' => $cv->personal_info,
            'experience' => $cv->experience,
            'education' => $cv->education,
            'skills' => $cv->skills,
            'projects' => $cv->projects,
            'summary' => $cv->summary,
        ];

        $suggestions = $this->geminiService->generateCvSuggestions($cvData, $jobData ?? [], $cv->file_path);

        if (! $suggestions) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate suggestions. Please try again.',
            ], 500);
        }

        $cv->update(['ai_suggestions' => $suggestions]);

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Generate or improve summary with AI
     */
    public function generateSummary(Cv $cv, Request $request): JsonResponse
    {
        $this->authorize('update', $cv);

        $jobApplicationId = $request->input('job_application_id');
        $jobData = null;

        if ($jobApplicationId) {
            $job = JobApplication::find($jobApplicationId);
            if ($job) {
                $jobData = [
                    'title' => $job->title,
                    'description' => $job->description,
                    'requirements' => $job->requirements,
                    'skills' => $job->skills,
                ];
            }
        }

        $cvData = [
            'personal_info' => $cv->personal_info,
            'experience' => $cv->experience,
            'education' => $cv->education,
            'skills' => $cv->skills,
        ];

        $summary = $this->geminiService->generateSummary($cvData, $jobData, $cv->file_path);

        if (! $summary) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate summary. Please try again.',
            ], 500);
        }

        $cv->update(['summary' => $summary]);

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }

    /**
     * Rewrite a section with AI
     */
    public function rewriteSection(Cv $cv, Request $request): JsonResponse
    {
        $this->authorize('update', $cv);

        $validated = $request->validate([
            'section' => 'required|string|in:experience,education,skills,summary',
            'content' => 'required|string',
            'job_application_id' => 'nullable|exists:job_applications,id',
        ]);

        $jobData = null;
        if ($validated['job_application_id'] ?? null) {
            $job = JobApplication::find($validated['job_application_id']);
            if ($job) {
                $jobData = [
                    'title' => $job->title,
                    'description' => $job->description,
                    'requirements' => $job->requirements,
                    'skills' => $job->skills,
                ];
            }
        }

        $result = $this->geminiService->rewriteSection(
            $validated['section'],
            $validated['content'],
            $jobData
        );

        if (! $result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to rewrite section. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'result' => $result,
        ]);
    }

    /**
     * Get version history
     */
    public function versions(Cv $cv): JsonResponse
    {
        $this->authorize('view', $cv);

        $versions = $cv->versions()->with('jobApplication:id,title')->get();

        return response()->json([
            'success' => true,
            'versions' => $versions,
        ]);
    }

    /**
     * Restore a version
     */
    public function restoreVersion(Cv $cv, int $versionId): JsonResponse
    {
        $this->authorize('update', $cv);

        $version = $cv->versions()->findOrFail($versionId);

        // Create a new version before restoring
        $cv->createVersion('Before restoring to version '.$version->version_number);

        // Restore the content
        $cv->update([
            'personal_info' => $version->content['personal_info'] ?? $cv->personal_info,
            'experience' => $version->content['experience'] ?? $cv->experience,
            'education' => $version->content['education'] ?? $cv->education,
            'skills' => $version->content['skills'] ?? $cv->skills,
            'projects' => $version->content['projects'] ?? $cv->projects,
            'certifications' => $version->content['certifications'] ?? $cv->certifications,
            'languages' => $version->content['languages'] ?? $cv->languages,
            'summary' => $version->content['summary'] ?? $cv->summary,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Version restored successfully!',
            'cv' => $cv->fresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cv $cv): RedirectResponse
    {
        $this->authorize('delete', $cv);

        // Delete uploaded file if exists
        if ($cv->file_path) {
            Storage::disk('local')->delete($cv->file_path);
        }

        $cv->delete();

        return redirect()->route('cvs.index')
            ->with('success', 'CV deleted successfully!');
    }

    /**
     * Chat with AI to edit CV
     */
    public function chat(Cv $cv, Request $request): JsonResponse
    {
        $this->authorize('update', $cv);

        $validated = $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array',
        ]);

        $cvData = [
            'personal_info' => $cv->personal_info,
            'summary' => $cv->summary,
            'experience' => $cv->experience,
            'education' => $cv->education,
            'skills' => $cv->skills,
            'projects' => $cv->projects,
            'certifications' => $cv->certifications,
            'languages' => $cv->languages,
        ];

        $result = $this->geminiService->chatWithCv($cvData, $validated['message'], $validated['history'] ?? []);

        if (! $result) {
            return response()->json(['success' => false, 'message' => 'AI failed to respond.'], 500);
        }

        if (! empty($result['updated_cv'])) {
            $cv->update([
                'personal_info' => $result['updated_cv']['personal_info'] ?? $cv->personal_info,
                'summary' => $result['updated_cv']['summary'] ?? $cv->summary,
                'experience' => $result['updated_cv']['experience'] ?? $cv->experience,
                'education' => $result['updated_cv']['education'] ?? $cv->education,
                'skills' => $result['updated_cv']['skills'] ?? $cv->skills,
                'projects' => $result['updated_cv']['projects'] ?? $cv->projects,
                'certifications' => $result['updated_cv']['certifications'] ?? $cv->certifications,
                'languages' => $result['updated_cv']['languages'] ?? $cv->languages,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'updated_cv' => $cv->fresh(),
            'changes_summary' => $result['changes_summary'] ?? null,
        ]);
    }

    /**
     * Export CV as PDF
     */
    public function exportPdf(Cv $cv)
    {
        $this->authorize('view', $cv);

        $pdf = Pdf::loadView('exports.cv-pdf', ['cv' => $cv]);

        return $pdf->download($cv->name.'.pdf');
    }

    /**
     * Export CV as DOCX
     */
    public function exportDocx(Cv $cv)
    {
        $this->authorize('view', $cv);

        $phpWord = new PhpWord;
        $section = $phpWord->addSection();

        // Personal Info
        $section->addText(
            $cv->personal_info['full_name'] ?? '',
            ['bold' => true, 'size' => 16]
        );
        $section->addText($cv->personal_info['email'] ?? '');
        $section->addText($cv->personal_info['phone'] ?? '');
        $section->addText($cv->personal_info['location'] ?? '');

        // Summary
        if ($cv->summary) {
            $section->addTextBreak(1);
            $section->addText('Summary', ['bold' => true, 'size' => 14]);
            $section->addText($cv->summary);
        }

        // Experience
        if ($cv->experience) {
            $section->addTextBreak(1);
            $section->addText('Experience', ['bold' => true, 'size' => 14]);

            foreach ($cv->experience as $exp) {
                $section->addTextBreak(1);
                $section->addText($exp['title'] ?? '', ['bold' => true]);
                $section->addText(($exp['company'] ?? '').' - '.($exp['location'] ?? ''));
                $section->addText(
                    ($exp['start_date'] ?? '').' - '.
                    ($exp['current'] ? 'Present' : ($exp['end_date'] ?? ''))
                );
                $section->addText($exp['description'] ?? '');
            }
        }

        // Education
        if ($cv->education) {
            $section->addTextBreak(1);
            $section->addText('Education', ['bold' => true, 'size' => 14]);

            foreach ($cv->education as $edu) {
                $section->addTextBreak(1);
                $section->addText($edu['degree'] ?? '', ['bold' => true]);
                $section->addText($edu['institution'] ?? '');
                $section->addText(
                    ($edu['start_date'] ?? '').' - '.($edu['end_date'] ?? '')
                );
            }
        }

        // Skills
        if ($cv->skills) {
            $section->addTextBreak(1);
            $section->addText('Skills', ['bold' => true, 'size' => 14]);
            $section->addText(implode(', ', $cv->skills));
        }

        // Projects
        if ($cv->projects) {
            $section->addTextBreak(1);
            $section->addText('Projects', ['bold' => true, 'size' => 14]);

            foreach ($cv->projects as $project) {
                $section->addTextBreak(1);
                $section->addText($project['title'] ?? '', ['bold' => true]);
                $section->addText($project['description'] ?? '');
            }
        }

        // Languages
        if ($cv->languages) {
            $section->addTextBreak(1);
            $section->addText('Languages', ['bold' => true, 'size' => 14]);

            foreach ($cv->languages as $lang) {
                $section->addText(
                    ($lang['language'] ?? '').' - '.
                    ucfirst($lang['proficiency'] ?? '')
                );
            }
        }

        // Certifications
        if ($cv->certifications) {
            $section->addTextBreak(1);
            $section->addText('Certifications', ['bold' => true, 'size' => 14]);

            foreach ($cv->certifications as $cert) {
                $section->addTextBreak(1);
                $section->addText($cert['name'] ?? '', ['bold' => true]);
                if (! empty($cert['issuer'])) {
                    $section->addText($cert['issuer']);
                }
                if (! empty($cert['date'])) {
                    $section->addText($cert['date'], ['size' => 10, 'color' => '666666']);
                }
            }
        }

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');

        $tempFile = tempnam(sys_get_temp_dir(), 'cv');
        $objWriter->save($tempFile);

        return response()->download($tempFile, $cv->name.'.docx')->deleteFileAfterSend(true);
    }
}
