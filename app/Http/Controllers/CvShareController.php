<?php

namespace App\Http\Controllers;

use App\Models\Cv;
use App\Models\CvShare;
use App\Models\CvComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\CommentReceived;
use App\Mail\CommentConfirmation;

class CvShareController extends Controller
{
    /**
     * Display the shared CV.
     */
    public function show(string $token): Response
    {
        $share = CvShare::where('token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        if ($share->isExpired()) {
            abort(404, 'Share link has expired.');
        }

        $cv = $share->cv()->with('comments.share', 'comments.user')->first();

        return Inertia::render('cvs/Shared', [
            'cv' => $cv,
            'share' => $share,
        ]);
    }

    /**
     * Update the CV content (for 'edit' permission).
     */
    public function update(Request $request, string $token): JsonResponse
    {
        $share = CvShare::where('token', $token)
            ->where('permission', 'edit')
            ->where('is_active', true)
            ->firstOrFail();

        if ($share->isExpired()) {
            return response()->json(['message' => 'Share link has expired.'], 403);
        }

        $cv = $share->cv;
        
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'personal_info' => 'nullable|array',
            'experience' => 'nullable|array',
            'education' => 'nullable|array',
            'skills' => 'nullable|array',
            'projects' => 'nullable|array',
            'certifications' => 'nullable|array',
            'languages' => 'nullable|array',
            'summary' => 'nullable|string',
        ]);

        $cv->update($validated);

        return response()->json([
            'success' => true,
            'cv' => $cv->fresh(),
        ]);
    }

    /**
     * Store a comment on the CV.
     */
    public function comment(Request $request, string $token): JsonResponse
    {
        $share = CvShare::where('token', $token)
            ->whereIn('permission', ['review', 'edit'])
            ->where('is_active', true)
            ->firstOrFail();

        if ($share->isExpired()) {
            return response()->json(['message' => 'Share link has expired.'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string',
            'section' => 'nullable|string',
            'guest_name' => 'nullable|string|max:255',
            'guest_email' => 'nullable|email|max:255',
        ]);

        $comment = $share->cv->comments()->create([
            'cv_share_id' => $share->id,
            'user_id' => auth()->id(),
            'guest_name' => auth()->check() ? null : ($validated['guest_name'] ?? 'Guest'),
            'guest_email' => auth()->check() ? auth()->user()->email : ($validated['guest_email'] ?? null),
            'content' => $validated['content'],
            'section' => $validated['section'],
        ]);

        // Send Notifications
        try {
            // Notify CV Owner
            Mail::to($share->cv->user->email)->send(new CommentReceived($comment));

            // Notify Commenter
            if ($comment->guest_email) {
                Mail::to($comment->guest_email)->send(new CommentConfirmation($comment));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send comment notifications: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'comment' => $comment->load('share', 'user'),
        ]);
    }

    /**
     * Store a new share link (for owner).
     */
    public function storeShare(Request $request, Cv $cv): JsonResponse
    {
        $this->authorize('update', $cv);

        $validated = $request->validate([
            'permission' => 'required|in:view,review,edit',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $share = $cv->shares()->create([
            'token' => Str::random(32),
            'permission' => $validated['permission'],
            'expires_at' => $validated['expires_at'],
        ]);

        return response()->json([
            'success' => true,
            'share' => $share,
        ]);
    }

    /**
     * Delete a share link (for owner).
     */
    public function destroyShare(Cv $cv, CvShare $share): JsonResponse
    {
        $this->authorize('update', $cv);

        if ($share->cv_id !== $cv->id) {
            abort(403);
        }

        $share->delete();

        return response()->json([
            'success' => true,
        ]);
    }
    
    /**
     * Get all shares for a CV.
     */
    public function getShares(Cv $cv): JsonResponse
    {
        $this->authorize('view', $cv);
        
        return response()->json([
            'success' => true,
            'shares' => $cv->shares()->latest()->get(),
        ]);
    }
}
