@extends('layouts.base')
@section('body')
<style>
  .adm { display: flex; min-height: 100vh; background: var(--phoenix-body-bg, #f5f7fa); }
  .adm-side { width: 240px; flex: 0 0 240px; background: var(--phoenix-emphasis-bg, #fff); border-right: 1px solid var(--phoenix-border-color, #e3e6ed);
              display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; }
  .adm-brand { display: block; padding: 20px 24px; font-weight: 800; font-size: 1.15rem; line-height: 1.2;
               color: var(--phoenix-emphasis-color, #31374a); border-bottom: 1px solid var(--phoenix-border-color, #e3e6ed); }
  .adm-brand small { display: block; font-weight: 700; font-size: .68rem; letter-spacing: .1em; text-transform: uppercase; color: var(--phoenix-tertiary-color, #9fa6bc); }
  .adm-nav { padding: 14px 12px; flex: 1; }
  .adm-nav a { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; margin-bottom: 2px; border-radius: 8px;
               font-weight: 600; color: var(--phoenix-body-color, #525b75); text-decoration: none; }
  .adm-nav a:hover { background: var(--phoenix-secondary-bg, #eff2f6); }
  .adm-nav a.active { background: var(--phoenix-primary-bg-subtle, #e5edff); color: var(--phoenix-primary, #3874ff); }
  .adm-user { padding: 16px 20px; border-top: 1px solid var(--phoenix-border-color, #e3e6ed); }
  .adm-main { flex: 1; min-width: 0; }
  .adm-top { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 18px 32px;
             background: var(--phoenix-emphasis-bg, #fff); border-bottom: 1px solid var(--phoenix-border-color, #e3e6ed); }
  .adm-top h1 { font-size: 1.35rem; margin: 0; }
  .adm-content { padding: 28px 32px; }
  .adm .card { box-shadow: none; }
  .adm .stat-label { font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; color: var(--phoenix-tertiary-color, #9fa6bc); font-weight: 700; }
  .adm .stat-value { font-size: 1.7rem; font-weight: 800; line-height: 1.2; }
  .adm .path-cell { max-width: 340px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  @media (max-width: 991px) {
    .adm { flex-direction: column; }
    .adm-side { position: static; width: 100%; height: auto; flex: none; }
    .adm-nav { display: flex; flex-wrap: wrap; gap: 6px; padding: 10px 12px; }
    .adm-nav a { margin: 0; }
    .adm-top, .adm-content { padding-left: 16px; padding-right: 16px; }
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
