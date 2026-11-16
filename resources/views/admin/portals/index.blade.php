@extends('layouts.admin')
@section('title', 'Portals | Admin')
@section('page_title', 'Portals')
@section('content')
<div class="row g-3 mb-4">
  @foreach ([['Total portals', $stats['total'], 'total'], ['Active', $stats['active'], 'active'], ['Suspended', $stats['suspended'], 'suspended'], ['DB not ready', $stats['unprovisioned'], 'unready']] as [$label, $value, $tone])
    <div class="col-6 col-xl-3">
      <div class="card portal-stat-card portal-stat-{{ $tone }}"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <span class="portal-stat-label">{{ $label }}</span><span class="portal-stat-mark" aria-hidden="true"></span>
        </div>
        <div class="portal-stat-value">{{ $value }}</div>
      </div></div>
    </div>
  @endforeach
</div>

<form class="card portal-filters" method="GET" role="search">
  <div class="card-body">
    <div class="portal-search">
      <label class="visually-hidden" for="portal-search">Search portals</label>
      <input class="form-control" id="portal-search" name="q" value="{{ $q }}" placeholder="Search by portal, editor, email, mobile, or reg. no.">
    </div>
    <div class="portal-status-filter">
      <label class="visually-hidden" for="portal-status">Filter by status</label>
      <select class="form-select" id="portal-status" name="status">
        <option value="">All statuses</option>
        <option value="active" @selected($status === 'active')>Active</option>
        <option value="suspended" @selected($status === 'suspended')>Suspended</option>
      </select>
    </div>
    <button class="btn btn-primary px-4" type="submit">Filter</button>
    @if ($q || $status)
      <a class="btn btn-phoenix-secondary" href="{{ route('admin.portals.index') }}">Clear</a>
    @endif
  </div>
</form>

<div class="card portal-table-card">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle portal-table">
      <thead><tr>
        <th class="portal-id-heading">ID</th><th>Portal</th><th>Editor</th><th>Mobile</th><th>Database</th><th>Plan</th><th>Status</th><th>Last login</th><th></th>
      </tr></thead>
      <tbody>
      @forelse ($portals as $p)
        <tr>
          <td class="portal-id-cell"><span class="portal-id" aria-label="Portal ID {{ $p->id }}">{{ $p->id }}</span></td>
          <td><div class="portal-name">{{ $p->portal_name }}</div>@if ($p->portal_reg_no)<span class="portal-secondary">Reg. no. {{ $p->portal_reg_no }}</span>@endif</td>
          <td><div class="fw-semibold">{{ $p->editor_name ?: 'Not provided' }}</div>@if ($p->editor_email)<span class="portal-secondary">{{ $p->editor_email }}</span>@endif</td>
          <td>{{ $p->editor_mobile }}</td>
          <td>
            @if ($p->isProvisioned())<code>{{ $p->database_name }}</code>
            @else<span class="badge badge-phoenix badge-phoenix-warning">not ready</span>@endif
          </td>
          @php($sub = app(\App\Services\SubscriptionService::class)->info($p))
          <td>
            @if ($sub['subscription'])
              <div>{{ $sub['subscription']->package->name }}</div>
              <span class="badge badge-phoenix badge-phoenix-{{ ['active' => 'success', 'grace' => 'warning', 'expired' => 'danger'][$sub['state']] ?? 'secondary' }}">{{ $sub['state'] }}</span>
              <span class="plan-until">until {{ $sub['subscription']->ends_at->format('d M Y') }}</span>
            @else<span class="text-body-tertiary">No plan</span>@endif
          </td>
          <td><span class="badge badge-phoenix badge-phoenix-{{ $p->isActive() ? 'success' : 'danger' }}">{{ ucfirst($p->status) }}</span></td>
          <td class="last-login">{{ $p->last_login_at?->diffForHumans() ?? 'Never' }}</td>
          <td class="text-end"><a class="btn btn-sm btn-phoenix-primary manage-btn" href="{{ route('admin.portals.show', $p) }}">Manage</a></td>
        </tr>
      @empty
        <tr><td colspan="9" class="text-center text-body-tertiary py-5">No portals match these filters.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  @if ($portals->hasPages())
    <div class="portal-pagination">{{ $portals->links('pagination::bootstrap-5') }}</div>
  @endif
</div>
@endsection
