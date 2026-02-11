<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @if(config('services.midtrans.is_production'))
        <script type="text/javascript"
                src="https://app.midtrans.com/snap/snap.js"
                data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    @else
        <script type="text/javascript"
                src="https://app.sandbox.midtrans.com/snap/snap.js"
                data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    @endif

    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>
    <body class="font-sans antialiased bg-[#FDFDFD]">
        @inertia
    </body>
</html>