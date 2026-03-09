<x-mail::message>
# New Contact Message

You have received a new contact message from **{{ $contactMessage->name }}**.

<x-mail::panel>
**From:** {{ $contactMessage->name }} ({{ $contactMessage->email }})
@if($contactMessage->company)
**Company:** {{ $contactMessage->company }}
@endif
**Subject:** {{ $contactMessage->subject }}

**Message:**

{{ $contactMessage->message }}
</x-mail::panel>

<x-mail::button :url="config('app.url') . '/admin/contact-messages'">
View in Dashboard
</x-mail::button>

Best regards,<br>
The {{ config('app.name') }} System
</x-mail::message>
