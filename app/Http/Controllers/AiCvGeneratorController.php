<?php

namespace App\Http\Controllers;

use App\Models\Cv;
use App\Models\CvTemplate;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AiCvGeneratorController extends Controller
{
    public function __construct(
        private readonly GeminiService $geminiService
    ) {}

    /**
     * Display the AI CV Generator page
     */
    public function index(): Response
    {
        return Inertia::render('cvs/AiGenerator', [
            'templates' => CvTemplate::where('is_active', true)->pluck('slug'),
        ]);
    }

    /**
     * Generate CV content based on user prompt
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:5000',
            'context' => 'nullable|string|max:10000',
            'file' => 'nullable|file|mimes:pdf,doc,docx,txt,png,jpg,jpeg|max:10240',
        ]);

        $user = Auth::user();
        $filePath = null;

        // Handle file upload if present
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('cv-generation-uploads', 'local');
        }

        // Build context for AI
        $userContext = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'location' => $user->location,
            'job_title' => $user->job_title,
            'industry' => $user->industry,
            'experience_level' => $user->experience_level,
            'bio' => $user->bio,
        ];

        $prompt = $this->buildCvGenerationPrompt(
            $validated['prompt'],
            $validated['context'] ?? null,
            $userContext
        );

        $response = $this->geminiService->generateContent($prompt, [], $filePath);
        $text = $this->geminiService->extractText($response);

        if (! $text) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate CV. Please try again.',
            ], 500);
        }

        // Parse the CV data from AI response
        $cvData = $this->parseCvResponse($text);

        if (! $cvData) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to parse CV data. Please try again.',
            ], 500);
        }

        // Clean up temp file
        if ($filePath) {
            Storage::disk('local')->delete($filePath);
        }

        return response()->json([
            'success' => true,
            'cv_data' => $cvData,
            'raw_response' => $text,
        ]);
    }

    /**
     * Chat with AI to refine the generated CV
     */
    public function refine(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'cv_data' => 'required|array',
            'history' => 'nullable|array',
        ]);

        $result = $this->geminiService->chatWithCv(
            $validated['cv_data'],
            $validated['message'],
            $validated['history'] ?? []
        );

        if (! $result) {
            return response()->json([
                'success' => false,
                'message' => 'AI failed to respond. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'updated_cv' => $result['updated_cv'] ?? null,
            'changes_summary' => $result['changes_summary'] ?? null,
        ]);
    }

    /**
     * Save the generated CV
     */
    public function save(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'template' => 'required|string|exists:cv_templates,slug',
            'cv_data' => 'required|array',
            'is_primary' => 'nullable|boolean',
        ]);

        $user = Auth::user();

        // If this is set as primary, unset other primary CVs
        if ($validated['is_primary'] ?? false) {
            $user->cvs()->update(['is_primary' => false]);
        }

        $cvData = $validated['cv_data'];

        $cv = $user->cvs()->create([
            'name' => $validated['name'],
            'template' => $validated['template'],
            'is_primary' => $validated['is_primary'] ?? false,
            'personal_info' => $cvData['personal_info'] ?? [],
            'experience' => $cvData['experience'] ?? [],
            'education' => $cvData['education'] ?? [],
            'skills' => $cvData['skills'] ?? [],
            'projects' => $cvData['projects'] ?? [],
            'certifications' => $cvData['certifications'] ?? [],
            'languages' => $cvData['languages'] ?? [],
            'summary' => $cvData['summary'] ?? null,
        ]);

        // Create initial version
        $cv->createVersion('Generated with AI');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cv' => $cv,
                'redirect_url' => route('cvs.show', $cv),
            ]);
        }

        return redirect()->route('cvs.show', $cv)
            ->with('success', 'CV generated and saved successfully!');
    }

    /**
     * Build the CV generation prompt
     */
    private function buildCvGenerationPrompt(string $userPrompt, ?string $context, array $userProfile): string
    {
        $profileJson = json_encode($userProfile, JSON_PRETTY_PRINT);
        $contextSection = $context ? "\n\nAdditional Context Provided:\n{$context}" : '';

        return <<<PROMPT
You are a professional CV/resume writer. Generate a complete, professional CV based on the user's request.

User's Profile Information:
{$profileJson}

User's Request:
{$userPrompt}
{$contextSection}

Generate a JSON object representing the CV content with the following structure:
{
    "personal_info": {
        "full_name": "...",
        "email": "...",
        "phone": "...",
        "location": "...",
        "linkedin": "...",
        "website": "..."
    },
    "summary": "A compelling professional summary (3-4 sentences)...",
    "experience": [
        {
            "company": "...",
            "title": "...",
            "location": "...",
            "start_date": "YYYY-MM",
            "end_date": "YYYY-MM or null for current",
            "current": boolean,
            "description": "Detailed description with bullet points..."
        }
    ],
    "education": [
        {
            "institution": "...",
            "degree": "...",
            "field": "...",
            "start_date": "YYYY-MM",
            "end_date": "YYYY-MM",
            "gpa": "..."
        }
    ],
    "skills": ["Skill 1", "Skill 2", ...],
    "projects": [
        {
            "name": "...",
            "description": "...",
            "url": "...",
            "technologies": ["Tech 1", "Tech 2"]
        }
    ],
    "certifications": [
        {
            "name": "...",
            "issuer": "...",
            "date": "YYYY-MM",
            "url": "..."
        }
    ],
    "languages": [
        {
            "language": "...",
            "proficiency": "Native/Fluent/Advanced/Intermediate/Beginner"
        }
    ]
}

Guidelines:
- Use the user's profile information where available
- If the user provides specific details, incorporate them accurately
- Make the content professional and impactful
- Use action verbs and quantifiable achievements
- If information is not provided, make reasonable professional assumptions
- Tailor the CV to the type of role or industry if mentioned
- Keep descriptions concise but impactful

Return ONLY valid JSON, no markdown or explanation.
PROMPT;
    }

    /**
     * Parse the AI response into structured CV data
     */
    private function parseCvResponse(?string $text): ?array
    {
        if (! $text) {
            return null;
        }

        // Try to find JSON block in markdown
        if (preg_match('/```json\s*(\{.*\}|\[.*\])\s*```/s', $text, $matches)) {
            $text = $matches[1];
        } elseif (preg_match('/(\{.*\})/s', $text, $matches)) {
            $text = $matches[1];
        }

        $text = trim($text);

        try {
            return json_decode($text, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return null;
        }
    }
}
