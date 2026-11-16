<!DOCTYPE html>
<html lang="en-US" dir="ltr" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'News Portal')</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicons/favicon-32x32.png') }}">

    {{-- Apply the saved Phoenix theme (same localStorage key the admin panel and the portal dashboard use)
         BEFORE the CSS paints, so the login pages follow light / dark / auto without a flash. --}}
    <script>
      (function () {
        var theme = 'light';
        try { theme = localStorage.getItem('phoenixTheme') || 'light'; } catch (e) {}
        if (theme === 'auto') {
          theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-bs-theme', theme === 'dark' ? 'dark' : 'light');
      })();
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/theme.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/user.min.css') }}" rel="stylesheet">
    <style>
      .theme-switch {
        position: fixed; top: 1rem; right: 1rem; z-index: 10;
        width: 2.5rem; height: 2.5rem; padding: 0; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        background: var(--phoenix-card-bg); color: var(--phoenix-body-color);
        border: 1px solid var(--phoenix-border-color); cursor: pointer; font-size: 1.1rem; line-height: 1;
      }
      .theme-switch:hover { border-color: var(--phoenix-primary); color: var(--phoenix-primary); }
      /* login cards/labels always use the theme's own colours */
      .main { color: var(--phoenix-body-color); }
      .form-text { color: var(--phoenix-tertiary-color); }
    </style>
</head>
<body>
<button type="button" class="theme-switch" id="themeSwitch" aria-label="Switch light / dark theme" title="Switch theme">
    <span id="themeSwitchIcon">&#9790;</span>
</button>

@yield('body')

<script>
  (function () {
    var root = document.documentElement;
    var icon = document.getElementById('themeSwitchIcon');
    var paint = function () { icon.innerHTML = root.getAttribute('data-bs-theme') === 'dark' ? '&#9728;' : '&#9790;'; };
    paint();
    document.getElementById('themeSwitch').addEventListener('click', function () {
      var next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-bs-theme', next);
      try { localStorage.setItem('phoenixTheme', next); } catch (e) {}
      paint();
    });
  })();
</script>
</body>
</html>
