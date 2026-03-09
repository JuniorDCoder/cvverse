<x-mail::message>
# Reply to Your Message

Hello {{ $contactMessage->name }},

We have responded to your inquiry regarding **"{{ $contactMessage->subject }}"**.

<x-mail::panel>
**Our Reply:**

{{ $contactMessage->admin_reply }}
</x-mail::panel>

<x-mail::panel>
**Your Original Message:**

{{ $contactMessage->message }}
</x-mail::panel>

If you have any further questions, feel free to reply to this email.

Best regards,<br>
The {{ config('app.name') }} Team
</x-mail::message>
