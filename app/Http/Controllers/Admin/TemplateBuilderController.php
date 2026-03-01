<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CvTemplate;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TemplateBuilderController extends Controller
{
    /**
     * Display a listing of templates.
     */
    public function index(Request $request): Response
    {
        $query = CvTemplate::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $templates = $query->latest()->paginate(12);

        return Inertia::render('admin/Templates', [
            'templates' => $templates,
            'categories' => CvTemplate::categories(),
            'filters' => $request->only(['search', 'category', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new template.
     */
    public function create(): Response
    {
        return Inertia::render('admin/TemplateBuilder', [
            'template' => null,
            'categories' => CvTemplate::categories(),
            'defaultStyles' => CvTemplate::defaultStyles(),
            'defaultSections' => CvTemplate::defaultSections(),
            'defaultLayout' => CvTemplate::defaultLayout(),
        ]);
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'is_premium' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'layout' => 'nullable',
            'styles' => 'nullable',
            'sections' => 'nullable',
            'html_content' => 'nullable|string',
            'css_content' => 'nullable|string',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:1024',
        ]);

        $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(6);

        // Decode JSON strings from FormData
        if (isset($validated['layout']) && is_string($validated['layout'])) {
            $validated['layout'] = json_decode($validated['layout'], true);
        }
        if (isset($validated['styles']) && is_string($validated['styles'])) {
            $validated['styles'] = json_decode($validated['styles'], true);
        }
        if (isset($validated['sections']) && is_string($validated['sections'])) {
            $validated['sections'] = json_decode($validated['sections'], true);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('templates', 'public');
        }

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('templates/thumbnails', 'public');
        }

        $template = CvTemplate::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Template created successfully.',
            'template' => $template,
        ]);
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit(CvTemplate $template): Response
    {
        return Inertia::render('admin/TemplateBuilder', [
            'template' => $template,
            'categories' => CvTemplate::categories(),
            'defaultStyles' => CvTemplate::defaultStyles(),
            'defaultSections' => CvTemplate::defaultSections(),
            'defaultLayout' => CvTemplate::defaultLayout(),
        ]);
    }

    /**
     * Update the specified template.
     */
    public function update(Request $request, CvTemplate $template): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'is_premium' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'layout' => 'nullable',
            'styles' => 'nullable',
            'sections' => 'nullable',
            'html_content' => 'nullable|string',
            'css_content' => 'nullable|string',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:1024',
        ]);

        // Decode JSON strings from FormData
        if (isset($validated['layout']) && is_string($validated['layout'])) {
            $validated['layout'] = json_decode($validated['layout'], true);
        }
        if (isset($validated['styles']) && is_string($validated['styles'])) {
            $validated['styles'] = json_decode($validated['styles'], true);
        }
        if (isset($validated['sections']) && is_string($validated['sections'])) {
            $validated['sections'] = json_decode($validated['sections'], true);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('templates', 'public');
        }

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('templates/thumbnails', 'public');
        }

        $template->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Template updated successfully.',
            'template' => $template->fresh(),
        ]);
    }

    /**
     * Remove the specified template.
     */
    public function destroy(CvTemplate $template): JsonResponse
    {
        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted successfully.',
        ]);
    }

    /**
     * Toggle template active status.
     */
    public function toggleStatus(CvTemplate $template): JsonResponse
    {
        $template->update(['is_active' => ! $template->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Template status updated.',
            'is_active' => $template->is_active,
        ]);
    }

    /**
     * Duplicate a template.
     */
    public function duplicate(CvTemplate $template): JsonResponse
    {
        $newTemplate = $template->replicate();
        $newTemplate->name = $template->name.' (Copy)';
        $newTemplate->slug = Str::slug($newTemplate->name).'-'.Str::random(6);
        $newTemplate->downloads_count = 0;
        $newTemplate->views_count = 0;
        $newTemplate->save();

        return response()->json([
            'success' => true,
            'message' => 'Template duplicated successfully.',
            'template' => $newTemplate,
        ]);
    }

    /**
     * Preview a template with sample data.
     */
    public function preview(CvTemplate $template): Response
    {
        $template->incrementViews();

        return Inertia::render('admin/TemplatePreview', [
            'template' => $template,
            'sampleData' => $this->getSampleCvData(),
        ]);
    }

    /**
     * Generate a template using AI based on a prompt.
     */
    public function generateWithAi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:2000',
            'category' => 'nullable|string',
        ]);

        $gemini = app(GeminiService::class);

        $category = $validated['category'] ?? 'professional';
        $categories = implode(', ', array_keys(CvTemplate::categories()));

        $prompt = <<<PROMPT
You are a professional CV/resume template designer. Based on the user's description, generate a complete CV template configuration.

