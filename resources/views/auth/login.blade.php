<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · {{ config('app.name') }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="login-page">
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
        <div class="card login-card border-0" style="max-width: 450px; width: 100%;">
            <div class="login-brand d-flex align-items-center gap-3"><img src="{{ asset('images/logo.jpg') }}" alt="Logo de Farmacia Académica" width="74" height="74" class="rounded-circle login-logo"><div class="login-copy"><div class="page-kicker text-warning">Gestión de farmacia</div><h1 class="h3 mb-1">Farmacia Académica</h1><p class="mb-0 text-white-50">Tu inventario, siempre bajo control.</p></div></div>
            <div class="card-body p-4 p-md-5">
                <div class="mb-4"><h2 class="h5 mb-1">Iniciar sesión</h2><p class="text-secondary mb-0">Ingresa tus credenciales para continuar.</p></div>

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-check mb-4">
                        <input id="remember" name="remember" type="checkbox" class="form-check-input" value="1">
                        <label for="remember" class="form-check-label">Recordarme</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
