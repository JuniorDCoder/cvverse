<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiService
{
    private string $apiKey;

    private string $model;

    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key');
        $this->model = config('gemini.model');
        $this->baseUrl = config('gemini.base_url');
    }

    /**
     * Generate content using Gemini AI
     *
     * @param  array<int, array<string, mixed>>  $contents
     * @return array<string, mixed>|null
     *
     * @throws ConnectionException
     */
    public function generateContent(string $prompt, array $contents = [], ?string $filePath = null): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('Gemini API key not configured');

            return null;
        }

        $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => empty($contents) ? [
                [
                    'parts' => array_merge(
                        [['text' => $prompt]],
                        $filePath && (Storage::disk('local')->exists($filePath) || file_exists($filePath)) ? [[
                            'inline_data' => [
                                'mime_type' => file_exists($filePath) ? mime_content_type($filePath) : Storage::disk('local')->mimeType($filePath),
                                'data' => base64_encode(file_exists($filePath) ? file_get_contents($filePath) : Storage::disk('local')->get($filePath)),
                            ],
                        ]] : []
                    ),
                ],
            ] : $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 8192,
            ],
        ];

        try {
            $response = Http::timeout(60)->post($url, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Gemini API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Gemini API exception', ['message' => $e->getMessage()]);

            return null;
        }
    }

    public function extractText(?array $response): ?string
    {
        if (! $response) {
            return null;
        }

        return $response['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }

    /**
     * Robustly parse JSON from AI response
     */
    private function parseJsonResponse(?string $text): ?array
    {
        if (! $text) {
            return null;
        }

        // Try to find JSON block in markdown
        if (preg_match('/```json\s*(\{.*\}|\[.*\])\s*```/s', $text, $matches)) {
            $text = $matches[1];
        } elseif (preg_match('/(\{.*\}|\[.*\])/s', $text, $matches)) {
            // Try to find anything that looks like a JSON object or array
            $text = $matches[1];
        }

        $text = trim($text);

        try {
            return json_decode($text, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::error('Failed to parse Gemini JSON response', [
                'error' => $e->getMessage(),
                'text' => $text
            ]);

            return null;
        }
    }

    /**
     * Analyze a job posting and extract structured data
     *
     * @return array<string, mixed>|null
     */
    public function analyzeJobPosting(string $content): ?array
    {
        $prompt = <<<PROMPT
Analyze the following job posting and extract structured information. Return a valid JSON object with these fields:
- title: The job title
- company: Company name if mentioned
- location: Job location
- work_type: remote/hybrid/onsite
- experience_level: entry/mid/senior/lead
- salary_range: Salary if mentioned
- requirements: Array of job requirements
- skills: Array of required skills
- description: A clean summary of the job description

Job Posting:
{$content}

Return ONLY valid JSON, no markdown or explanation.
PROMPT;

        $response = $this->generateContent($prompt);
        $text = $this->extractText($response);

        return $this->parseJsonResponse($text);
    }

    /**
     * Research a company and return insights
     *
     * @return array<string, mixed>|null
     */
    public function researchCompany(string $companyName, ?string $website = null): ?array
    {
        $websiteInfo = $website ? "Website: {$website}" : '';

        $prompt = <<<PROMPT
Research and provide information about this company for a job seeker. Return a valid JSON object with:
- description: Brief company description
- industry: Company industry
- size: Approximate company size (startup/small/medium/large/enterprise)
- culture: Brief description of company culture if known
- interview_tips: Array of interview tips for this company
- notable_facts: Array of notable facts about the company
- glassdoor_rating: Estimated rating if known (or null)

Company: {$companyName}
{$websiteInfo}

Return ONLY valid JSON, no markdown or explanation.
PROMPT;

        $response = $this->generateContent($prompt);
        $text = $this->extractText($response);

        return $this->parseJsonResponse($text);
    }

    /**
     * Generate CV suggestions based on job requirements
     *
     * @param  array<string, mixed>  $cvData
     * @param  array<string, mixed>  $jobData
     * @return array<string, mixed>|null
     */
    public function generateCvSuggestions(array $cvData, array $jobData, ?string $filePath = null): ?array
    {
        $cvJson = json_encode($cvData);

        $hasJob = ! empty($jobData) && ! empty(array_filter($jobData));
        $jobJson = $hasJob ? json_encode($jobData) : 'No specific job provided. Provide general best-practice improvement suggestions.';

        $context = $hasJob
            ? 'Analyze this CV against the job requirements and provide specific, actionable suggestions to improve the match.'
            : 'Analyze this CV and provide specific, actionable suggestions to improve its quality, impact, and professional appeal.';

        $prompt = <<<PROMPT
You are a professional CV/resume consultant. {$context}

Current CV:
{$cvJson}

Target Job:
{$jobJson}

Return a valid JSON object with:
- match_score: Number 0-100 indicating how well the CV matches the job (or general quality score if no job)
- summary_suggestions: Suggestions for improving the professional summary
- experience_suggestions: Array of suggestions for work experience section
- skills_to_add: Array of skills that should be added/highlighted
- skills_to_emphasize: Array of existing skills that should be emphasized
- keywords_missing: Array of important keywords missing in CV
- overall_recommendations: Array of top 5 overall recommendations

Return ONLY valid JSON, no markdown or explanation.
PROMPT;

        $response = $this->generateContent($prompt, [], $filePath);
        $text = $this->extractText($response);

        return $this->parseJsonResponse($text);
    }

    /**
     * Generate a cover letter
     *
     * @param  array<string, mixed>  $cvData
     * @param  array<string, mixed>  $jobData
     * @param  array<string, mixed>|null  $companyData
     */
    public function generateCoverLetter(
        array $cvData,
        array $jobData,
        ?array $companyData = null,
        string $tone = 'professional'
    ): ?string {
        $cvJson = json_encode($cvData);
        $jobJson = json_encode($jobData);
        $companyJson = $companyData ? json_encode($companyData) : 'No additional company info available';

        $prompt = <<<PROMPT
You are an expert cover letter writer. Create a compelling, personalized cover letter for this job application.

Candidate's CV/Background:
{$cvJson}

Target Job:
{$jobJson}

Company Information:
{$companyJson}

Tone: {$tone}

Guidelines:
- Make it personal and specific to this job
- Highlight relevant experience and skills
- Show enthusiasm for the company and role
- Keep it concise (3-4 paragraphs)
- Include specific achievements with metrics where possible
- End with a strong call to action

Write the cover letter directly, no explanations or markdown formatting. Start with the greeting.
PROMPT;

        $response = $this->generateContent($prompt);

        return $this->extractText($response);
    }

    /**
     * Improve existing cover letter content
     *
     * @return array<string, mixed>|null
     */
    public function improveCoverLetter(string $content, string $feedback = ''): ?array
    {
        $feedbackSection = $feedback ? "User Feedback: {$feedback}" : '';

        $prompt = <<<PROMPT
You are an expert cover letter editor. Review and improve this cover letter.

Current Cover Letter:
{$content}

{$feedbackSection}

Return a valid JSON object with:
- improved_content: The improved cover letter text
- changes_made: Array of changes made and why
- strength_score: Number 0-100 rating the cover letter strength
- suggestions: Array of additional suggestions for further improvement

Return ONLY valid JSON, no markdown or explanation.
PROMPT;

        $response = $this->generateContent($prompt);
        $text = $this->extractText($response);

        return $this->parseJsonResponse($text);
    }

    /**
     * Generate or improve a professional summary
     *
     * @param  array<string, mixed>  $cvData
     * @param  array<string, mixed>|null  $jobData
     */
    public function generateSummary(array $cvData, ?array $jobData = null, ?string $filePath = null): ?string
    {
        $cvJson = json_encode($cvData);
        $jobContext = $jobData ? 'Target Job: '.json_encode($jobData) : 'Create a general professional summary.';

        $prompt = <<<PROMPT
You are an expert CV writer. Create a compelling professional summary based on this background.

Candidate Background:
{$cvJson}

{$jobContext}

Guidelines:
- 3-4 sentences maximum
- Highlight key achievements and skills
- Be specific with years of experience and expertise areas
- If targeting a job, align the summary with the role
- Use powerful action words

Write the summary directly, no explanations or formatting.
PROMPT;

        $response = $this->generateContent($prompt, [], $filePath);

        return $this->extractText($response);
    }

    /**
     * Rewrite a CV section with AI
     *
     * @param  array<string, mixed>|null  $jobContext
     * @return array<string, mixed>|null
     */
    public function rewriteSection(string $section, string $content, ?array $jobContext = null): ?array
    {
        $jobInfo = $jobContext ? 'Target Job: '.json_encode($jobContext) : '';

        $prompt = <<<PROMPT
You are an expert CV writer. Improve this {$section} section of a CV.

Current Content:
{$content}

{$jobInfo}

Return a valid JSON object with:
- improved_content: The improved section content
- improvements_made: Array of specific improvements made

Return ONLY valid JSON, no markdown or explanation.
PROMPT;

        $response = $this->generateContent($prompt);
        $text = $this->extractText($response);

        return $this->parseJsonResponse($text);
    }

    /**
     * Generate a full CV based on user profile and job description
     *
     * @param  array<string, mixed>  $userData
     * @param  array<string, mixed>  $jobData
     * @return array<string, mixed>|null
     */
    public function generateCvFromJob(array $userData, array $jobData): ?array
    {
        $userJson = json_encode($userData);
        $jobJson = json_encode($jobData);

        $prompt = <<<PROMPT
You are a professional CV writer. Create a complete, tailored CV for a candidate applying to a specific job.
Use the candidate's background information and the job requirements to generate a targeted resume.

Candidate Profile:
{$userJson}

Target Job:
{$jobJson}

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
    "summary": "Professional summary...",
    "experience": [
        {
            "company": "...",
            "title": "...",
            "location": "...",
            "start_date": "YYYY-MM",
            "end_date": "YYYY-MM", // or null for current
            "current": boolean,
            "description": "..."
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
    "languages": [
        {
            "language": "...",
            "proficiency": "Native/Fluent/Advanced/Intermediate/Beginner"
        }
    ]
}

Guidelines:
- Tailor the summary and experience descriptions to match the job requirements.
- Highlight relevant skills.
- If the candidate profile is brief, infer reasonable and professional details appropriate for their experience level, but do not hallucinate specific companies or degrees if not provided (use placeholders or keep generic if unknown, but prefer using provided data).
- Ensure the tone is professional.

Return ONLY valid JSON, no markdown or explanation.
PROMPT;

        $response = $this->generateContent($prompt);
        $text = $this->extractText($response);

        return $this->parseJsonResponse($text);
    }

    /**
     * Chat with AI to edit CV
     *
     * @return array<string, mixed>|null
     */
    public function chatWithCv(array $cvData, string $message, array $history = []): ?array
    {
        $cvJson = json_encode($cvData);
        $historyJson = json_encode($history);

        $prompt = <<<PROMPT
You are a professional CV assistant. The user wants to edit their CV or ask questions about it.
You have the ability to MODIFY the CV content based on the user's request.

Current CV:
{$cvJson}

Conversation History:
{$historyJson}

User Message:
{$message}

Return a valid JSON object with:
- message: Your response to the user (e.g. "I updated your summary.")
- updated_cv: The updated CV object (if you made changes, otherwise null). MUST match the structure of the Current CV exactly.
- changes_summary: A brief summary of changes made (if any)

Guidelines:
- If the user asks to change something (e.g., "change the summary", "add this skill"), UPDATE the cv content in the `updated_cv` field.
- If the user just asks a question or for advice, keep `updated_cv` null.
- Be helpful, professional, and concise.

Return ONLY valid JSON, no markdown or explanation.
PROMPT;

        $response = $this->generateContent($prompt);
        $text = $this->extractText($response);

        return $this->parseJsonResponse($text);
    }

    /**
     * General Chat with AI
     *
     * @return array<string, mixed>|null
     */
    public function chatWithAi(string $message, array $history = [], ?string $filePath = null): ?array
    {
        $historyJson = json_encode($history);

        $prompt = <<<PROMPT
You are a helpful and professional career assistant. You help users with their job search, CV writing, and career advice.

Conversation History:
{$historyJson}

User Message:
{$message}

Return a valid JSON object with:
- message: Your response to the user.

Guidelines:
- Be helpful, professional, and concise.
- Provide actionable advice.
- If the user provides an image or file, analyze it and provide feedback.

Return ONLY valid JSON, no markdown or explanation.
PROMPT;

        $response = $this->generateContent($prompt, [], $filePath);
        $text = $this->extractText($response);

        return $this->parseJsonResponse($text);
    }

    /**
     * Chat with AI in the context of a specific resource (CV, Job, Cover Letter)
     *
     * @return array<string, mixed>|null
     */
    public function chatWithResource(string $resourceType, array $resourceData, string $message, array $history = []): ?array
    {
        $resourceJson = json_encode($resourceData);
        $historyJson = json_encode($history);

        $prompt = <<<PROMPT
You are a professional assistant specialized in {$resourceType} management.
The user is currently viewing/editing this {$resourceType} and wants help or changes.

Current {$resourceType} Data:
{$resourceJson}

Conversation History:
{$historyJson}

User Message:
{$message}

Return a valid JSON object with:
- message: Your response to the user.
- updated_data: An object containing ONLY the fields that should be updated in the {$resourceType} (or null if no changes). 
- changes_summary: A brief description of changes made.

Guidelines:
- If the user asks to modify the {$resourceType} (e.g. "change the title", "add this skill", "rewrite the content"), provide the new values in `updated_data`.
- Keep the structure of `updated_data` consistent with the "Current {$resourceType} Data".
- Be professional, concise, and helpful.
- If it's a CV, help improve structure and wording.
- If it's a Job Application, help with matching their profile.
- If it's a Cover Letter, help with tone and impact.

Return ONLY valid JSON, no markdown or explanation.
PROMPT;

        $response = $this->generateContent($prompt);
        $text = $this->extractText($response);

        return $this->parseJsonResponse($text);
    }
}
