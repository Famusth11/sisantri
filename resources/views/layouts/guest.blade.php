<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SISANTRI') }}</title>

        @vite('resources/css/app.css')
        <style>
            body {
                background: linear-gradient(135deg, #f7f4eb 0%, #efe7d6 55%, #e4dcc7 100%);
            }

            .guest-shell {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1.25rem;
            }

            .guest-panel {
                width: min(100%, 34rem);
                background: rgba(255, 255, 255, 0.98);
                border: 1px solid rgba(207, 229, 220, 0.95);
                border-radius: 1.25rem;
                box-shadow: 0 16px 32px rgba(15, 92, 77, 0.12);
                overflow: hidden;
            }

            .guest-panel__brand {
                padding: 1.15rem 1.25rem 0.5rem;
                text-align: center;
            }

            .guest-panel__brand a {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 4.5rem;
                height: 4.5rem;
                border-radius: 50%;
                background: linear-gradient(135deg, #0f5c4d 0%, #1f7a68 100%);
                box-shadow: 0 10px 24px rgba(15, 92, 77, 0.24);
            }

            .guest-panel__card {
                width: 100%;
                margin-top: 0;
                padding: 1.5rem;
                background: transparent;
                box-shadow: none;
                border-radius: 0;
            }

            @media (max-width: 575.98px) {
                .guest-shell {
                    padding: 0.75rem;
                }

                .guest-panel__brand {
                    padding-top: 1rem;
                }

                .guest-panel__card {
                    padding: 1rem;
                }
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="guest-shell">
            <div class="guest-panel">
                <div class="guest-panel__brand">
                    <a href="/">
                        <x-application-logo class="w-10 h-10 fill-current text-white" />
                    </a>
                </div>

                <div class="guest-panel__card">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>

