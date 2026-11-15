@extends('layouts.admin')
@section('title', 'Portals · Admin')
@section('page_title', 'Portals')
@section('content')
<div class="row g-3 mb-4">
  @foreach ([['Total portals', $stats['total']], ['Active', $stats['active']], ['Suspended', $stats['suspended']], ['DB not ready', $stats['unprovisioned']]] as [$label, $value])
    <div class="col-6 col-lg-3">
      <div class="card"><div class="card-body">
        <div class="text-body-tertiary fs-9">{{ $label }}</div>
        <div class="fs-3 fw-bold">{{ $value }}</div>
      </div></div>
    </div>
  @endforeach
</div>

<form class="row g-2 mb-3" method="GET">
  <div class="col-md-5"><input class="form-control" name="q" value="{{ $q }}" placeholder="Search portal, editor, email, mobile, reg no"></div>
  <div class="col-md-2">
    <select class="form-select" name="status">
      <option value="">All status</option>
      <option value="active" @selected($status === 'active')>Active</option>
      <option value="suspended" @selected($status === 'suspended')>Suspended</option>
    </select>
  </div>
  <div class="col-auto"><button class="btn btn-primary" type="submit">Filter</button></div>
</form>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0 align-middle">
      <thead><tr>
        <th>#</th><th>Portal</th><th>Editor</th><th>Mobile</th><th>Database</th><th>Plan</th><th>Status</th><th>Last login</th><th></th>
      </tr></thead>
      <tbody>
      @forelse ($portals as $p)
        <tr>
          <td>{{ $p->id }}</td>
          <td><div class="fw-semibold">{{ $p->portal_name }}</div><div class="fs-9 text-body-tertiary">{{ $p->portal_reg_no }}</div></td>
          <td><div>{{ $p->editor_name ?: '—' }}</div><div class="fs-9 text-body-tertiary">{{ $p->editor_email }}</div></td>
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
              <span class="fs-9 text-body-tertiary">till {{ $sub['subscription']->ends_at->format('d M') }}</span>
            @else<span class="text-body-tertiary">—</span>@endif
          </td>
          <td><span class="badge badge-phoenix badge-phoenix-{{ $p->isActive() ? 'success' : 'danger' }}">{{ $p->status }}</span></td>
          <td class="fs-9">{{ $p->last_login_at?->diffForHumans() ?? 'never' }}</td>
          <td class="text-end"><a class="btn btn-sm btn-phoenix-primary" href="{{ route('admin.portals.show', $p) }}">Manage</a></td>
        </tr>
      @empty
        <tr><td colspan="9" class="text-center text-body-tertiary py-5">No portals found.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
<div class="mt-3">{{ $portals->links('pagination::bootstrap-5') }}</div>
@endsection