User's request: {$validated['prompt']}
Preferred category: {$category}
Available categories: {$categories}

Return a valid JSON object with exactly these fields:
- name: A creative template name (string, max 60 chars)
- description: A marketing description of the template (string, 1-2 sentences)
- category: One of [{$categories}]
- styles: An object with these exact keys:
  - primaryColor: hex color (e.g. "#2563eb")
  - secondaryColor: hex color
  - backgroundColor: hex color (usually "#ffffff")
  - textColor: hex color
  - headingColor: hex color
  - accentColor: hex color
  - fontFamily: CSS font stack (e.g. "Inter, sans-serif")
  - headingFont: CSS font stack
  - fontSize: "12px" or "14px" or "16px"
  - lineHeight: number as string (e.g. "1.6")
  - spacing: "compact" or "normal" or "relaxed"
  - borderRadius: CSS value (e.g. "4px")
- layout: An object with:
  - columns: 1 or 2
  - headerStyle: "centered" or "left" or "split"
  - sidebarPosition: "none" or "left" or "right"
  - sectionStyle: "simple" or "boxed" or "underlined" or "accent"
- sections: An array of objects, each with:
  - id: one of ["header", "summary", "experience", "education", "skills", "projects", "certifications", "languages"]
  - name: Display name for the section
  - enabled: boolean
  - order: integer starting from 0
- html_content: A complete standalone HTML template for a CV. Use placeholder variables like {{full_name}}, {{email}}, {{phone}}, {{location}}, {{linkedin}}, {{website}}, {{summary}}, {{experience}}, {{education}}, {{skills}}, {{certifications}}, {{languages}}, {{interests}}. Include inline CSS styling that matches the styles object. Make it print-friendly and visually professional.
- css_content: Additional CSS rules for the template (can be empty string if all styles are inline)

Make the design creative, modern, and visually appealing based on the user's request. The HTML template should be a complete A4-sized document.

Return ONLY valid JSON, no markdown or explanation.
PROMPT;

        $response = $gemini->generateContent($prompt);
        $text = $gemini->extractText($response);

        if (! $text) {
            return response()->json([
                'success' => false,
                'message' => 'AI failed to generate template. Please try again.',
            ], 422);
        }

        // Parse JSON from response
        $parsed = null;
        if (preg_match('/```json\s*(\{.*\})\s*```/s', $text, $matches)) {
            $text = $matches[1];
        } elseif (preg_match('/(\{.*\})/s', $text, $matches)) {
            $text = $matches[1];
        }

        try {
            $parsed = json_decode(trim($text), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return response()->json([
                'success' => false,
                'message' => 'AI returned invalid data. Please try again with a clearer description.',
            ], 422);
        }

        // Validate required keys exist
        $requiredKeys = ['name', 'styles', 'sections'];
        foreach ($requiredKeys as $key) {
            if (! isset($parsed[$key])) {
                return response()->json([
                    'success' => false,
                    'message' => "AI response missing required field: {$key}. Please try again.",
                ], 422);
            }
        }

        return response()->json([
            'success' => true,
            'template' => $parsed,
        ]);
    }

    /**
     * Get sample CV data for preview.
     */
    private function getSampleCvData(): array
    {
        return [
            'personal_info' => [
                'full_name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1 (555) 123-4567',
                'location' => 'New York, NY',
                'linkedin' => 'linkedin.com/in/johndoe',
                'website' => 'johndoe.com',
            ],
            'summary' => 'Experienced software engineer with 8+ years of expertise in full-stack development. Passionate about building scalable applications and leading engineering teams.',
            'experience' => [
                [
                    'title' => 'Senior Software Engineer',
                    'company' => 'Tech Corp',
                    'location' => 'San Francisco, CA',
                    'start_date' => '2020-01',
                    'end_date' => 'Present',
                    'description' => 'Led development of microservices architecture serving 10M+ users.',
                ],
                [
                    'title' => 'Software Engineer',
                    'company' => 'StartupXYZ',
                    'location' => 'New York, NY',
                    'start_date' => '2017-06',
                    'end_date' => '2019-12',
                    'description' => 'Built and maintained core product features using React and Node.js.',
                ],
            ],
            'education' => [
                [
                    'degree' => 'Bachelor of Science in Computer Science',
                    'institution' => 'MIT',
                    'location' => 'Cambridge, MA',
                    'graduation_date' => '2017',
                ],
            ],
            'skills' => ['JavaScript', 'TypeScript', 'React', 'Node.js', 'Python', 'AWS', 'Docker', 'PostgreSQL'],
            'languages' => [
                ['name' => 'English', 'level' => 'Native'],
                ['name' => 'Spanish', 'level' => 'Intermediate'],
            ],
        ];
    }
}
