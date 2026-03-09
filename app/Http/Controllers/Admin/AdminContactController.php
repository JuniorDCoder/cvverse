<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ContactReply;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class AdminContactController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ContactMessage::query()
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->latest();

        $stats = [
            'total' => ContactMessage::count(),
            'new' => ContactMessage::where('status', 'new')->count(),
            'read' => ContactMessage::where('status', 'read')->count(),
            'replied' => ContactMessage::where('status', 'replied')->count(),
        ];

        return Inertia::render('admin/ContactMessages/Index', [
            'messages' => $query->paginate(20)->withQueryString(),
            'stats' => $stats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function markAsRead(ContactMessage $contactMessage): RedirectResponse
    {
        if ($contactMessage->status === 'new') {
            $contactMessage->update(['status' => 'read']);
        }

        return back();
    }

    public function reply(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $validated = $request->validate([
            'reply' => ['required', 'string', 'max:10000'],
        ]);

        $contactMessage->update([
            'admin_reply' => $validated['reply'],
            'status' => 'replied',
            'replied_by' => $request->user()->id,
            'replied_at' => now(),
        ]);

        Mail::to($contactMessage->email)->send(new ContactReply($contactMessage));

        return back()->with('success', 'Reply sent successfully.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        return redirect()->route('admin.contact-messages.index')
            ->with('success', 'Message deleted successfully.');
    }
}
