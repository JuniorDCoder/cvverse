<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CvTemplate;
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
