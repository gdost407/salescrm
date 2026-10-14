<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <title>Set up your company | {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased dark:bg-zinc-900 dark:text-zinc-100">
        <main data-company-onboarding class="mx-auto w-full max-w-4xl px-4 py-8 sm:px-6 sm:py-12">
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm sm:p-8 dark:border-zinc-700 dark:bg-zinc-800">
                {{ $slot }}
            </div>
        </main>
        @fluxScripts
    </body>
</html>
