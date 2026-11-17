@extends('layouts.admin')
@section('title', $portal->portal_name . ' · Admin')
@section('page_title', 'Portal details')
@section('content')
@php($stateBadge = ['active' => 'success', 'grace' => 'warning', 'expired' => 'danger'][$info['state']] ?? 'secondary')

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
  <div>
    <a href="{{ route('admin.portals.index') }}" class="fs-9">&larr; All portals</a>
    <h3 class="mb-1 mt-1">{{ $portal->portal_name }}</h3>
    <div class="d-flex flex-wrap align-items-center gap-2">
      <span class="badge badge-phoenix badge-phoenix-{{ $portal->isActive() ? 'success' : 'danger' }}">{{ $portal->status }}</span>
      @if ($info['subscription'])
        <span class="badge badge-phoenix badge-phoenix-{{ $stateBadge }}">{{ $info['subscription']->package->name }} · {{ $info['state'] }}</span>
      @endif
      <span class="fs-9 text-body-tertiary">{{ $portal->editor_mobile }} · {{ $portal->database_name ?: 'no database' }}</span>
    </div>
  </div>
  <div class="d-flex flex-wrap gap-2">
    @if ($portal->isProvisioned())
      <form method="POST" action="{{ route('admin.portals.support.start', $portal) }}"
            data-confirm="Start an audited support session? You can submit purchase and grace-period requests, which still require approval.">
        @csrf
        <button class="btn btn-phoenix-primary" type="submit">Log in as admin</button>
      </form>
    @endif
    <form method="POST" action="{{ route('admin.portals.toggle', $portal) }}"
          @if ($portal->isActive()) data-confirm="Suspend this portal? The editor will be blocked from logging in." @endif>
      @csrf
      <button class="btn btn-{{ $portal->isActive() ? 'warning' : 'success' }}" type="submit">{{ $portal->isActive() ? 'Suspend portal' : 'Activate portal' }}</button>
    </form>
  </div>
</div>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item"><a class="nav-link {{ $tab === 'overview' ? 'active' : '' }}" href="{{ route('admin.portals.show', [$portal, 'tab' => 'overview']) }}">Overview</a></li>
  <li class="nav-item"><a class="nav-link {{ $tab === 'billing' ? 'active' : '' }}" href="{{ route('admin.portals.show', [$portal, 'tab' => 'billing']) }}">
    Billing @if ($info['due_months'] > 0)<span class="badge badge-phoenix badge-phoenix-warning ms-1">{{ $info['due_months'] }} due</span>@endif</a></li>
  <li class="nav-item"><a class="nav-link {{ $tab === 'activity' ? 'active' : '' }}" href="{{ route('admin.portals.show', [$portal, 'tab' => 'activity']) }}">
    Activity @if (count($alerts))<span class="badge badge-phoenix badge-phoenix-danger ms-1">{{ count($alerts) }}</span>@endif</a></li>
</ul>

@php($pendingForPortal = $portal->graceRequests()->where('status', 'pending')->count() + $portal->purchaseRequests()->where('status', 'pending')->count())
@if ($pendingForPortal > 0)
  <div class="alert alert-subtle-info d-flex flex-wrap justify-content-between align-items-center gap-2" role="alert">
    <div>This portal has <b>{{ $pendingForPortal }}</b> pending request(s) (grace period / renewal).</div>
    <a class="btn btn-sm btn-phoenix-primary" href="{{ route('admin.requests.index') }}">Review requests</a>
  </div>
@endif

