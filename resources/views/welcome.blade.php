<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900">

<div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-8 w-full max-w-md">
    <h1 class="text-2xl font-bold mb-6 text-center text-gray-800 dark:text-white">
        {{ config('app.name', 'Laravel') }}
    </h1>

    <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
        <div class="flex justify-between">
            <span>Laravel Version</span>
            <span class="font-semibold">{{ app()->version() }}</span>
        </div>

        <div class="flex justify-between">
            <span>PHP Version</span>
            <span class="font-semibold">{{ phpversion() }}</span>
        </div>

        <div class="flex justify-between">
            <span>Environment</span>
            <span class="font-semibold">{{ app()->environment() }}</span>
        </div>

        <div class="flex justify-between">
            <span>Debug Mode</span>
            <span class="font-semibold">{{ config('app.debug') ? 'true' : 'false' }}</span>
        </div>

        <div class="flex justify-between">
            <span>Timezone</span>
            <span class="font-semibold">{{ config('app.timezone') }}</span>
        </div>

        <div class="flex justify-between">
            <span>Locale</span>
            <span class="font-semibold">{{ app()->getLocale() }}</span>
        </div>

        <div class="flex justify-between">
            <span>Cache Driver</span>
            <span class="font-semibold">{{ config('cache.default') }}</span>
        </div>

        <div class="flex justify-between">
            <span>Queue Driver</span>
            <span class="font-semibold">{{ config('queue.default') }}</span>
        </div>

        <div class="flex justify-between">
            <span>Session Driver</span>
            <span class="font-semibold">{{ config('session.driver') }}</span>
        </div>

        <div class="flex justify-between">
            <span>Composer Version</span>
            <span class="font-semibold">{{ exec('composer --version') }}</span>
        </div>
    </div>

    <div class="mt-6 text-center text-xs text-gray-500">
        Server Time: {{ now() }}
    </div>
</div>

</body>
</html>
