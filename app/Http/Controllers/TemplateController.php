<?php

namespace App\Http\Controllers;

use App\Models\Cv;
use App\Models\CvTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TemplateController extends Controller
{
    /**
     * Display public templates gallery (landing page).
     */
    public function index(Request $request): Response
    {
        $query = CvTemplate::active();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('type')) {
            if ($request->type === 'free') {
                $query->free();
            } elseif ($request->type === 'premium') {
                $query->premium();
            }
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $sort = $request->input('sort', 'popular');
        match ($sort) {
            'newest' => $query->latest(),
            'popular' => $query->orderByDesc('downloads_count'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('downloads_count'),
        };

        $templates = $query->paginate(12);

        return Inertia::render('landing/Templates', [
            'templates' => $templates,
            'categories' => CvTemplate::categories(),
            'filters' => $request->only(['category', 'type', 'search', 'sort']),
            'hasPremiumAccess' => $this->userHasPremiumAccess(),
        ]);
    }

    /**
     * Show template details.
     */
    public function show(CvTemplate $template): Response
    {
        if (! $template->is_active) {
            abort(404);
        }

        $template->incrementViews();

        $relatedTemplates = CvTemplate::active()
            ->where('id', '!=', $template->id)
            ->where('category', $template->category)
            ->limit(4)
            ->get();

        // Generate preview with sample data
        $previewHtml = $this->generateCvHtml($template, $this->getSampleCvData());

        return Inertia::render('templates/Show', [
            'template' => $template,
            'relatedTemplates' => $relatedTemplates,
            'previewHtml' => $previewHtml,
        ]);
    }

    /**
     * Show template editor/customizer for users.
     */
    public function editor(CvTemplate $template): Response
    {
        if (! $template->is_active) {
            abort(404);
        }

        // Get user's CVs if authenticated for importing data
        $userCvs = Auth::check()
            ? Auth::user()->cvs()->select('id', 'name', 'personal_info', 'experience', 'education', 'skills', 'summary')->get()
            : collect();

        // Generate initial preview with empty/sample data
        $previewHtml = $this->generateCvHtml($template, $this->getEmptyCvData());

        return Inertia::render('templates/Editor', [
            'template' => $template,
            'userCvs' => $userCvs,
            'categories' => CvTemplate::categories(),
            'previewHtml' => $previewHtml,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Download template as PDF with user data.
     */
    public function download(Request $request, CvTemplate $template): HttpResponse|JsonResponse
    {
        if (! $template->is_active) {
            abort(404);
        }

        // Check if premium and user has access
        if ($template->is_premium && ! $this->userHasPremiumAccess()) {
            return response()->json([
                'success' => false,
                'message' => 'This is a premium template. Please upgrade your plan to download.',
            ], 403);
        }

        $validated = $request->validate([
            'cv_data' => 'required|array',
            'cv_data.personal_info' => 'required|array',
        ]);

        $cvData = $validated['cv_data'];

        // Generate HTML with user data
        $html = $this->generateCvHtml($template, $cvData);

        // Generate PDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4');

        $template->incrementDownloads();

        $filename = 'cv-'.now()->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Save customized template as user's CV.
     */
    public function saveAsCv(Request $request, CvTemplate $template): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to save your CV.',
            ], 401);
        }

        // Check if premium and user has access
        if ($template->is_premium && ! $this->userHasPremiumAccess()) {
            return response()->json([
                'success' => false,
                'message' => 'This is a premium template. Please upgrade your plan to use it.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cv_data' => 'required|array',
            'cv_data.personal_info' => 'required|array',
        ]);

        $cvData = $validated['cv_data'];

        $cv = Auth::user()->cvs()->create([
            'name' => $validated['name'],
            'template' => $template->slug,
            'personal_info' => $cvData['personal_info'] ?? [],
            'experience' => $cvData['experience'] ?? [],
            'education' => $cvData['education'] ?? [],
            'skills' => $cvData['skills'] ?? [],
            'summary' => $cvData['summary'] ?? '',
            'projects' => $cvData['projects'] ?? [],
            'certifications' => $cvData['certifications'] ?? [],
            'languages' => $cvData['languages'] ?? [],
        ]);

        $template->incrementDownloads();

        return response()->json([
            'success' => true,
            'message' => 'CV saved successfully.',
            'cv' => $cv,
        ]);
    }

    /**
     * Preview template with custom data.
     */
    public function preview(Request $request, CvTemplate $template): JsonResponse|HttpResponse
    {
        $cvData = $request->input('cv_data', $this->getEmptyCvData());

        $html = $this->generateCvHtml($template, $cvData);

        return response()->json([
            'success' => true,
            'html' => $html,
        ]);
    }

    /**
     * Preview template with data from editor form (returns HTML directly).
     */
    public function previewWithData(Request $request, CvTemplate $template): HttpResponse
    {
        // Transform form data to CV data format
        $cvData = [
            'personal_info' => [
                'full_name' => $request->input('full_name', ''),
                'email' => $request->input('email', ''),
                'phone' => $request->input('phone', ''),
                'location' => $request->input('address', ''),
                'linkedin' => $request->input('linkedin', ''),
                'website' => $request->input('website', ''),
            ],
            'summary' => $request->input('summary', ''),
            'experience' => collect($request->input('experiences', []))->map(fn ($exp) => [
                'title' => $exp['job_title'] ?? '',
                'company' => $exp['company'] ?? '',
                'location' => $exp['location'] ?? '',
                'start_date' => $exp['start_date'] ?? '',
                'end_date' => $exp['current'] ? 'Present' : ($exp['end_date'] ?? ''),
                'description' => $exp['description'] ?? '',
            ])->toArray(),
            'education' => collect($request->input('education', []))->map(fn ($edu) => [
                'degree' => $edu['degree'] ?? '',
                'institution' => $edu['school'] ?? '',
                'location' => $edu['location'] ?? '',
                'graduation_date' => $edu['end_date'] ?? '',
                'gpa' => $edu['gpa'] ?? '',
                'description' => $edu['description'] ?? '',
            ])->toArray(),
            'skills' => array_filter($request->input('skills', []), fn ($s) => ! empty($s)),
            'certifications' => collect($request->input('certifications', []))->filter(fn ($c) => ! empty($c['name']))->toArray(),
            'languages' => collect($request->input('languages', []))->filter(fn ($l) => ! empty($l['language']))->toArray(),
            'interests' => array_filter($request->input('interests', []), fn ($i) => ! empty($i)),
        ];

        $html = $this->generateCvHtml($template, $cvData);

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Generate CV HTML from template and data.
     */
    private function generateCvHtml(CvTemplate $template, array $cvData): string
    {
        // Use custom HTML content if available, otherwise generate from sections
        if ($template->html_content) {
            return $this->replacePlaceholders($template->html_content, $cvData, $template->css_content);
        }

        return $this->generateDefaultHtml($template, $cvData);
    }

    /**
     * Replace placeholders in HTML with actual data.
     */
    private function replacePlaceholders(string $html, array $data, ?string $css = null): string
    {
        $personalInfo = $data['personal_info'] ?? [];

        $replacements = [
            '{{full_name}}' => $personalInfo['full_name'] ?? '',
            '{{email}}' => $personalInfo['email'] ?? '',
            '{{phone}}' => $personalInfo['phone'] ?? '',
            '{{location}}' => $personalInfo['location'] ?? '',
            '{{linkedin}}' => $personalInfo['linkedin'] ?? '',
            '{{website}}' => $personalInfo['website'] ?? '',
            '{{summary}}' => $data['summary'] ?? '',
        ];

        $result = str_replace(array_keys($replacements), array_values($replacements), $html);

        // Handle experience section
        if (isset($data['experience']) && is_array($data['experience'])) {
            $experienceHtml = '';
            foreach ($data['experience'] as $exp) {
                $experienceHtml .= '<div class="experience-item">';
                $experienceHtml .= '<h4>'.($exp['title'] ?? '').'</h4>';
                $experienceHtml .= '<p class="company">'.($exp['company'] ?? '').' | '.($exp['location'] ?? '').'</p>';
                $experienceHtml .= '<p class="dates">'.($exp['start_date'] ?? '').' - '.($exp['end_date'] ?? '').'</p>';
                $experienceHtml .= '<p class="description">'.($exp['description'] ?? '').'</p>';
                $experienceHtml .= '</div>';
            }
            $result = str_replace('{{experience}}', $experienceHtml, $result);
        }

        // Handle education section
        if (isset($data['education']) && is_array($data['education'])) {
            $educationHtml = '';
            foreach ($data['education'] as $edu) {
                $educationHtml .= '<div class="education-item">';
                $educationHtml .= '<h4>'.($edu['degree'] ?? '').'</h4>';
                $educationHtml .= '<p class="institution">'.($edu['institution'] ?? '').'</p>';
                $educationHtml .= '<p class="graduation">'.($edu['graduation_date'] ?? '').'</p>';
                $educationHtml .= '</div>';
            }
            $result = str_replace('{{education}}', $educationHtml, $result);
        }

        // Handle skills section
        if (isset($data['skills']) && is_array($data['skills'])) {
            $skillsHtml = '<ul class="skills-list">';
            foreach ($data['skills'] as $skill) {
                $skillsHtml .= '<li>'.(is_string($skill) ? $skill : ($skill['name'] ?? '')).'</li>';
            }
            $skillsHtml .= '</ul>';
            $result = str_replace('{{skills}}', $skillsHtml, $result);
        }

        // Wrap with CSS
        if ($css) {
            $result = '<style>'.$css.'</style>'.$result;
        }

        return $result;
    }

    /**
     * Generate default HTML from template styles and sections.
     */
    private function generateDefaultHtml(CvTemplate $template, array $cvData): string
    {
        $styles = $template->styles ?? CvTemplate::defaultStyles();
        $sections = $template->sections ?? CvTemplate::defaultSections();

        $css = $this->generateCss($styles);
        $html = '<div class="cv-container">';

        foreach ($sections as $section) {
            if (! ($section['enabled'] ?? true)) {
                continue;
            }

            $html .= match ($section['id']) {
                'header' => $this->renderHeaderSection($cvData),
                'summary' => $this->renderSummarySection($cvData),
                'experience' => $this->renderExperienceSection($cvData),
                'education' => $this->renderEducationSection($cvData),
                'skills' => $this->renderSkillsSection($cvData),
                'projects' => $this->renderProjectsSection($cvData),
                'certifications' => $this->renderCertificationsSection($cvData),
                'languages' => $this->renderLanguagesSection($cvData),
                default => '',
            };
        }

        $html .= '</div>';

        return '<style>'.$css.'</style>'.$html;
    }

    /**
     * Generate CSS from styles array.
     */
    private function generateCss(array $styles): string
    {
        return "
            .cv-container {
                font-family: {$styles['fontFamily']};
                font-size: {$styles['fontSize']};
                line-height: {$styles['lineHeight']};
                color: {$styles['textColor']};
                background-color: {$styles['backgroundColor']};
                max-width: 800px;
                margin: 0 auto;
                padding: 40px;
            }
            h1, h2, h3, h4 {
                font-family: {$styles['headingFont']};
                color: {$styles['headingColor']};
            }
            .section { margin-bottom: 24px; }
            .section-title {
                color: {$styles['primaryColor']};
                border-bottom: 2px solid {$styles['primaryColor']};
                padding-bottom: 8px;
                margin-bottom: 16px;
            }
            .experience-item, .education-item, .project-item { margin-bottom: 16px; }
            .company, .institution { color: {$styles['secondaryColor']}; }
            .dates { font-size: 0.9em; color: {$styles['secondaryColor']}; }
            .skills-list { display: flex; flex-wrap: wrap; gap: 8px; list-style: none; padding: 0; }
            .skills-list li {
                background: {$styles['accentColor']}20;
                color: {$styles['accentColor']};
                padding: 4px 12px;
                border-radius: {$styles['borderRadius']};
            }
        ";
    }

    private function renderHeaderSection(array $data): string
    {
        $info = $data['personal_info'] ?? [];

        return '
            <div class="section header">
                <h1>'.($info['full_name'] ?? 'Your Name').'</h1>
                <p>'.implode(' | ', array_filter([
            $info['email'] ?? '',
            $info['phone'] ?? '',
            $info['location'] ?? '',
        ])).'</p>
            </div>
        ';
    }

    private function renderSummarySection(array $data): string
    {
        $summary = $data['summary'] ?? '';
        if (empty($summary)) {
            return '';
        }

        return '
            <div class="section summary">
                <h2 class="section-title">Professional Summary</h2>
                <p>'.$summary.'</p>
            </div>
        ';
    }

    private function renderExperienceSection(array $data): string
    {
        $experience = $data['experience'] ?? [];
        if (empty($experience)) {
            return '';
        }

        $html = '<div class="section experience"><h2 class="section-title">Work Experience</h2>';
        foreach ($experience as $exp) {
            $html .= '
                <div class="experience-item">
                    <h4>'.($exp['title'] ?? '').'</h4>
                    <p class="company">'.($exp['company'] ?? '').' | '.($exp['location'] ?? '').'</p>
                    <p class="dates">'.($exp['start_date'] ?? '').' - '.($exp['end_date'] ?? '').'</p>
                    <p>'.($exp['description'] ?? '').'</p>
                </div>
            ';
        }

        return $html.'</div>';
    }

    private function renderEducationSection(array $data): string
    {
        $education = $data['education'] ?? [];
        if (empty($education)) {
            return '';
        }

        $html = '<div class="section education"><h2 class="section-title">Education</h2>';
        foreach ($education as $edu) {
            $html .= '
                <div class="education-item">
                    <h4>'.($edu['degree'] ?? '').'</h4>
                    <p class="institution">'.($edu['institution'] ?? '').'</p>
                    <p class="dates">'.($edu['graduation_date'] ?? '').'</p>
                </div>
            ';
        }

        return $html.'</div>';
    }

    private function renderSkillsSection(array $data): string
    {
        $skills = $data['skills'] ?? [];
        if (empty($skills)) {
            return '';
        }

        $html = '<div class="section skills"><h2 class="section-title">Skills</h2><ul class="skills-list">';
        foreach ($skills as $skill) {
            $html .= '<li>'.(is_string($skill) ? $skill : ($skill['name'] ?? '')).'</li>';
        }

        return $html.'</ul></div>';
    }

    private function renderProjectsSection(array $data): string
    {
        $projects = $data['projects'] ?? [];
        if (empty($projects)) {
            return '';
        }

        $html = '<div class="section projects"><h2 class="section-title">Projects</h2>';
        foreach ($projects as $project) {
            $html .= '
                <div class="project-item">
                    <h4>'.($project['name'] ?? '').'</h4>
                    <p>'.($project['description'] ?? '').'</p>
                </div>
            ';
        }

        return $html.'</div>';
    }

    private function renderCertificationsSection(array $data): string
    {
        $certs = $data['certifications'] ?? [];
        if (empty($certs)) {
            return '';
        }

        $html = '<div class="section certifications"><h2 class="section-title">Certifications</h2><ul>';
        foreach ($certs as $cert) {
            $html .= '<li>'.(is_string($cert) ? $cert : ($cert['name'] ?? '')).'</li>';
        }

        return $html.'</ul></div>';
    }

    private function renderLanguagesSection(array $data): string
    {
        $languages = $data['languages'] ?? [];
        if (empty($languages)) {
            return '';
        }

        $html = '<div class="section languages"><h2 class="section-title">Languages</h2><ul>';
        foreach ($languages as $lang) {
            $name = is_string($lang) ? $lang : ($lang['name'] ?? '');
            $level = is_array($lang) ? ($lang['level'] ?? '') : '';
            $html .= '<li>'.$name.($level ? " - {$level}" : '').'</li>';
        }

        return $html.'</ul></div>';
    }

    private function getEmptyCvData(): array
    {
        return [
            'personal_info' => [
                'full_name' => '',
                'email' => '',
                'phone' => '',
                'location' => '',
            ],
            'summary' => '',
            'experience' => [],
            'education' => [],
            'skills' => [],
            'projects' => [],
            'certifications' => [],
            'languages' => [],
        ];
    }

    private function getSampleCvData(): array
    {
        return [
            'personal_info' => [
                'full_name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1 (555) 123-4567',
                'location' => 'San Francisco, CA',
                'linkedin' => 'linkedin.com/in/johndoe',
                'website' => 'johndoe.com',
            ],
            'summary' => 'Experienced software engineer with 5+ years of expertise in building scalable web applications. Passionate about clean code, user experience, and continuous learning.',
            'experience' => [
                [
                    'title' => 'Senior Software Engineer',
                    'company' => 'Tech Corp Inc.',
                    'location' => 'San Francisco, CA',
                    'start_date' => '2021-01',
                    'end_date' => 'Present',
                    'description' => 'Led development of microservices architecture. Mentored junior developers and implemented CI/CD pipelines.',
                ],
                [
                    'title' => 'Software Engineer',
                    'company' => 'StartUp Labs',
                    'location' => 'New York, NY',
                    'start_date' => '2018-06',
                    'end_date' => '2020-12',
                    'description' => 'Developed and maintained RESTful APIs. Collaborated with cross-functional teams to deliver features.',
                ],
            ],
            'education' => [
                [
                    'degree' => 'Bachelor of Science in Computer Science',
                    'institution' => 'Stanford University',
                    'location' => 'Stanford, CA',
                    'graduation_date' => '2018',
                    'gpa' => '3.8',
                ],
            ],
            'skills' => ['JavaScript', 'TypeScript', 'React', 'Node.js', 'Python', 'AWS', 'Docker', 'PostgreSQL'],
            'projects' => [],
            'certifications' => [
                ['name' => 'AWS Solutions Architect', 'issuer' => 'Amazon Web Services', 'date' => '2022'],
            ],
            'languages' => [
                ['language' => 'English', 'proficiency' => 'Native'],
                ['language' => 'Spanish', 'proficiency' => 'Intermediate'],
            ],
        ];
    }

    private function userHasPremiumAccess(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        return Auth::user()->hasPremiumAccess();
    }
}
