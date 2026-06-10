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

            <x-toast>
                <x-slot:successIcon>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 10.5l2 2 4-4" />
                        <circle cx="10" cy="10" r="8" />
                    </svg>
                </x-slot:successIcon>

                <x-slot:errorIcon>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.5 6.5l7 7M13.5 6.5l-7 7" />
                        <circle cx="10" cy="10" r="8" />
                    </svg>
                </x-slot:errorIcon>

                <x-slot:infoIcon>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 8.5v5m0-8.5h.01" />
                        <circle cx="10" cy="10" r="8" />
                    </svg>
                </x-slot:infoIcon>

                <x-slot:warningIcon>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 7.5v4.5m0 2h.01" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 3.5h3L18 15.5H2L8.5 3.5Z" />
                    </svg>
                </x-slot:warningIcon>
            </x-toast>

            <main class="flex-1">
                {{ $slot }}
            </main>

            @include('layouts.partials.footer')
        </div>

        @livewireScripts
    </body>
</html>
