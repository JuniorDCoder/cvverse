<?php

namespace App\Http\Controllers;

use App\Models\HelpConversation;
use App\Services\HelpChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class HelpChatController extends Controller
{
    public function __construct(
        private readonly HelpChatService $helpChatService
    ) {}

    /**
     * Show the Help Center page.
     */
    public function index(?int $conversationId = null): Response
    {
        $user = Auth::user();

        // Load the requested conversation or get/create an active one
        if ($conversationId) {
            $conversation = HelpConversation::where('id', $conversationId)
                ->where('user_id', $user->id)
                ->firstOrFail();
        } else {
            $conversation = $this->helpChatService->getOrCreateConversation($user->id);
        }

        $messages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($msg) => [
                'id' => $msg->id,
                'sender_type' => $msg->sender_type,
                'sender_id' => $msg->sender_id,
                'content' => $msg->content,
                'media_path' => $msg->media_path,
                'media_mime_type' => $msg->media_mime_type,
                'read_at' => $msg->read_at?->toISOString(),
                'created_at' => $msg->created_at->toISOString(),
            ]);

        $this->helpChatService->markMessagesAsRead($conversation, 'user');

        // Get all conversations for the sidebar
        $conversations = HelpConversation::where('user_id', $user->id)
            ->with('latestMessage')
            ->latest('updated_at')
            ->get()
            ->map(fn ($conv) => [
                'id' => $conv->id,
                'status' => $conv->status,
                'subject' => $conv->subject,
                'updated_at' => $conv->updated_at->toISOString(),
                'created_at' => $conv->created_at->toISOString(),
                'latest_message' => $conv->latestMessage ? [
                    'content' => $conv->latestMessage->content,
                    'sender_type' => $conv->latestMessage->sender_type,
                    'created_at' => $conv->latestMessage->created_at->toISOString(),
                ] : null,
                'unread_count' => $conv->unreadMessagesForUser()->count(),
            ]);

        return Inertia::render('HelpCenter', [
            'conversation' => [
                'id' => $conversation->id,
                'status' => $conversation->status,
                'subject' => $conversation->subject,
            ],
            'messages' => $messages,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Send a message in the help chat.
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:help_conversations,id',
            'message' => 'required_without:media|string|max:5000',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,doc,docx|max:10240',
        ]);

        $conversation = HelpConversation::where('id', $validated['conversation_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($conversation->status === 'closed') {
            return response()->json(['success' => false, 'message' => 'This conversation is closed. Please reopen it first.'], 422);
        }

        $mediaPath = null;
        $mediaMimeType = null;
        if ($request->hasFile('media')) {
            $mediaPath = $request->file('media')->store('help-chat-media', 'public');
            $mediaMimeType = $request->file('media')->getMimeType();
        }

        // Clear typing indicator
        $conversation->update(['user_typing_at' => null]);

        $this->helpChatService->sendUserMessage(
            $conversation,
            $validated['message'] ?? 'Shared a file',
            $mediaPath,
            $mediaMimeType
        );

        return response()->json(['success' => true]);
    }

    /**
     * Poll for new messages (real-time via polling).
     */
    public function poll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:help_conversations,id',
            'after_id' => 'nullable|integer',
        ]);

        $conversation = HelpConversation::where('id', $validated['conversation_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $query = $conversation->messages()->orderBy('created_at', 'asc');

        if (! empty($validated['after_id'])) {
            $query->where('id', '>', $validated['after_id']);
        }

        $messages = $query->get()->map(fn ($msg) => [
            'id' => $msg->id,
            'sender_type' => $msg->sender_type,
            'sender_id' => $msg->sender_id,
            'content' => $msg->content,
            'media_path' => $msg->media_path,
            'media_mime_type' => $msg->media_mime_type,
            'read_at' => $msg->read_at?->toISOString(),
            'created_at' => $msg->created_at->toISOString(),
        ]);

        // Mark incoming messages as read
        $this->helpChatService->markMessagesAsRead($conversation, 'user');

        // Get read_at updates for user's own messages that have been read by admin
        $readUpdates = $conversation->messages()
            ->where('sender_type', 'user')
            ->whereNotNull('read_at')
            ->pluck('read_at', 'id')
            ->mapWithKeys(fn ($readAt, $id) => [$id => $readAt->toISOString()])
            ->toArray();

        // Detect if admin/AI is typing (within last 5 seconds)
        $adminTyping = $conversation->admin_typing_at && $conversation->admin_typing_at->gt(now()->subSeconds(5));

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'conversation_status' => $conversation->fresh()->status,
            'read_updates' => $readUpdates,
            'admin_typing' => $adminTyping,
        ]);
    }

    /**
     * Close a conversation.
     */
    public function close(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:help_conversations,id',
        ]);

        $conversation = HelpConversation::where('id', $validated['conversation_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $conversation->update(['status' => 'closed']);

        return response()->json(['success' => true]);
    }

    /**
     * Reopen a closed conversation.
     */
    public function reopen(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:help_conversations,id',
        ]);

        $conversation = HelpConversation::where('id', $validated['conversation_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($conversation->status !== 'closed') {
            return response()->json(['success' => false, 'message' => 'Conversation is already open.'], 422);
        }

        $conversation->update(['status' => 'open']);

        $conversation->messages()->create([
            'sender_type' => 'ai',
            'sender_id' => null,
            'content' => 'This conversation has been reopened. How can we help you further?',
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Update typing status.
     */
    public function typing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:help_conversations,id',
            'typing' => 'required|boolean',
        ]);

        $conversation = HelpConversation::where('id', $validated['conversation_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $conversation->update([
            'user_typing_at' => $validated['typing'] ? now() : null,
        ]);

        return response()->json(['success' => true]);
    }
}
