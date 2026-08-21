<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        >

        <meta
            name="theme-color"
            content="#123C5E"
        >

        <style>
            html {
                background-color: oklch(1 0 0);
                color-scheme: light;
            }
        </style>

        <link
            rel="icon"
            href="/favicon.svg"
            type="image/svg+xml"
        >

        @fonts

        @viteReactRefresh
        @vite([
            'resources/css/app.css',
            'resources/js/app.tsx',
            "resources/js/pages/{$page['component']}.tsx"
        ])

        <x-inertia::head>
            <title>
                {{ config('app.name', 'BAST') }}
            </title>
        </x-inertia::head>
    </head>

    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>