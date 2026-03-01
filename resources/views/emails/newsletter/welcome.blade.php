<x-mail::message>
# Welcome to {{ config('app.name') }}!

Hi{{ $subscriber->name ? ' ' . $subscriber->name : '' }},

Thank you for subscribing to our newsletter! You'll receive updates about:

- New CV templates & features
- Career tips & industry insights
- Exclusive offers & early access

We're excited to help you build the perfect CV and land your dream job.

<x-mail::button :url="config('app.url')">
Explore CVverse
</x-mail::button>

If you didn't subscribe, or wish to unsubscribe at any time, click below:

<x-mail::subcopy>
[Unsubscribe]({{ config('app.url') }}/newsletter/unsubscribe/{{ $subscriber->token }})
</x-mail::subcopy>

Best regards,<br>
The {{ config('app.name') }} Team
</x-mail::message>
