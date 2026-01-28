<x-mail::message>
# Feedback Posted Successfully

Hello {{ $comment->user?->name ?? $comment->guest_name }},

Thank you for providing your feedback on **{{ $comment->cvShare->cv->name }}**. Your comments have been successfully sent to {{ $comment->cvShare->cv->user->name }}.

<x-mail::panel>
**Your Feedback:**

{{ $comment->content }}
</x-mail::panel>

<x-mail::button :url="config('app.url') . '/s/' . $comment->cvShare->token">
Continue Reviewing
</x-mail::button>

Thank you for using CVverse!

Best regards,<br>
The {{ config('app.name') }} Team
</x-mail::message>
