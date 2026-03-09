<x-mail::message>
# Thank You for Contacting Us

Hello {{ $contactMessage->name }},

We have received your message and will get back to you within 24 hours.

<x-mail::panel>
**Your Message:**

**Subject:** {{ $contactMessage->subject }}

{{ $contactMessage->message }}
</x-mail::panel>

If you have any urgent questions, feel free to reply to this email.

Best regards,<br>
The {{ config('app.name') }} Team
</x-mail::message>
