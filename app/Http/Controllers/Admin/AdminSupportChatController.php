<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HelpConversation;
use App\Models\SiteSetting;
use App\Services\HelpChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdminSupportChatController extends Controller
{
    public function __construct(
        private readonly HelpChatService $helpChatService
    ) {}

    /**
     * List all help conversations.
     */
    public function index(): Response
    {
        $conversations = HelpConversation::with(['user:id,name,email', 'latestMessage', 'admin:id,name'])
            ->withCount(['unreadMessagesForAdmin as unread_count'])
            ->latest('updated_at')
            ->paginate(20);

        $aiTimeout = (int) SiteSetting::getValue('help_chat_ai_timeout', 5);

        return Inertia::render('admin/SupportChat/Index', [
            'conversations' => $conversations,
            'aiTimeout' => $aiTimeout,
        ]);
    }

    /**
     * Show a specific conversation.
     */
    public function show(HelpConversation $conversation): Response
    {
        $conversation->load('user:id,name,email');

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

        $this->helpChatService->markMessagesAsRead($conversation, 'admin');

        return Inertia::render('admin/SupportChat/Show', [
            'conversation' => [
                'id' => $conversation->id,
                'status' => $conversation->status,
                'subject' => $conversation->subject,
                'user' => $conversation->user,
                'admin_id' => $conversation->admin_id,
                'created_at' => $conversation->created_at->toISOString(),
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Admin sends a reply.
     */
    public function reply(Request $request, HelpConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required_without:media|string|max:5000',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,doc,docx|max:10240',
        ]);

        $mediaPath = null;
        $mediaMimeType = null;
        if ($request->hasFile('media')) {
            $mediaPath = $request->file('media')->store('help-chat-media', 'public');
            $mediaMimeType = $request->file('media')->getMimeType();
        }

        // Clear typing indicator
        $conversation->update(['admin_typing_at' => null]);

        $this->helpChatService->sendAdminMessage(
            $conversation,
            Auth::id(),
            $validated['message'] ?? 'Shared a file',
            $mediaPath,
            $mediaMimeType
        );

        return response()->json(['success' => true]);
    }

    /**
     * Poll for new messages in a conversation.
     */
    public function poll(Request $request, HelpConversation $conversation): JsonResponse
    {
        $afterId = $request->input('after_id', 0);

        $messages = $conversation->messages()
            ->when($afterId, fn ($q) => $q->where('id', '>', $afterId))
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

        $this->helpChatService->markMessagesAsRead($conversation, 'admin');

        // Get read_at updates for admin's own messages that have been read by user
        $readUpdates = $conversation->messages()
            ->whereIn('sender_type', ['admin', 'ai'])
            ->whereNotNull('read_at')
            ->pluck('read_at', 'id')
            ->mapWithKeys(fn ($readAt, $id) => [$id => $readAt->toISOString()])
            ->toArray();

        // Detect if user is typing (within last 5 seconds)
        $conversation->refresh();
        $userTyping = $conversation->user_typing_at && $conversation->user_typing_at->gt(now()->subSeconds(5));

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'conversation_status' => $conversation->status,
            'read_updates' => $readUpdates,
            'user_typing' => $userTyping,
        ]);
    }

    /**
     * Close a conversation.
     */
    public function close(HelpConversation $conversation): JsonResponse
    {
        $conversation->update(['status' => 'closed']);

        $conversation->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => Auth::id(),
            'content' => 'This conversation has been closed by support. Feel free to start a new one anytime!',
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Reopen a closed conversation.
     */
    public function reopen(HelpConversation $conversation): JsonResponse
    {
        if ($conversation->status !== 'closed') {
            return response()->json(['success' => false, 'message' => 'Conversation is already open.'], 422);
        }

        $conversation->update([
            'status' => 'open',
            'admin_id' => Auth::id(),
            'last_admin_activity_at' => now(),
        ]);

        $conversation->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => Auth::id(),
            'content' => 'This conversation has been reopened by support.',
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Update typing status.
     */
    public function typing(Request $request, HelpConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'typing' => 'required|boolean',
        ]);

        $conversation->update([
            'admin_typing_at' => $validated['typing'] ? now() : null,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Update AI timeout setting.
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ai_timeout' => 'required|integer|min:1|max:60',
        ]);

        SiteSetting::setValue('help_chat_ai_timeout', $validated['ai_timeout'], 'number', 'support');

        return response()->json(['success' => true]);
    }

    /**
     * Get unread count for admin header badge.
     */
    public function unreadCount(): JsonResponse
    {
        $count = HelpConversation::where('status', '!=', 'closed')
            ->whereHas('unreadMessagesForAdmin')
            ->count();

        return response()->json(['count' => $count]);
    }
}
