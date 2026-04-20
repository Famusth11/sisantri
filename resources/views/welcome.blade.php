<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SISANTRI') }}</title>

        
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    
            <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        
        :root {
            --brand-dark: #0f5c4d;
            --brand: #1f7a68;
            --brand-soft: #7ab8a6;
            --line-soft: #cfe5dc;
            --surface-soft: #eef6f2;
            --accent-dark: #2f8f6b;
            --accent: #5ca88a;
            --accent-soft: #a9d1c1;
            --brand-gradient: linear-gradient(135deg, #0f5c4d 0%, #1f7a68 100%);
            --surface-gradient: linear-gradient(135deg, #dff2ea 0%, #eef6f2 100%);
            --accent-gradient: linear-gradient(135deg, #2f8f6b 0%, #5ca88a 100%);
            --bg-image-url: url('{{ asset("images/mosque-background.jpg.jpeg") }}');
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }
        
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                linear-gradient(135deg, rgba(15, 92, 77, 0.48) 0%, rgba(31, 122, 104, 0.34) 100%),
                var(--bg-image-url) center center / cover no-repeat fixed;
            z-index: -3;
            background-blend-mode: soft-light;
        }
        
        
        @supports not (background-blend-mode: multiply) {
            body::before {
                background: linear-gradient(135deg, rgba(15, 92, 77, 0.52) 0%, rgba(31, 122, 104, 0.44) 100%);
            }
        }
        
        
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.16) 0%, rgba(0, 0, 0, 0.08) 100%);
            z-index: -2;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        
        .header {
            padding: 20px 0;
            background: rgba(0, 0, 0, 0.28);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 100;
        }
        
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .logo {
            font-family: 'Segoe UI Semibold', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
        }
        
        .logo i {
            font-size: 1.8rem;
            color: white;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.5));
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .btn-primary {
            background: var(--brand-gradient);
            color: white;
            padding: 14px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(15, 92, 77, 0.25);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(15, 92, 77, 0.3);
            color: white;
        }
        
        
        .hero {
            padding: 80px 0;
            text-align: center;
            color: white;
            position: relative;
            z-index: 1;
        }
        
        .hero-title {
            font-family: 'Segoe UI Semibold', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: white;
            text-shadow: 3px 3px 10px rgba(0, 0, 0, 0.7), 0 0 20px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 1;
        }
        
        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }
        
        .btn-secondary {
            background: var(--accent-gradient);
            color: white;
            padding: 15px 30px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            box-shadow: 0 4px 12px rgba(93, 156, 236, 0.25);
        }
        
        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(93, 156, 236, 0.3);
            opacity: 0.95;
        }
        
        
        .features {
            padding: 80px 0;
            background: rgba(0, 0, 0, 0.14);
            backdrop-filter: blur(4px);
            position: relative;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .feature-card {
            background: rgba(0, 0, 0, 0.3);
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(0, 0, 0, 0.38);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
            border-color: rgba(255, 255, 255, 0.25);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: white;
            margin-bottom: 20px;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.5));
        }
        
        .feature-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: white;
            margin-bottom: 15px;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.7);
        }
        
        .feature-description {
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.6);
        }
        
        
        .stats {
            padding: 60px 0;
            text-align: center;
            position: relative;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .stat-item {
            background: rgba(0, 0, 0, 0.28);
            padding: 30px;
            border-radius: 16px;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }
        
        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.4);
            background: rgba(0, 0, 0, 0.36);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--brand);
            margin-bottom: 10px;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.7);
        }
        
        .stat-label {
            color: white;
            font-weight: 500;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.6);
        }
        
        
        .footer {
            background: rgba(0, 0, 0, 0.32);
            padding: 40px 0;
            text-align: center;
            color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(6px);
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.6);
        }
        @media (max-width: 768px) {
            .header {
                padding: 16px 0;
            }

            .container {
                padding: 0 16px;
            }

            .nav {
                justify-content: center;
            }

            .logo {
                width: 100%;
                justify-content: center;
                text-align: center;
                font-size: 1.65rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }
            
            .nav-links {
                width: 100%;
                justify-content: center;
                flex-direction: column;
                gap: 10px;
            }

            .nav-link,
            .btn-primary {
                width: 100%;
                text-align: center;
                justify-content: center;
            }

            .hero {
                padding: 56px 0;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 575.98px) {
            .hero-title {
                font-size: 2rem;
            }

            .feature-card,
            .stat-item {
                padding: 22px 18px;
            }

            .hero-buttons {
                gap: 12px;
            }

            .hero-buttons a {
                width: 100%;
                text-align: center;
            }
        }
            </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="#" class="logo">
                    <i class="fas fa-mosque"></i>
                    SISANTRI
                </a>
                <div class="nav-links">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="nav-link">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="nav-link">
                                <i class="fas fa-sign-in-alt"></i>
                                Masuk
                            </a>
                        @endauth
        @endif
                </div>
            </nav>
        </div>
    </header>

    
    <section class="hero">
        <div class="container">
            <h1 class="hero-title">SISANTRI</h1>
            <div class="hero-buttons">
            @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            Ke Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-primary">
                            <i class="fas fa-sign-in-alt"></i>
                            Masuk
                        </a>
                        <a href="#fitur" class="btn-secondary">
                            <i class="fas fa-info-circle"></i>
                            Lihat Fitur
                        </a>
                    @endauth
            @endif
            </div>
        </div>
    </section>

    
    <section class="features" id="fitur">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <h3 class="feature-title">Scan QR</h3>
                    <p class="feature-description">
                        Presensi santri untuk kegiatan sholat dan diniyah dengan pemindaian QR.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">Rekap Kehadiran</h3>
                    <p class="feature-description">
                        Ringkasan absensi harian dan bulanan bisa dilihat tanpa input ulang.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3 class="feature-title">Kelola Pengguna</h3>
                    <p class="feature-description">
                        Pengaturan akun dan hak akses disesuaikan dengan kebutuhan pengurus.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="feature-title">Tampilan Fleksibel</h3>
                    <p class="feature-description">
                        Tetap nyaman dipakai di desktop, tablet, dan ponsel.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">Data Tersimpan Rapi</h3>
                    <p class="feature-description">
                        Data santri dan absensi tersimpan terpusat dan mudah ditelusuri.
                    </p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h3 class="feature-title">Data Selalu Terbarui</h3>
                    <p class="feature-description">
                        Perubahan data langsung terbaca di halaman yang terkait.
                    </p>
                </div>
            </div>
        </div>
    </section>
    
    
    <footer class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} SISANTRI</p>
        </div>
    </footer>

    
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
    </body>
</html>


