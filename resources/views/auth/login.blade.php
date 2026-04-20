<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SISANTRI</title>
    @vite('resources/css/app.css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        
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
        }
        
        body {
            background: linear-gradient(135deg, #f7f4eb 0%, #efe7d6 50%, #e4dcc7 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(15, 92, 77, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(207, 229, 220, 0.9);
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-icon {
            width: 80px;
            height: 80px;
            background: var(--brand-gradient);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 8px 24px rgba(15, 92, 77, 0.3);
        }
        
        .logo-icon i {
            font-size: 2.5rem;
            color: white;
        }
        
        .login-title {
            color: #2d3748;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .login-subtitle {
            color: #718096;
            font-size: 1rem;
        }

        .login-note {
            margin-top: 10px;
            color: #5f6b7a;
            font-size: 0.92rem;
            line-height: 1.5;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-label {
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control {
            border: 2px solid var(--surface-soft);
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }
        
        .form-control:focus {
            border-color: var(--brand-dark);
            box-shadow: 0 0 0 3px rgba(15, 92, 77, 0.18);
            background: white;
        }
        
        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }
        
        .input-icon:hover {
            color: var(--brand-dark);
        }
        
        #togglePassword {
            cursor: pointer;
            z-index: 10;
        }
        
        .btn-login {
            background: var(--brand-gradient);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: 500;
            font-size: 1.1rem;
            color: white;
            width: 100%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(15, 92, 77, 0.25);
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(15, 92, 77, 0.3);
            color: white;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 25px 0;
        }
        
        .form-check-input:checked {
            background-color: var(--brand-dark);
            border-color: var(--brand-dark);
        }
        
        .form-check-label {
            color: #4a5568;
            font-size: 0.9rem;
        }
        
        .forgot-link {
            color: #0f5c4d;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .forgot-link:hover {
            color: #0a463b;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
            color: #c53030;
        }
        
        .text-danger {
            color: #e53e3e !important;
            font-size: 0.875rem;
            margin-top: 5px;
        }
        
        @media (max-width: 575.98px) {
            .login-container {
                padding: 12px;
            }

            .login-card {
                padding: 24px 18px;
                border-radius: 16px;
            }

            .logo-icon {
                width: 64px;
                height: 64px;
                margin-bottom: 16px;
            }

            .logo-icon i {
                font-size: 2rem;
            }

            .login-title {
                font-size: 1.65rem;
            }

            .login-subtitle {
                font-size: 0.95rem;
            }

            .form-control,
            .btn-login {
                padding-top: 13px;
                padding-bottom: 13px;
            }

            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <div class="logo-icon">
                    <i class="fas fa-mosque"></i>
                </div>
                <h1 class="login-title">SISANTRI</h1>
                <p class="login-subtitle">Halaman masuk</p>
                <p class="login-note">Akun dibuat oleh admin. Jika belum memiliki akses, silakan hubungi admin.</p>
            </div>
            
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-2"></i>Email
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus 
                           autocomplete="username"
                           placeholder="Masukkan email Anda">
                    <i class="fas fa-envelope input-icon"></i>
                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
        </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Password
                    </label>
                    <div style="position: relative;">
                        <input type="password" 
                               id="password" 
                               name="password"
                               class="form-control @error('password') is-invalid @enderror" 
                               required 
                               autocomplete="current-password"
                               placeholder="Masukkan password Anda"
                               style="padding-right: 50px;">
                        <i class="fas fa-lock input-icon" style="right: 45px; left: auto;"></i>
                        <i class="fas fa-eye input-icon" id="togglePassword" style="right: 15px; cursor: pointer; z-index: 10;" onclick="togglePasswordVisibility()"></i>
                    </div>
                    @error('password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
        </div>

                <div class="remember-forgot">
                    <div class="form-check">
                        <input type="checkbox" 
                               id="remember_me" 
                               name="remember" 
                               class="form-check-input">
                        <label for="remember_me" class="form-check-label">
                            Ingat saya
            </label>
        </div>

            @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            Lupa password?
                </a>
            @endif
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Masuk
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePassword');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>

