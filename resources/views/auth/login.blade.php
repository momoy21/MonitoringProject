<!DOCTYPE html>
<html lang="en" class="light-style customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets') }}/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Login - Monitoring Project System</title>
    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/logo/logo_kit.jpg') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />

    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            background: #ffffff;
            overflow-x: hidden;
        }

        .bg-curves {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .curve {
            position: absolute;
            opacity: 0.15;
        }

        .curve-1 {
            top: -50px;
            right: -100px;
            width: 600px;
            height: 600px;
            border: 3px solid #00a0d4;
            border-radius: 50% 40% 60% 50%;
            transform: rotate(45deg);
        }

        .curve-2 {
            bottom: -100px;
            left: -80px;
            width: 500px;
            height: 500px;
            border: 3px solid #00a0d4;
            border-radius: 60% 50% 40% 60%;
            transform: rotate(-30deg);
        }

        .curve-3 {
            top: 50%;
            left: -150px;
            width: 400px;
            height: 400px;
            border: 2px solid #00a0d4;
            border-radius: 45% 55% 50% 50%;
            transform: translateY(-50%) rotate(60deg);
        }

        .curve-4 {
            bottom: 100px;
            right: -120px;
            width: 450px;
            height: 450px;
            border: 2px solid #00a0d4;
            border-radius: 50% 60% 50% 40%;
            transform: rotate(20deg);
        }

        .authentication-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            position: relative;
            z-index: 1;
        }

        .authentication-inner {
            max-width: 400px;
            width: 100%;
            position: relative;
            z-index: 2;
        }

        .card {
            box-shadow: 0 4px 24px 0 rgba(34, 41, 47, 0.1);
            border-radius: 10px;
            background: #ffffff;
        }

        .app-brand {
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .app-brand-logo img {
            max-width: 180px;
            height: auto;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .btn-primary {
            background-color: #00a0d4;
            border-color: #00a0d4;
        }

        .btn-primary:hover {
            background-color: #008bb8;
            border-color: #008bb8;
        }
    </style>
</head>

<body>
    <!-- Background Curves -->
    <div class="bg-curves">
        <div class="curve curve-1"></div>
        <div class="curve curve-2"></div>
        <div class="curve curve-3"></div>
        <div class="curve curve-4"></div>
    </div>

    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">

                <!-- Login Card -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand mb-4">
                            <a href="#" class="app-brand-link gap-2">
                                <span class="app-brand-logo">
                                    <img src="{{ asset('assets/img/logo/Logo_KIT.png') }}" alt="Logo" />
                                </span>
                            </a>
                        </div>

                        <h4 class="mb-2 text-center">Monitoring Project System</h4>
                        <p class="mb-4 text-center">Silakan login untuk melanjutkan</p>

                        <!-- Session Status -->
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible" role="alert">
                                {{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Errors -->
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form id="formAuthentication" method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}"
                                       placeholder="Masukkan email Anda" autofocus required />
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 form-password-toggle">
                                <label class="form-label" for="password">Password</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password" class="form-control @error('password') is-invalid @enderror"
                                           name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                           aria-describedby="password" required />
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember_me" name="remember" />
                                    <label class="form-check-label" for="remember_me"> Ingat Saya </label>
                                </div>
                            </div> --}}

                            <div class="mb-3">
                                <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
                            </div>
                        </form>
                    </div>
                </div>

        </div>
    </div>

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Password Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordToggle = document.querySelector('.form-password-toggle .input-group-text');
            const passwordInput = document.querySelector('#password');

            if (passwordToggle && passwordInput) {
                passwordToggle.addEventListener('click', function() {
                    const icon = this.querySelector('i');
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('bx-hide');
                        icon.classList.add('bx-show');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('bx-show');
                        icon.classList.add('bx-hide');
                    }
                });
            }
        });
    </script>
</body>
</html>
