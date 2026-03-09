<?php

namespace App\Services;

use App\Models\HelpConversation;
use App\Models\HelpMessage;
use App\Models\SiteSetting;

class HelpChatService
{
    public function __construct(
        private readonly GeminiService $geminiService
    ) {}

    /**
     * Get or create the active help conversation for a user.
     */
    public function getOrCreateConversation(int $userId, ?string $subject = null): HelpConversation
    {
        $conversation = HelpConversation::where('user_id', $userId)
            ->where('status', '!=', 'closed')
            ->latest()
            ->first();

        if (! $conversation) {
            $timeout = (int) SiteSetting::getValue('help_chat_ai_timeout', 5);

            $conversation = HelpConversation::create([
                'user_id' => $userId,
                'subject' => $subject ?? 'Help Request',
                'status' => 'open',
                'ai_timeout_minutes' => $timeout,
            ]);

            // Welcome message
            $conversation->messages()->create([
                'sender_type' => 'ai',
                'sender_id' => null,
                'content' => "Hello! Welcome to our Help Center. An admin will be with you shortly. In the meantime, I'm an AI assistant and can help answer questions about CVVerse — like how to generate a CV with AI, manage subscriptions, track job applications, and more. How can I help you today?",
            ]);
        }

        return $conversation;
    }

    /**
     * Send a user message, checking if AI should respond.
     */
    public function sendUserMessage(HelpConversation $conversation, string $content, ?string $mediaPath = null, ?string $mediaMimeType = null): HelpMessage
    {
        $userMessage = $conversation->messages()->create([
            'sender_type' => 'user',
            'sender_id' => $conversation->user_id,
            'content' => $content,
            'media_path' => $mediaPath,
            'media_mime_type' => $mediaMimeType,
        ]);

        $conversation->touch();

        // Check if AI should respond
        if ($this->shouldAiRespond($conversation)) {
            $this->generateAiResponse($conversation);
        }

        return $userMessage;
    }

