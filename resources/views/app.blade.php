<!DOCTYPE html>
<html lang="en-US" dir="ltr" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'News Portal') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/theme.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/user.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/simplebar/simplebar.min.css') }}" rel="stylesheet">
    <script src="{{ asset('vendors/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <script>
      (function () {
        var theme = 'light';
        try { theme = localStorage.getItem('phoenixTheme') || 'light'; } catch (e) {}
        if (theme === 'auto') theme = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', theme === 'dark' ? 'dark' : 'light');
      })();
    </script>
    @vite('resources/js/app.js')
    @inertiaHead
</head>
<body>
    @inertia
    <script src="{{ asset('vendors/popper/popper.min.js') }}"></script>
    <script src="{{ asset('vendors/bootstrap/bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendors/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/phoenix.js') }}"></script>
    <style>
      .main > .navbar-vertical { position: fixed; top: var(--phoenix-navbar-top-height, 4rem); bottom: 0; overflow-y: auto; }
      .content { min-height: calc(100vh - var(--phoenix-navbar-top-height, 4rem)); }
      .content > .footer { margin-top: 2rem; }
      @media (max-width: 991.98px) { .main > .navbar-vertical { display: none; } }
    </style>
</body>
</html>
