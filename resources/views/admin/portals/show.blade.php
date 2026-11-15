@extends('layouts.admin')
@section('title', $portal->portal_name . ' · Admin')
@section('content')
<a href="{{ route('admin.portals.index') }}" class="fs-9">&larr; All portals</a>
<div class="d-flex flex-wrap align-items-center gap-3 my-3">
  <h3 class="mb-0">{{ $portal->portal_name }}</h3>
  <span class="badge badge-phoenix badge-phoenix-{{ $portal->isActive() ? 'success' : 'danger' }}">{{ $portal->status }}</span>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card"><div class="card-body">
      <h5 class="mb-3">Portal information</h5>
      <form method="POST" action="{{ route('admin.portals.update', $portal) }}">
        @csrf @method('PUT')
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Portal name</label>
            <input class="form-control" name="portal_name" value="{{ old('portal_name', $portal->portal_name) }}" required></div>
          <div class="col-md-6"><label class="form-label">Reg. no</label>
            <input class="form-control" name="portal_reg_no" value="{{ old('portal_reg_no', $portal->portal_reg_no) }}"></div>
          <div class="col-md-6"><label class="form-label">Editor name</label>
            <input class="form-control" name="editor_name" value="{{ old('editor_name', $portal->editor_name) }}"></div>
          <div class="col-md-6"><label class="form-label">Editor email</label>
            <input class="form-control" type="email" name="editor_email" value="{{ old('editor_email', $portal->editor_email) }}"></div>
          <div class="col-md-6"><label class="form-label">Editor mobile</label>
            <input class="form-control" name="editor_mobile" value="{{ old('editor_mobile', $portal->editor_mobile) }}" required></div>
        </div>
        <button class="btn btn-primary mt-4" type="submit">Save changes</button>
      </form>
    </div></div>
  </div>

  <div class="col-lg-5">
    <div class="card mb-4"><div class="card-body">
      <h5 class="mb-3">Tenant database</h5>
      <dl class="row mb-3">
        <dt class="col-5">Database</dt><dd class="col-7">{{ $portal->database_name ? $portal->database_name : '—' }}</dd>
        <dt class="col-5">Provisioned</dt><dd class="col-7">{{ $portal->provisioned_at?->format('d M Y H:i') ?? 'No' }}</dd>
        <dt class="col-5">Tables</dt><dd class="col-7">{{ $tableCount ?? '—' }}</dd>
        <dt class="col-5">Created</dt><dd class="col-7">{{ $portal->created_at->format('d M Y H:i') }}</dd>
        <dt class="col-5">Last login</dt><dd class="col-7">{{ $portal->last_login_at?->format('d M Y H:i') ?? 'never' }}</dd>
      </dl>
      @if ($dbError)<div class="alert alert-warning fs-9">DB connection error: {{ $dbError }}</div>@endif
      @if ($portal->provision_error)<div class="alert alert-warning fs-9">Last provisioning error: {{ $portal->provision_error }}</div>@endif
      <form method="POST" action="{{ route('admin.portals.provision', $portal) }}">@csrf
        <button class="btn btn-phoenix-secondary" type="submit">Create / repair tenant database</button>
      </form>
    </div></div>

    <div class="card border-danger"><div class="card-body">
      <h5 class="mb-3">Access control</h5>
      <form method="POST" action="{{ route('admin.portals.toggle', $portal) }}" class="mb-3">@csrf
        <button class="btn btn-{{ $portal->isActive() ? 'warning' : 'success' }}" type="submit">
          {{ $portal->isActive() ? 'Suspend portal (block login)' : 'Activate portal' }}
        </button>
      </form>
      <form method="POST" action="{{ route('admin.portals.destroy', $portal) }}"
            onsubmit="return confirm('This permanently deletes the portal AND its database. Continue?')">
        @csrf @method('DELETE')
        <button class="btn btn-danger" type="submit">Delete portal &amp; database</button>
      </form>
    </div></div>
  </div>
</div>
@include('admin.portals._billing')
@endsection
