<!DOCTYPE html>
<html lang="{{ \App\Support\AppLocale::current() }}" dir="{{ \App\Support\AppLocale::direction() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <meta name="theme-color" content="#0a3356">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name') }} - {{ __('site.admin_title') }}</title>
        <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <script>
            window.RakeezaAdmin = {
                locale: @json(\App\Support\AppLocale::current()),
                dir: @json(\App\Support\AppLocale::direction())
            };
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f8fafc] antialiased">
        <div id="app"></div>
    </body>
</html>
