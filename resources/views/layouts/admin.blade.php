<!DOCTYPE html>
<html lang="en-US" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin · News Portal')</title>
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicons/favicon-32x32.png') }}">
    <script src="{{ asset('vendors/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&amp;display=swap" rel="stylesheet">
    <link href="{{ asset('vendors/simplebar/simplebar.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/theme-rtl.min.css') }}" rel="stylesheet" id="style-rtl">
    <link href="{{ asset('assets/css/theme.min.css') }}" rel="stylesheet" id="style-default">
    <link href="{{ asset('assets/css/user-rtl.min.css') }}" rel="stylesheet" id="user-style-rtl">
    <link href="{{ asset('assets/css/user.min.css') }}" rel="stylesheet" id="user-style-default">
    <script>
      var phoenixIsRTL = window.config.config.phoenixIsRTL;
      if (phoenixIsRTL) {
        document.getElementById('style-default').disabled = true;
        document.getElementById('user-style-default').disabled = true;
        document.documentElement.setAttribute('dir', 'rtl');
      } else {
        document.getElementById('style-rtl').disabled = true;
        document.getElementById('user-style-rtl').disabled = true;
      }
    </script>
    <style>
      .path-cell { max-width: 340px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

      /* Tables: Phoenix-style spacing, size and header look */
      .content .table { font-size: .875rem; margin-bottom: 0; }
      .content .table > :not(caption) > * > * { padding: .75rem 1rem; vertical-align: middle; }
      .content .table > :not(caption) > * > *:first-child { padding-left: 1.25rem; }
      .content .table > :not(caption) > * > *:last-child { padding-right: 1.25rem; }
      .content .table thead th { font-size: .72rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase;
                                 color: var(--phoenix-tertiary-color); white-space: nowrap; }
      .content .table td { white-space: nowrap; }
      .content .table td.path-cell { white-space: nowrap; }
      .content .table code { color: var(--phoenix-body-color); font-size: .8rem; }
      .content .card > .table-responsive { border-radius: inherit; }
      .stat-label { font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; color: var(--phoenix-tertiary-color); font-weight: 700; }
      .stat-value { font-size: 1.75rem; font-weight: 800; line-height: 1.2; color: var(--phoenix-emphasis-color); }

      /* ---- Theme-safe colours: follow Phoenix light/dark variables, never hardcoded colours ---- */
      .content .card { color: var(--phoenix-body-color); }
      .content .card .fs-3,
      .content .card .fs-2,
      .stat-value { color: var(--phoenix-emphasis-color); }
      .content .table {
        --phoenix-table-color: var(--phoenix-body-color);
        --phoenix-table-bg: transparent;
        color: var(--phoenix-body-color);
      }
      .content .table > :not(caption) > * > * { color: var(--phoenix-body-color) !important; }
      .content .table .fw-semibold,
      .content .table .fw-bold { color: var(--phoenix-emphasis-color) !important; }
      .content .table > thead > tr > th {
        color: var(--phoenix-tertiary-color) !important;
        background-color: var(--phoenix-card-bg) !important;
        border-bottom: 1px solid var(--phoenix-border-color);
      }
      .content .table code { color: var(--phoenix-body-color); background: transparent; }

      /* Keep portal search and status controls aligned on wider screens. */
      .portal-filters { margin-bottom: 1rem; }
      .portal-filters .card-body {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(190px, 230px) auto auto;
        align-items: center;
        gap: 10px;
        padding: 14px;
      }
      .portal-search,
      .portal-status-filter { min-width: 0; }
      .portal-filters .form-control,
      .portal-filters .form-select,
      .portal-filters .btn {
        min-height: 44px;
      }
      .portal-filters .form-control,
      .portal-filters .form-select { width: 100%; }
      .portal-filters .btn { white-space: nowrap; }
      @media (max-width: 575.98px) {
        .portal-filters .card-body { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
        .portal-search,
        .portal-status-filter { grid-column: 1 / -1; }
        .portal-filters .btn { width: 100%; }
      }
    </style>
</head>
<body>
<main class="main" id="top">

  {{-- ============ Vertical sidebar (same markup as the Phoenix dashboard) ============ --}}
  <nav class="navbar navbar-vertical navbar-expand-lg">
    <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
      <div class="navbar-vertical-content">
        <ul class="navbar-nav flex-column" id="navbarVerticalNav">
          <li class="nav-item">
            <p class="navbar-vertical-label">Admin</p>
            <hr class="navbar-vertical-line" />

            <div class="nav-item-wrapper"><a class="nav-link label-1 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}" role="button" aria-expanded="false">
                <div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="pie-chart"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">Dashboard</span></span></div>
              </a></div>

            <div class="nav-item-wrapper"><a class="nav-link label-1 {{ request()->routeIs('admin.portals.*') ? 'active' : '' }}" href="{{ route('admin.portals.index') }}" role="button" aria-expanded="false">
                <div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="globe"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">Portals</span></span></div>
              </a></div>

            <div class="nav-item-wrapper"><a class="nav-link label-1 {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}" href="{{ route('admin.requests.index') }}" role="button" aria-expanded="false">
                <div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="inbox"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">Requests</span>@if (($pendingRequestCount ?? 0) > 0)<span class="badge ms-2 badge badge-phoenix badge-phoenix-warning">{{ $pendingRequestCount }}</span>@endif</span></div>
              </a></div>

            <div class="nav-item-wrapper"><a class="nav-link label-1 {{ request()->routeIs('admin.activity.*') ? 'active' : '' }}" href="{{ route('admin.activity.index') }}" role="button" aria-expanded="false">
                <div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="activity"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">Activity monitor</span></span></div>
              </a></div>

            <div class="nav-item-wrapper"><a class="nav-link label-1 {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}" href="{{ route('admin.packages.index') }}" role="button" aria-expanded="false">
                <div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="package"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">Packages</span></span></div>
              </a></div>
          </li>
        </ul>
      </div>
    </div>
    <div class="navbar-vertical-footer"><button class="btn navbar-vertical-toggle border-0 fw-semibold w-100 white-space-nowrap d-flex align-items-center"><span class="uil uil-left-arrow-to-left fs-8"></span><span class="uil uil-arrow-from-right fs-8"></span><span class="navbar-vertical-footer-text ms-2">Collapsed View</span></button></div>
  </nav>

  {{-- ============ Top navbar ============ --}}
  <nav class="navbar navbar-top fixed-top navbar-expand" id="navbarDefault">
    <div class="collapse navbar-collapse justify-content-between">
      <div class="navbar-logo">
        <button class="btn navbar-toggler navbar-toggler-humburger-icon hover-bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#navbarVerticalCollapse" aria-controls="navbarVerticalCollapse" aria-expanded="false" aria-label="Toggle Navigation"><span class="navbar-toggle-icon"><span class="toggle-line"></span></span></button>
        <a class="navbar-brand me-1 me-sm-3" href="{{ route('admin.dashboard') }}">
          <div class="d-flex align-items-center">
            <div class="d-flex align-items-center"><img src="{{ asset('assets/img/icons/logo.png') }}" alt="phoenix" width="27" />
              <h5 class="logo-text ms-2 d-none d-sm-block">phoenix</h5>
              <span class="badge badge-phoenix badge-phoenix-primary ms-2 d-none d-sm-inline-block">Admin</span>
            </div>
          </div>
        </a>
      </div>

      <ul class="navbar-nav navbar-nav-icons flex-row">
        <li class="nav-item">
          <div class="theme-control-toggle px-2"><input class="form-check-input ms-0 theme-control-toggle-input" type="checkbox" data-theme-control="phoenixTheme" value="dark" id="themeControlToggle" /><label class="mb-0 theme-control-toggle-label theme-control-toggle-light" for="themeControlToggle" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Switch theme" style="height:32px;width:32px;"><span class="icon" data-feather="moon"></span></label><label class="mb-0 theme-control-toggle-label theme-control-toggle-dark" for="themeControlToggle" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Switch theme" style="height:32px;width:32px;"><span class="icon" data-feather="sun"></span></label></div>
        </li>
        <li class="nav-item dropdown"><a class="nav-link lh-1 pe-0" id="navbarDropdownUser" href="#!" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
            <div class="avatar avatar-l">
              <div class="avatar-name rounded-circle"><span>{{ strtoupper(substr($currentAdmin->name, 0, 1)) }}</span></div>
            </div>
          </a>
          <div class="dropdown-menu dropdown-menu-end navbar-dropdown-caret py-0 dropdown-profile shadow border" aria-labelledby="navbarDropdownUser">
            <div class="card position-relative border-0">
              <div class="card-body p-0">
                <div class="text-center pt-4 pb-3">
                  <div class="avatar avatar-xl">
                    <div class="avatar-name rounded-circle"><span>{{ strtoupper(substr($currentAdmin->name, 0, 1)) }}</span></div>
                  </div>
                  <h6 class="mt-2 text-body-emphasis">{{ $currentAdmin->name }}</h6>
                  <p class="fs-9 text-body-tertiary mb-0">{{ $currentAdmin->email }}</p>
                </div>
              </div>
              <div class="card-footer p-0 border-top border-translucent">
                <div class="px-3 py-3">
                  <form method="POST" action="{{ route('admin.logout') }}">@csrf
                    <button class="btn btn-phoenix-secondary d-flex flex-center w-100" type="submit"><span class="me-2" data-feather="log-out"></span>Sign out</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </nav>

  {{-- ============ Content ============ --}}
  <div class="content">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
      <div>
        <h2 class="mb-1 lh-sm">@yield('page_title', 'Admin')</h2>
        @hasSection('page_subtitle')<h5 class="text-body-tertiary fw-semibold mb-0">@yield('page_subtitle')</h5>@endif
      </div>
      <div>@yield('page_actions')</div>
    </div>

    @if (session('success'))<div class="alert alert-subtle-success" role="alert">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-subtle-danger" role="alert">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    @yield('content')

    <footer class="footer position-absolute">
      <div class="row g-0 justify-content-between align-items-center h-100">
        <div class="col-12 col-sm-auto text-center">
          <p class="mb-0 mt-2 mt-sm-0 text-body">News Portal Admin<span class="d-none d-sm-inline-block mx-1">|</span><br class="d-sm-none" />{{ date('Y') }} &copy;</p>
        </div>
        <div class="col-12 col-sm-auto text-center">
          <p class="mb-0 text-body-tertiary text-opacity-85">v1.23.0</p>
        </div>
      </div>
    </footer>
  </div>
</main>

<script src="{{ asset('vendors/popper/popper.min.js') }}"></script>
<script src="{{ asset('vendors/bootstrap/bootstrap.min.js') }}"></script>
<script src="{{ asset('vendors/anchorjs/anchor.min.js') }}"></script>
<script src="{{ asset('vendors/is/is.min.js') }}"></script>
<script src="{{ asset('vendors/fontawesome/all.min.js') }}"></script>
<script src="{{ asset('vendors/lodash/lodash.min.js') }}"></script>
<script src="{{ asset('vendors/list.js/list.min.js') }}"></script>
<script src="{{ asset('vendors/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('vendors/dayjs/dayjs.min.js') }}"></script>
<script src="{{ asset('assets/js/phoenix.js') }}"></script>
@stack('scripts')
</body>
</html>