@if ($tab === 'overview')
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
        <dt class="col-5">Database</dt><dd class="col-7">{{ $portal->database_name ?: '—' }}</dd>
        <dt class="col-5">Provisioned</dt><dd class="col-7">{{ $portal->provisioned_at?->format('d M Y H:i') ?? 'No' }}</dd>
        <dt class="col-5">Tables</dt><dd class="col-7">{{ $tableCount ?? '—' }}</dd>
        <dt class="col-5">Created</dt><dd class="col-7">{{ $portal->created_at->format('d M Y H:i') }}</dd>
        <dt class="col-5">Last login</dt><dd class="col-7">{{ $portal->last_login_at?->format('d M Y H:i') ?? 'never' }}</dd>
      </dl>
      @if ($dbError)<div class="alert alert-subtle-warning fs-9">DB connection error: {{ $dbError }}</div>@endif
      @if ($portal->provision_error)<div class="alert alert-subtle-warning fs-9">Last provisioning error: {{ $portal->provision_error }}</div>@endif
      <form method="POST" action="{{ route('admin.portals.provision', $portal) }}">@csrf
        <button class="btn btn-phoenix-secondary" type="submit">Create / repair tenant database</button>
      </form>
    </div></div>

    <div class="card border-danger"><div class="card-body">
      <h5 class="mb-2 text-danger">Danger zone</h5>
      <p class="fs-9 text-body-tertiary">Deleting removes the portal and drops its tenant database. This cannot be undone.</p>
      <form method="POST" action="{{ route('admin.portals.destroy', $portal) }}"
            data-confirm="This permanently deletes the portal AND its database. Continue?">
        @csrf @method('DELETE')
        <button class="btn btn-danger" type="submit">Delete portal &amp; database</button>
      </form>
    </div></div>
  </div>
</div>

@elseif ($tab === 'billing')
  @include('admin.portals._billing')

@else
  @include('admin.partials._alerts', ['alerts' => $alerts, 'portal' => $portal])

  <div class="row g-3 mb-4">
    @foreach ([['Requests', $summary['requests']], ['Errors', $summary['errors']], ['Unique IPs', $summary['ips']], ['DB writes', $summary['writes']], ['Avg response (ms)', $summary['avg_ms']]] as [$label, $value])
      <div class="col-6 col-xl"><div class="card"><div class="card-body py-3">
        <div class="stat-label">{{ $label }}</div><div class="stat-value">{{ number_format($value) }}</div>
      </div></div></div>
    @endforeach
  </div>

  <div class="row g-3 mb-4">
    <div class="col-lg-6"><div class="card h-100"><div class="card-body">
      <h6 class="mb-3">Most used endpoints</h6>
      <table class="table table-sm mb-0">
        <thead><tr><th>Path</th><th>Hits</th><th>Errors</th></tr></thead>
        <tbody>
        @forelse ($summary['top_paths'] as $row)
          <tr><td class="path-cell">{{ $row->path }}</td><td>{{ $row->hits }}</td><td>{{ $row->errors }}</td></tr>
        @empty<tr><td colspan="3" class="text-body-tertiary">No requests in this range.</td></tr>@endforelse
        </tbody>
      </table>
    </div></div></div>
    <div class="col-lg-6"><div class="card h-100"><div class="card-body">
      <h6 class="mb-3">IP addresses</h6>
      <table class="table table-sm mb-0">
        <thead><tr><th>IP</th><th>Hits</th><th>Last seen</th></tr></thead>
        <tbody>
        @forelse ($summary['top_ips'] as $row)
          <tr><td>{{ $row->ip }}</td><td>{{ $row->hits }}</td><td class="fs-9">{{ \Illuminate\Support\Carbon::parse($row->last_seen)->format('d M H:i') }}</td></tr>
        @empty<tr><td colspan="3" class="text-body-tertiary">No requests in this range.</td></tr>@endforelse
        </tbody>
      </table>
    </div></div></div>
  </div>

  @include('admin.partials._activity_filters', ['ranges' => $ranges, 'hidden' => ['tab' => 'activity']])
  @include('admin.partials._activity_table', ['logs' => $logs, 'showPortal' => false])
@endif

<div class="card mt-4">
  <div class="card-header"><h5 class="mb-0">Admin support audit</h5></div>
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead><tr><th>Admin</th><th>Action</th><th>Page</th><th>When</th></tr></thead>
      <tbody>
      @forelse ($supportEvents as $event)
        <tr>
          <td>{{ $event->supportSession->admin_name }}</td>
          <td>{{ ucfirst(str_replace('_', ' ', $event->action)) }}</td>
          <td><code>{{ $event->path ?: '—' }}</code></td>
          <td class="fs-9">{{ $event->created_at?->format('d M Y H:i:s') }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="text-center text-body-tertiary py-4">No admin support sessions yet.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
