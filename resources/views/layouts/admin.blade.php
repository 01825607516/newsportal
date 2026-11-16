<!DOCTYPE html>
<html lang="en-US" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Â· News Portal')</title>
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

  .portal-stat-card { min-height: 132px; overflow: hidden; }
  .portal-stat-card .card-body { padding: 20px 22px; }
  .portal-stat-label { color: #727d94; font-size: .82rem; font-weight: 600; }
  .portal-stat-value { margin-top: 13px; color: #252b3b; font-size: 2.25rem; font-weight: 750; line-height: 1; }
  .portal-stat-mark { width: 10px; height: 10px; border-radius: 50%; background: #527df4; box-shadow: 0 0 0 5px #edf2ff; }
  .portal-stat-active .portal-stat-mark { background: #20a574; box-shadow: 0 0 0 5px #e8f8f1; }
  .portal-stat-suspended .portal-stat-mark { background: #e35d6a; box-shadow: 0 0 0 5px #fff0f1; }
  .portal-stat-unready .portal-stat-mark { background: #e1a52b; box-shadow: 0 0 0 5px #fff7e6; }
  .portal-filters { margin-bottom: 16px; }
  .portal-filters .card-body { display: flex; align-items: center; gap: 10px; padding: 14px; }
  .portal-filters .form-control, .portal-filters .form-select { min-height: 42px; border-color: #dce2ec; }
  .portal-filters .form-control:focus, .portal-filters .form-select:focus { border-color: #7fa0fa; box-shadow: 0 0 0 3px rgba(56, 116, 255, .12); }
  .portal-search { flex: 1 1 440px; min-width: 180px; }
  .portal-status-filter { flex: 0 1 220px; min-width: 145px; }
  .portal-table-card { overflow: hidden; }
  .portal-table { min-width: 1050px; }
  .portal-table thead th { padding: 14px 13px; background: #f8f9fc; color: #6f7a91; font-size: .7rem; font-weight: 700; letter-spacing: .055em; text-transform: uppercase; white-space: nowrap; border-bottom-color: #e4e9f1; }
  .portal-table tbody td { padding: 16px 13px; color: #394257; border-bottom-color: #edf0f5; }
  .portal-table .portal-id-heading, .portal-table .portal-id-cell { width: 72px; min-width: 72px; text-align: center; }
  .portal-table tbody tr:last-child td { border-bottom: 0; }
  .portal-table .portal-name { color: #252b3b; font-weight: 700; }
  .portal-table .portal-secondary { display: block; margin-top: 4px; color: #8490a6; font-size: .78rem; }
  .portal-table .portal-id { display: inline-flex; min-width: 34px; height: 30px; align-items: center; justify-content: center; padding: 0 8px; border: 1px solid #e2e8f3; border-radius: 9px; background: #f5f7fc; color: #53627c; font-size: .82rem; font-weight: 700; font-variant-numeric: tabular-nums; }
  .portal-table code { display: inline-block; padding: 4px 7px; border-radius: 6px; background: #f4f5fa; color: #6a5bc7; font-size: .78rem; }
  .portal-table .badge { font-weight: 700; letter-spacing: .025em; }
  .portal-table .plan-until { display: inline-block; margin-left: 5px; color: #8993a7; font-size: .75rem; white-space: nowrap; }
  .portal-table .last-login { color: #6f7a91; font-size: .82rem; white-space: nowrap; }
  .portal-table .manage-btn { min-width: 82px; border-radius: 7px; }
  .portal-pagination { padding: 14px 16px; border-top: 1px solid #edf0f5; }
  .portal-pagination nav { display: flex; justify-content: flex-end; }
  @media (max-width: 575px) {
    .portal-stat-card { min-height: 112px; }
    .portal-stat-card .card-body { padding: 16px; }
    .portal-stat-value { font-size: 1.9rem; }
    .portal-filters .card-body { align-items: stretch; flex-direction: column; }
    .portal-search, .portal-status-filter { flex: 1 1 auto; width: 100%; }
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
