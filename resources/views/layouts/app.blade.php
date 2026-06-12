<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <div class="flex min-h-screen flex-col">
            @include('layouts.partials.navbar')

            <main class="flex-1">
                {{ $slot }}
            </main>

            @include('layouts.partials.footer')
        </div>

        @livewireScripts
    </body>
</html>
