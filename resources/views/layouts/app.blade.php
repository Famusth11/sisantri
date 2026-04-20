<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" crossorigin="anonymous">

        <style>
            :root {
                --app-bg: #f7f4eb;
                --panel-bg: #fffdf8;
                --panel-muted: #f3efe3;
                --border-soft: #ddd5c3;
                --border-strong: #cabfa8;
                --text-main: #20352f;
                --text-soft: #607066;
                --text-faint: #908a78;
                --brand: #0f5c4d;
                --brand-dark: #0a463b;
                --brand-soft: #dff2ea;
                --accent-green: #2f8f6b;
                --accent-orange: #c6922d;
                --accent-red: #c45b4c;
                --shadow-soft: 0 12px 30px rgba(32, 53, 47, 0.09);
                --shadow-card: 0 10px 24px rgba(65, 77, 67, 0.12);
                --base-font-size: 18px;
                --tablet-font-size: 16.75px;
                --mobile-font-size: 15.5px;
            }

            * {
                box-sizing: border-box;
            }

            html {
                font-size: var(--base-font-size);
            }

            body {
                margin: 0;
                min-height: 100vh;
                background: var(--app-bg);
                color: var(--text-main);
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            body.is-mobile-nav-open {
                overflow: hidden;
            }

            .app-shell {
                display: flex;
                min-height: 100vh;
            }

            .app-sidebar {
                width: 260px;
                flex-shrink: 0;
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
                padding: 1.5rem 1rem;
                background: linear-gradient(180deg, #fffdf8 0%, #f4efe2 100%);
                border-right: 1px solid var(--border-soft);
                box-shadow: 12px 0 30px rgba(148, 163, 184, 0.08);
            }

            .sidebar-brand {
                display: flex;
                align-items: center;
                gap: 0.85rem;
                padding: 0.35rem 0.4rem 0.1rem;
            }

            .sidebar-brand__mark {
                width: 2.7rem;
                height: 2.7rem;
                display: grid;
                place-items: center;
                border-radius: 0.95rem;
                background: linear-gradient(135deg, var(--brand) 0%, #1f7a68 100%);
                color: #fff;
                box-shadow: 0 10px 24px rgba(15, 92, 77, 0.28);
            }

            .sidebar-brand__title {
                font-family: 'Segoe UI Semibold', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                font-size: 1rem;
                font-weight: 700;
                letter-spacing: 0.04em;
            }

            .sidebar-brand__caption {
                color: var(--text-soft);
                font-size: 0.75rem;
            }

            .sidebar-user {
                display: flex;
                align-items: center;
                gap: 0.85rem;
                padding: 0.9rem;
                background: var(--panel-muted);
                border: 1px solid var(--border-soft);
                border-radius: 1rem;
            }

            .sidebar-user__avatar {
                width: 2.5rem;
                height: 2.5rem;
                display: grid;
                place-items: center;
                border-radius: 999px;
                background: #dcefe7;
                color: var(--brand);
                font-weight: 700;
            }

            .sidebar-user__name {
                font-weight: 600;
                font-size: 0.92rem;
            }

            .sidebar-user__role {
                color: var(--text-soft);
                font-size: 0.76rem;
            }

            .sidebar-nav {
                display: flex;
                flex-direction: column;
                gap: 0.35rem;
            }

            .sidebar-group {
                display: flex;
                flex-direction: column;
                gap: 0.35rem;
                margin: 0.3rem 0 0.45rem;
            }

            .sidebar-group__label {
                padding: 0 0.75rem;
                color: var(--text-faint);
                font-size: 0.7rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                text-transform: none;
            }

            .sidebar-link,
            .sidebar-logout__button {
                width: 100%;
                display: flex;
                align-items: center;
                gap: 0.8rem;
                padding: 0.85rem 0.9rem;
                border: 1px solid transparent;
                border-radius: 0.95rem;
                color: var(--text-soft);
                background: transparent;
                text-decoration: none;
                font-weight: 500;
                font-size: 0.92rem;
                transition: 0.2s ease;
            }

            .sidebar-link:hover,
            .sidebar-logout__button:hover {
                color: var(--brand);
                background: var(--brand-soft);
                border-color: #c7e2d8;
            }

            .sidebar-link.is-active {
                color: #fff;
                background: linear-gradient(135deg, var(--brand) 0%, #1f7a68 100%);
                box-shadow: 0 12px 24px rgba(15, 92, 77, 0.2);
            }

            .sidebar-link i,
            .sidebar-logout__button i {
                width: 1rem;
                text-align: center;
            }

            .sidebar-logout {
                margin-top: auto;
            }

            .sidebar-logout__button {
                border-color: var(--border-soft);
                background: #fff;
                cursor: pointer;
            }

            .app-main {
                flex: 1;
                min-width: 0;
                display: flex;
                flex-direction: column;
                padding: 1rem 1.25rem 1.5rem;
            }

            .app-sidebar-backdrop {
                position: fixed;
                inset: 0;
                background: rgba(32, 53, 47, 0.36);
                backdrop-filter: blur(2px);
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.25s ease;
                z-index: 1040;
            }

            .app-topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 0.9rem 1.1rem;
                margin-bottom: 1rem;
                background: rgba(255, 253, 248, 0.92);
                border: 1px solid var(--border-soft);
                border-radius: 1rem;
                box-shadow: var(--shadow-soft);
                backdrop-filter: blur(12px);
            }

            .app-topbar__lead {
                display: flex;
                align-items: center;
                gap: 0.85rem;
                min-width: 0;
            }

            .app-topbar__title {
                font-family: 'Segoe UI Semibold', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                font-size: 1rem;
                font-weight: 700;
            }

            .app-topbar__meta {
                color: var(--text-soft);
                font-size: 0.82rem;
            }

            .app-topbar__actions {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                flex-wrap: wrap;
                justify-content: flex-end;
            }

            .topbar-pill {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                padding: 0.55rem 0.85rem;
                border-radius: 999px;
                background: var(--brand-soft);
                color: var(--brand);
                font-size: 0.8rem;
                font-weight: 600;
                white-space: nowrap;
            }

            .mobile-nav-toggle {
                display: none;
                align-items: center;
                justify-content: center;
                width: 2.8rem;
                height: 2.8rem;
                border: 1px solid var(--border-soft);
                border-radius: 0.9rem;
                background: #fff;
                color: var(--brand);
                box-shadow: var(--shadow-soft);
            }

            .mobile-nav-toggle:hover {
                background: var(--brand-soft);
                color: var(--brand-dark);
            }

            .main-content {
                display: block;
                min-width: 0;
            }

            .flash-stack {
                display: grid;
                gap: 0.85rem;
                margin-bottom: 1rem;
            }

            .alert {
                margin: 0;
                border: 1px solid var(--border-soft);
                border-radius: 1rem;
                box-shadow: var(--shadow-card);
            }

            .card {
                border: 1px solid var(--border-soft);
                border-radius: 1rem;
                box-shadow: var(--shadow-card);
            }

            .table {
                border-radius: 1rem;
                overflow: hidden;
            }

            .table-responsive {
                -webkit-overflow-scrolling: touch;
            }

            .form-control,
            .form-select {
                border-radius: 0.8rem;
                border-color: var(--border-strong);
            }

            .form-control:focus,
            .form-select:focus {
                border-color: var(--brand);
                box-shadow: 0 0 0 0.2rem rgba(41, 71, 163, 0.12);
            }

            @media (max-width: 991.98px) {
                html {
                    font-size: var(--tablet-font-size);
                }

                .app-sidebar {
                    position: fixed;
                    top: 0;
                    left: 0;
                    z-index: 1050;
                    width: min(86vw, 320px);
                    height: 100vh;
                    overflow-y: auto;
                    border-right: 1px solid var(--border-soft);
                    border-bottom: none;
                    box-shadow: 18px 0 40px rgba(32, 53, 47, 0.16);
                    transform: translateX(calc(-100% - 1rem));
                    transition: transform 0.25s ease;
                }

                body.is-mobile-nav-open .app-sidebar {
                    transform: translateX(0);
                }

                body.is-mobile-nav-open .app-sidebar-backdrop {
                    opacity: 1;
                    pointer-events: auto;
                }

                .app-main {
                    padding: 1rem;
                }

                .app-topbar {
                    align-items: flex-start;
                }

                .app-topbar__lead {
                    width: 100%;
                }

                .app-topbar__actions {
                    width: 100%;
                    justify-content: flex-start;
                }

                .mobile-nav-toggle {
                    display: inline-flex;
                }
            }

            @media (max-width: 575.98px) {
                html {
                    font-size: var(--mobile-font-size);
                }

                .app-sidebar {
                    padding: 1rem 0.85rem;
                }

                .app-topbar {
                    padding: 0.8rem 0.9rem;
                }

                .app-topbar__actions {
                    gap: 0.5rem;
                }

                .topbar-pill {
                    padding: 0.5rem 0.75rem;
                    max-width: 100%;
                }

                .mobile-nav-toggle {
                    width: 2.55rem;
                    height: 2.55rem;
                }

                .app-main {
                    padding: 0.75rem;
                }
            }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="app-shell">
            @include('layouts.navigation')
            <div class="app-sidebar-backdrop" data-mobile-nav-backdrop></div>

            <div class="app-main">
                <header class="app-topbar">
                    <div class="app-topbar__lead">
                        <button type="button" class="mobile-nav-toggle" aria-label="Buka menu" aria-expanded="false" data-mobile-nav-toggle>
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="app-topbar__title">Dashboard</div>
                    </div>
                    <div class="app-topbar__actions">
                        <span class="topbar-pill">
                            <i class="fas fa-calendar-alt"></i>
                            {{ now()->locale('id')->translatedFormat('d M Y') }}
                        </span>
                        <span class="topbar-pill">
                            <i class="fas fa-user-shield"></i>
                            {{ Auth::user()->role }}
                        </span>
                    </div>
                </header>

                <main class="main-content">
                    <div class="flash-stack">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Terjadi kesalahan:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                    </div>

                    @yield('content')
                </main>
            </div>
        </div>
        <script>
            (function() {
                function initMobileNav() {
                    const body = document.body;
                    const toggle = document.querySelector('[data-mobile-nav-toggle]');
                    const backdrop = document.querySelector('[data-mobile-nav-backdrop]');
                    const navCloseTargets = document.querySelectorAll('.app-sidebar .sidebar-link, .app-sidebar .sidebar-logout__button');

                    if (!toggle || !backdrop) {
                        return;
                    }

                    const setOpenState = function(isOpen) {
                        body.classList.toggle('is-mobile-nav-open', isOpen);
                        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    };

                    toggle.addEventListener('click', function() {
                        setOpenState(!body.classList.contains('is-mobile-nav-open'));
                    });

                    backdrop.addEventListener('click', function() {
                        setOpenState(false);
                    });

                    navCloseTargets.forEach(function(item) {
                        item.addEventListener('click', function() {
                            if (window.innerWidth <= 991.98) {
                                setOpenState(false);
                            }
                        });
                    });

                    window.addEventListener('resize', function() {
                        if (window.innerWidth > 991.98) {
                            setOpenState(false);
                        }
                    });

                    document.addEventListener('keydown', function(event) {
                        if (event.key === 'Escape') {
                            setOpenState(false);
                        }
                    });
                }

                function initAlerts() {
                    setTimeout(function() {
                        if (typeof bootstrap === 'undefined' || !bootstrap.Alert) {
                            return;
                        }

                        document.querySelectorAll('.alert').forEach(function(alert) {
                            try {
                                const bsAlert = new bootstrap.Alert(alert);
                                bsAlert.close();
                            } catch (error) {
                                console.warn(error);
                            }
                        });
                    }, 5000);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function() {
                        initMobileNav();
                        initAlerts();
                    });
                } else {
                    initMobileNav();
                    initAlerts();
                }
            })();
        </script>
    </body>
</html>

