<x-mail::message>
    # Introduction

    Welcome to Elevate ERP! We're excited to have you on board. To get started, please verify your email address by
    clicking the button below.

    <x-mail::button :url="$url">
        Verify Email Address
    </x-mail::button>

    If you did not create an account, no further action is required.

    Thank you for choosing Elevate ERP for your business management needs.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
