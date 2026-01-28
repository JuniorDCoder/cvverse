<x-mail::message>
# New Feedback Received

Hello {{ $comment->cvShare->cv->user->name }},

Someone has left new feedback on your shared CV: **{{ $comment->cvShare->cv->name }}**.

<x-mail::panel>
**From:** {{ $comment->user?->name ?? $comment->guest_name }}
**Section:** {{ $comment->section ?? 'General' }}

{{ $comment->content }}
</x-mail::panel>

<x-mail::button :url="config('app.url') . '/cvs/' . $comment->cvShare->cv->id">
View CV Details
</x-mail::button>

Best regards,<br>
The {{ config('app.name') }} Team
</x-mail::message>
