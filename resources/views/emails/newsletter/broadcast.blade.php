<x-mail::message>
{!! $body !!}

<x-mail::button :url="config('app.url')">
Visit {{ config('app.name') }}
</x-mail::button>

@if($unsubscribeUrl)
<x-mail::subcopy>
You're receiving this because you subscribed to our newsletter. [Unsubscribe]({{ $unsubscribeUrl }})
</x-mail::subcopy>
@endif

Best regards,<br>
The {{ config('app.name') }} Team
</x-mail::message>
