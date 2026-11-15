@extends('layouts.base')
@section('body')
<nav class="navbar bg-body-emphasis border-bottom px-4 py-3">
  <a class="navbar-brand fw-bold text-body-emphasis" href="{{ route('admin.portals.index') }}">News Portal · Admin</a>
  <div class="d-flex align-items-center gap-3">
    <span class="text-body-tertiary fs-9">{{ $currentAdmin->name }}</span>
    <form method="POST" action="{{ route('admin.logout') }}">@csrf
      <button class="btn btn-sm btn-phoenix-secondary" type="submit">Logout</button>
    </form>
  </div>
</nav>
<main class="container-fluid px-4 py-4">
  @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if ($errors->any())<div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
  @yield('content')
</main>
@endsection
