<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#e8600c">
        <link rel="manifest" href="{{ request()->is('platform*') ? '/manifest-platform.json' : '/manifest.json' }}">
        <link rel="icon" href="/icon-192-v2.png">
        <link rel="apple-touch-icon" href="/icon-192-v2.png">

        <title inertia>{{ config('app.name', 'مدير الديون') }}</title>

        @routes
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="font-cairo antialiased">
        @inertia
    </body>
</html>
