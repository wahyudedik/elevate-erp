@component('mail::message')
    # Customer Interaction Follow-Up

    Dear {{ $customer->name }},

    {!! $emailContent !!}

    Best regards,<br>
    {{ config('app.name') }} Team
@endcomponent
