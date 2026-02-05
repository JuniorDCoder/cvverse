<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\CoverLetter;
use App\Models\Cv;
use App\Models\JobApplication;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiChatController extends Controller
{
    public function __construct(
        private readonly GeminiService $geminiService
    ) {}

    /**
     * General AI chat endpoint for /ai-chat page (no session, just message and history)
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array',
        ]);

        $result = $this->geminiService->chatWithAi(
            $request->input('message'),
            $request->input('history', []),
            null
        );

        if (! $result || empty($result['message'])) {
            return response()->json([
                'success' => false,
                'message' => 'AI failed to respond.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }

    public function index(): JsonResponse
    {
        $sessions = Auth::user()->chatSessions()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'sessions' => $sessions,
        ]);
    }

    public function show(ChatSession $session): JsonResponse
    {
        $this->authorize('view', $session);

        $messages = $session->messages()->orderBy('created_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string',
                'session_id' => 'nullable|exists:chat_sessions,id',
                'context_type' => 'nullable|string|in:general,cv,job,cover_letter',
                'context_id' => 'nullable|integer',
                'media' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            ]);

            $user = Auth::user();

            // Find or create session
            if (! empty($validated['session_id'])) {
                $session = ChatSession::findOrFail($validated['session_id']);
                $this->authorize('update', $session);
            } else {
                $session = $user->chatSessions()->create([
                    'title' => substr($validated['message'], 0, 50).'...',
                    'context_type' => $validated['context_type'] ?? 'general',
                    'context_id' => $validated['context_id'] ?? null,
                ]);
            }

            // Handle Media
            $mediaPath = null;
            $mediaMimeType = null;
            if ($request->hasFile('media')) {
                $mediaPath = $request->file('media')->store('chat-media', 'public');
                $mediaMimeType = $request->file('media')->getMimeType();
            }

            // Save user message
            $userMessage = $session->messages()->create([
                'role' => 'user',
                'content' => $validated['message'],
                'media_path' => $mediaPath,
                'media_mime_type' => $mediaMimeType,
            ]);

            // Get History for Gemini
            $history = $session->messages()
                ->where('id', '!=', $userMessage->id)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn ($m) => [
                    'role' => $m->role,
                    'parts' => [['text' => $m->content]],
                ])->toArray();

            // Prepare Chat for Gemini
            $aiResult = null;
            $contextModel = null;
            $contextType = $session->context_type;
            $contextId = $session->context_id;

            if ($contextType !== 'general' && $contextId) {
                $contextModel = match ($contextType) {
                    'cv' => Cv::find($contextId),
                    'job' => JobApplication::find($contextId),
                    'cover_letter' => CoverLetter::find($contextId),
                    default => null,
                };

                if ($contextModel) {
                    $aiResult = $this->geminiService->chatWithResource($contextType, $contextModel->toArray(), $validated['message'], $history);
                }
            }

            if (! $aiResult) {
                $aiResult = $this->geminiService->chatWithAi($validated['message'], $history, $mediaPath ? storage_path("app/public/{$mediaPath}") : null);
            }

            if (! $aiResult) {
                return response()->json(['success' => false, 'message' => 'AI failed to respond.'], 500);
            }

            // Apply Edits if suggested
            $editApplied = false;
            if (isset($aiResult['updated_data']) && $aiResult['updated_data'] && $contextModel) {
                $contextModel->update($aiResult['updated_data']);
                $editApplied = true;
            }

            // Save AI message
            $assistantMessage = $session->messages()->create([
                'role' => 'assistant',
                'content' => $aiResult['message'],
            ]);

            return response()->json([
                'success' => true,
                'message' => $assistantMessage->content,
                'session' => $session,
                'user_message' => $userMessage,
                'assistant_message' => $assistantMessage,
                'edit_applied' => $editApplied,
                'changes_summary' => $aiResult['changes_summary'] ?? null,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI Chat Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Server error: '.$e->getMessage()], 500);
        }
    }

    /**
     * Return persistent chat history for the current user for the AI chat page.
     */
    public function history(Request $request): JsonResponse
    {
        $user = Auth::user();
        $messages = [];
        $session = $user->chatSessions()
            ->where('context_type', 'general')
            ->latest()
            ->first();
        if ($session) {
            $messages = $session->messages()->orderBy('created_at', 'asc')->get()->map(function ($msg) {
                return [
                    'role' => $msg->role,
                    'content' => $msg->content,
                    'media_path' => $msg->media_path,
                ];
            })->toArray();
        }

        return response()->json([
            'success' => true,
            'history' => $messages,
        ]);
    }

    public function destroy(ChatSession $session): JsonResponse
    {
        $this->authorize('delete', $session);

        $session->delete();

        return response()->json(['success' => true]);
    }
}
