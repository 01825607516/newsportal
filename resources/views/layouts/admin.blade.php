@extends('layouts.base')
@section('body')
<style>
  .adm { display: flex; min-height: 100vh; background: #f5f7fb; color: #252b3b; }
  .adm-side { width: 252px; flex: 0 0 252px; background: #fff; border-right: 1px solid #e6eaf1;
              display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; }
  .adm-brand { display: block; padding: 22px 24px; font-weight: 800; font-size: 1.12rem; line-height: 1.2;
               color: #252b3b; border-bottom: 1px solid #e6eaf1; text-decoration: none; }
  .adm-brand small { display: block; margin-top: 5px; font-weight: 700; font-size: .66rem; letter-spacing: .12em; text-transform: uppercase; color: #8992a8; }
  .adm-nav { padding: 18px 12px; flex: 1; }
  .adm-nav a { display: flex; justify-content: space-between; align-items: center; padding: 11px 14px; margin-bottom: 5px; border-radius: 9px;
               font-weight: 600; color: #59637a; text-decoration: none; transition: background .16s ease, color .16s ease; }
  .adm-nav a:hover { background: #f3f6fb; color: #252b3b; }
  .adm-nav a.active { background: #eaf0ff; color: #3167e3; }
  .adm-user { padding: 16px 20px; border-top: 1px solid #e6eaf1; }
  .adm-main { flex: 1; min-width: 0; }
  .adm-top { display: flex; justify-content: space-between; align-items: center; gap: 12px; min-height: 66px; padding: 16px 32px;
             background: #fff; border-bottom: 1px solid #e6eaf1; }
  .adm-top h1 { font-size: 1.3rem; font-weight: 750; margin: 0; color: #252b3b; }
  .adm-content { padding: 30px 32px; }
  .adm .card { border: 1px solid #e4e9f1; border-radius: 12px; box-shadow: 0 2px 7px rgba(37, 43, 59, .025); }
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
  .adm .pagination { margin: 0; }
  .adm .path-cell { max-width: 340px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  @media (max-width: 991px) {
    .adm { flex-direction: column; }
    .adm-side { position: static; width: 100%; height: auto; flex: none; }
    .adm-nav { display: flex; flex-wrap: wrap; gap: 6px; padding: 10px 12px; }
    .adm-nav a { margin: 0; }
    .adm-top, .adm-content { padding-left: 16px; padding-right: 16px; }
  }
  @media (max-width: 575px) {
    .adm-nav { flex-wrap: nowrap; overflow-x: auto; }
    .adm-nav a { flex: 0 0 auto; }
    .adm-content { padding-top: 20px; padding-bottom: 20px; }
    .portal-stat-card { min-height: 112px; }
    .portal-stat-card .card-body { padding: 16px; }
    .portal-stat-value { font-size: 1.9rem; }
    .portal-filters .card-body { align-items: stretch; flex-direction: column; }
    .portal-search, .portal-status-filter { flex: 1 1 auto; width: 100%; }
  }
</style>

<div class="adm">
  <aside class="adm-side">
    <a class="adm-brand" href="{{ route('admin.dashboard') }}">News Portal<small>Admin panel</small></a>
    <nav class="adm-nav">
      <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
      <a href="{{ route('admin.portals.index') }}" class="{{ request()->routeIs('admin.portals.*') ? 'active' : '' }}">Portals</a>
      <a href="{{ route('admin.activity.index') }}" class="{{ request()->routeIs('admin.activity.*') ? 'active' : '' }}">Activity monitor</a>
      <a href="{{ route('admin.packages.index') }}" class="{{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">Packages</a>
    </nav>
    <div class="adm-user">
      <div class="fw-semibold">{{ $currentAdmin->name }}</div>
      <div class="fs-9 text-body-tertiary mb-2">{{ $currentAdmin->email }}</div>
      <form method="POST" action="{{ route('admin.logout') }}">@csrf
        <button class="btn btn-sm btn-phoenix-secondary w-100" type="submit">Logout</button>
      </form>
    </div>
  </aside>

  <div class="adm-main">
    <header class="adm-top">
      <h1>@yield('page_title', 'Admin')</h1>
      <div>@yield('page_actions')</div>
    </header>
    <div class="adm-content">
      @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      @if ($errors->any())<div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
      @yield('content')
    </div>
  </div>
</div>
@endsection