    /**
     * Send an admin message and mark admin as active.
     */
    public function sendAdminMessage(HelpConversation $conversation, int $adminId, string $content, ?string $mediaPath = null, ?string $mediaMimeType = null): HelpMessage
    {
        $adminMessage = $conversation->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => $adminId,
            'content' => $content,
            'media_path' => $mediaPath,
            'media_mime_type' => $mediaMimeType,
        ]);

        // Admin is active — suspend AI
        $conversation->update([
            'admin_id' => $adminId,
            'status' => 'open',
            'last_admin_activity_at' => now(),
        ]);

        return $adminMessage;
    }

    /**
     * Determine if AI should auto-respond.
     */
    public function shouldAiRespond(HelpConversation $conversation): bool
    {
        // Already AI-active
        if ($conversation->status === 'ai_active') {
            return true;
        }

        // No admin has ever joined
        if (! $conversation->admin_id && ! $conversation->last_admin_activity_at) {
            // Check if timed out since conversation was created
            if ($conversation->created_at->diffInMinutes(now()) >= $conversation->ai_timeout_minutes) {
                $conversation->update(['status' => 'ai_active']);

                return true;
            }

            return false;
        }

        // Admin was active but has timed out
        if ($conversation->isAiTimedOut()) {
            $conversation->update(['status' => 'ai_active']);

            return true;
        }

        return false;
    }

    /**
     * Generate an AI response using Gemini with app context.
     */
    public function generateAiResponse(HelpConversation $conversation): ?HelpMessage
    {
        $appContext = $this->getAppContext();

        $lastUserMessage = $conversation->messages()
            ->where('sender_type', 'user')
            ->latest()
            ->first();

        $systemPrompt = <<<PROMPT
You are a friendly and knowledgeable support assistant for CVVerse, a career management platform.
You are filling in for a human support agent who is currently unavailable.

{$appContext}

IMPORTANT GUIDELINES:
- Only answer questions related to CVVerse and its features.
- If the user asks something you're unsure about, say "I'll flag this for our support team to follow up on."
- Be warm, helpful, and concise.
- Do NOT make up features or pricing that aren't described above.
- If the user needs account-specific help (billing issues, bugs, etc.), reassure them that a human agent will follow up.
- Respond directly with your answer as plain text. Do NOT wrap your response in JSON.
PROMPT;

        // Build Gemini contents with system instruction + conversation history
        $contents = [];

        // Add system context as the first model turn
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $systemPrompt]],
        ];
        $contents[] = [
            'role' => 'model',
            'parts' => [['text' => 'Understood! I\'m ready to help users with CVVerse questions as a friendly support assistant. I\'ll stay within the platform\'s features and escalate anything I\'m unsure about to the human support team.']],
        ];

        // Add conversation history
        $history = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($history as $msg) {
            $role = $msg->sender_type === 'user' ? 'user' : 'model';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $msg->content]],
            ];
        }

        $response = $this->geminiService->generateContent(
            $lastUserMessage?->content ?? 'Hello',
            $contents
        );

        $text = null;
        if ($response && isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            $text = $response['candidates'][0]['content']['parts'][0]['text'];
        }

        if (! $text) {
            return $conversation->messages()->create([
                'sender_type' => 'ai',
                'sender_id' => null,
                'content' => "I apologize, but I'm having trouble processing your request right now. A human support agent will follow up with you shortly.",
            ]);
        }

        return $conversation->messages()->create([
            'sender_type' => 'ai',
            'sender_id' => null,
            'content' => trim($text),
        ]);
    }

    /**
     * Mark messages as read.
     */
    public function markMessagesAsRead(HelpConversation $conversation, string $readerType): void
    {
        $senderTypes = $readerType === 'user' ? ['admin', 'ai'] : ['user'];

        $conversation->messages()
            ->whereIn('sender_type', $senderTypes)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get app context for AI to understand CVVerse features.
     */
    private function getAppContext(): string
    {
        return <<<'CONTEXT'
ABOUT CVVERSE:
CVVerse is a comprehensive career management platform that helps users create professional CVs, track job applications, and advance their careers.

KEY FEATURES:
1. **AI CV Generator** - Users can describe themselves and AI creates a professional CV. Available at "Generate CV with AI" in the sidebar.
2. **Manual CV Creation** - Users can create CVs manually with a form-based editor at "My CVs > Create CV".
3. **CV Upload** - Users can upload existing CVs (PDF/DOCX) which are parsed and imported.
4. **CV Templates** - Multiple professional templates: Modern, Classic, Minimal, Creative, Executive.
5. **CV Export** - Download CVs as PDF from the CV preview page.
6. **CV Sharing** - Share CVs via a public link with optional password protection.
7. **Job Application Tracking** - Track job applications with statuses: Saved, Applied, Interviewing, Offered, Rejected, Withdrawn.
8. **AI Job Analysis** - AI analyzes job postings and provides insights.
9. **Cover Letters** - AI-generated cover letters tailored to specific jobs and CVs.
10. **AI Chat Assistant** - General career advice chatbot available in the sidebar.
11. **Subscription Plans** - Free and premium plans with different usage limits. Manage at "Subscription" in the sidebar.

HOW-TO GUIDES:
- To generate a CV with AI: Go to sidebar > "Generate CV with AI" > Describe yourself > Click Generate.
- To create a CV manually: Sidebar > "My CVs" > "Create CV" > "Create Manually" tab.
- To upload a CV: Sidebar > "My CVs" > "Create CV" > "Upload" tab > Select PDF or DOCX.
- To track a job: Sidebar > "Job Applications" > "Track New Job" > Fill in details.
- To generate a cover letter: Sidebar > "Cover Letters" > "Create" > Select a CV and job > Generate.
- To subscribe/upgrade: Sidebar > "Subscription" or visit the Pricing page.
- To export a CV as PDF: Open a CV > Click "Download PDF" or "Export".
- To share a CV: Open a CV > Click "Share" > Copy the public link.
- To use AI Chat: Sidebar > "AI Chat" for general career advice, or use the floating chat button.

SUBSCRIPTION & PRICING:
- Users can view plans at /pricing or through the Subscription page.
- Free plans have limited usage (CV generations, AI messages, etc.).
- Premium plans unlock higher limits and premium features like advanced templates and AI capabilities.
- Users can upgrade anytime from the Subscription page.

ACCOUNT MANAGEMENT:
- Profile settings are available via the user menu (top-right avatar).
- Users can update their name, email, and password in settings.
- Dark mode is available via the appearance toggle.
CONTEXT;
    }
}
