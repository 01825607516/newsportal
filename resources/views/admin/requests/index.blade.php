@extends('layouts.admin')
@section('title', 'Requests · Admin')
@section('page_title', 'Requests')
@section('page_subtitle', 'Grace period and renewal requests sent by portals')
@section('content')
@php($methods = config('billing.methods'))

<form method="GET" class="card mb-3" role="search">
  <div class="card-body d-flex flex-column flex-sm-row gap-2">
    <div class="flex-grow-1">
      <label class="visually-hidden" for="request-search">Search pending requests</label>
      <input class="form-control" id="request-search" type="search" name="q" value="{{ $q }}" placeholder="Find pending requests by portal, mobile, or email">
    </div>
    <button class="btn btn-primary px-4" type="submit">Search</button>
    @if ($q)
      <a class="btn btn-phoenix-secondary" href="{{ route('admin.requests.index') }}">Clear</a>
    @endif
  </div>
</form>

<nav class="d-flex flex-wrap gap-2 mb-4" aria-label="Request sections">
  <a class="btn btn-phoenix-secondary" href="#renewal-requests">Renewals <span class="badge badge-phoenix badge-phoenix-warning ms-1">{{ $pendingPurchases->total() }}</span></a>
  <a class="btn btn-phoenix-secondary" href="#grace-requests">Grace periods <span class="badge badge-phoenix badge-phoenix-warning ms-1">{{ $pendingGraces->total() }}</span></a>
  <a class="btn btn-phoenix-secondary" href="#request-history">Recently handled</a>
</nav>

{{-- ================= Renewal / purchase ================= --}}
<div id="renewal-requests" class="d-flex align-items-center gap-2 mb-3">
  <h4 class="mb-0">Renewal requests</h4>
  <span class="badge badge-phoenix badge-phoenix-{{ $pendingPurchases->total() ? 'warning' : 'secondary' }}">{{ $pendingPurchases->total() }} pending</span>
</div>

@forelse ($pendingPurchases as $r)
  <div class="card mb-3"><div class="card-body">
    <div class="row g-4">
      <div class="col-lg-6">
        <a class="fw-bold fs-8" href="{{ route('admin.portals.show', [$r->portal_id, 'tab' => 'billing']) }}">{{ $r->portal->portal_name }}</a>
        <span class="fs-9 text-body-tertiary ms-2">{{ $r->portal->editor_mobile }} · sent {{ $r->created_at->diffForHumans() }}</span>
        <dl class="row mt-3 mb-0 fs-9">
          <dt class="col-4">Plan</dt><dd class="col-8">{{ $r->package->name }} · {{ $r->months }} month(s)</dd>
          <dt class="col-4">Amount asked</dt><dd class="col-8 fw-bold">৳{{ number_format($r->amount, 2) }}</dd>
          <dt class="col-4">Method</dt><dd class="col-8">{{ $methods[$r->method] ?? ucfirst($r->method) }}</dd>
          <dt class="col-4">Trx ID</dt><dd class="col-8">{{ $r->trx_id ?: '—' }}</dd>
          @if ($r->note)<dt class="col-4">Note</dt><dd class="col-8">{{ $r->note }}</dd>@endif
        </dl>
      </div>
      <div class="col-lg-6">
        <form method="POST" action="{{ route('admin.requests.purchase.approve', $r) }}">
          @csrf
          <div class="row g-2">
            <div class="col-sm-5">
              <label class="form-label">Amount received (৳)</label>
              <input class="form-control" type="number" step="0.01" min="0" name="amount" value="{{ $r->amount }}" required>
            </div>
            <div class="col-sm-7">
              <label class="form-label">Note to portal <span class="text-body-tertiary">(optional)</span></label>
              <input class="form-control" name="admin_note" maxlength="255">
            </div>
          </div>
          <div class="d-flex gap-2 mt-3">
            <button class="btn btn-success" type="submit">Approve &amp; record payment</button>
            <button class="btn btn-phoenix-danger" type="submit" formaction="{{ route('admin.requests.purchase.reject', $r) }}"
                    data-confirm="Reject this renewal request?">Reject</button>
          </div>
          <div class="form-text mt-2">Approving records the payment and extends the subscription, exactly like "Record payment" in the Billing tab.</div>
        </form>
      </div>
    </div>
  </div></div>
@empty
  <div class="card mb-3"><div class="card-body text-body-tertiary">{{ $q ? 'No pending renewal requests match your search.' : 'No pending renewal requests.' }}</div></div>
@endforelse
@if ($pendingPurchases->hasPages())
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <span class="small text-body-tertiary">Showing {{ $pendingPurchases->firstItem() }}–{{ $pendingPurchases->lastItem() }} of {{ $pendingPurchases->total() }} renewal requests</span>
    {{ $pendingPurchases->links('pagination::bootstrap-5') }}
  </div>
@endif

{{-- ================= Grace period ================= --}}
<div id="grace-requests" class="d-flex align-items-center gap-2 mb-3 mt-5">
  <h4 class="mb-0">Grace period requests</h4>
  <span class="badge badge-phoenix badge-phoenix-{{ $pendingGraces->total() ? 'warning' : 'secondary' }}">{{ $pendingGraces->total() }} pending</span>
</div>

@forelse ($pendingGraces as $r)
  @php($sub = $r->portal->subscriptions()->orderByDesc('ends_at')->first())
  <div class="card mb-3"><div class="card-body">
    <div class="row g-4">
      <div class="col-lg-6">
        <a class="fw-bold fs-8" href="{{ route('admin.portals.show', [$r->portal_id, 'tab' => 'billing']) }}">{{ $r->portal->portal_name }}</a>
        <span class="fs-9 text-body-tertiary ms-2">{{ $r->portal->editor_mobile }} · sent {{ $r->created_at->diffForHumans() }}</span>
        <dl class="row mt-3 mb-0 fs-9">
          <dt class="col-4">Paid till</dt><dd class="col-8">{{ $sub?->ends_at->format('d M Y') ?? '—' }}</dd>
          <dt class="col-4">Current grace</dt><dd class="col-8">{{ $sub ? $sub->grace_days . ' day(s) · till ' . $sub->ends_at->copy()->addDays($sub->grace_days)->format('d M Y') : '—' }}</dd>
          <dt class="col-4">Wants until</dt><dd class="col-8 fw-bold">{{ $r->until_date->format('d M Y') }}</dd>
          @if ($r->reason)<dt class="col-4">Reason</dt><dd class="col-8">{{ $r->reason }}</dd>@endif
        </dl>
      </div>
      <div class="col-lg-6">
        <form method="POST" action="{{ route('admin.requests.grace.approve', $r) }}">
          @csrf
          <div class="row g-2">
            <div class="col-sm-5">
              <label class="form-label">Grace ends on</label>
              <input class="form-control" type="date" name="until_date" value="{{ $r->until_date->format('Y-m-d') }}" required>
            </div>
            <div class="col-sm-7">
              <label class="form-label">Note to portal <span class="text-body-tertiary">(optional)</span></label>
              <input class="form-control" name="admin_note" maxlength="255">
            </div>
          </div>
          <div class="d-flex gap-2 mt-3">
            <button class="btn btn-success" type="submit">Approve</button>
            <button class="btn btn-phoenix-danger" type="submit" formaction="{{ route('admin.requests.grace.reject', $r) }}"
                    data-confirm="Reject this grace period request?">Reject</button>
          </div>
          <div class="form-text mt-2">You can change the date before approving. The portal can keep working until that day.</div>
        </form>
      </div>
    </div>
  </div></div>
@empty
  <div class="card mb-3"><div class="card-body text-body-tertiary">{{ $q ? 'No pending grace period requests match your search.' : 'No pending grace period requests.' }}</div></div>
@endforelse
@if ($pendingGraces->hasPages())
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <span class="small text-body-tertiary">Showing {{ $pendingGraces->firstItem() }}–{{ $pendingGraces->lastItem() }} of {{ $pendingGraces->total() }} grace period requests</span>
    {{ $pendingGraces->links('pagination::bootstrap-5') }}
  </div>
@endif

{{-- ================= History ================= --}}
<h4 id="request-history" class="mb-3 mt-5">Recently handled</h4>
<div class="row g-4">
  <div class="col-xl-6">
    <div class="card">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0">Renewals</h5>
        <form method="GET" class="d-flex align-items-center gap-2">
          @if ($q)<input type="hidden" name="q" value="{{ $q }}">@endif
          <input type="hidden" name="grace_status" value="{{ $graceStatus }}">
          <label class="visually-hidden" for="renewal-history-status">Filter renewals by status</label>
          <select class="form-select form-select-sm" id="renewal-history-status" name="renewal_status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="approved" @selected($renewalStatus === 'approved')>Approved</option>
            <option value="rejected" @selected($renewalStatus === 'rejected')>Rejected</option>
          </select>
        </form>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead><tr><th>Portal</th><th>Plan</th><th>Amount</th><th>Status</th><th>When</th></tr></thead>
          <tbody>
          @forelse ($historyPurchases as $r)
            <tr>
              <td><a href="{{ route('admin.portals.show', [$r->portal_id, 'tab' => 'billing']) }}">{{ $r->portal->portal_name }}</a></td>
              <td>{{ $r->package->name }} · {{ $r->months }}m</td>
              <td>৳{{ number_format($r->amount) }}</td>
              <td><span class="badge badge-phoenix badge-phoenix-{{ $r->status === 'approved' ? 'success' : 'danger' }}">{{ $r->status }}</span></td>
              <td class="fs-9">{{ $r->decided_at?->diffForHumans() }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-body-tertiary py-4">Nothing yet.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-xl-6">
    <div class="card">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0">Grace periods</h5>
        <form method="GET" class="d-flex align-items-center gap-2">
          @if ($q)<input type="hidden" name="q" value="{{ $q }}">@endif
          <input type="hidden" name="renewal_status" value="{{ $renewalStatus }}">
          <label class="visually-hidden" for="grace-history-status">Filter grace periods by status</label>
          <select class="form-select form-select-sm" id="grace-history-status" name="grace_status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="approved" @selected($graceStatus === 'approved')>Approved</option>
            <option value="rejected" @selected($graceStatus === 'rejected')>Rejected</option>
          </select>
        </form>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead><tr><th>Portal</th><th>Asked until</th><th>Granted until</th><th>Status</th><th>When</th></tr></thead>
          <tbody>
          @forelse ($historyGraces as $r)
            <tr>
              <td><a href="{{ route('admin.portals.show', [$r->portal_id, 'tab' => 'billing']) }}">{{ $r->portal->portal_name }}</a></td>
              <td>{{ $r->until_date->format('d M Y') }}</td>
              <td>{{ $r->approved_until?->format('d M Y') ?? '—' }}</td>
              <td><span class="badge badge-phoenix badge-phoenix-{{ $r->status === 'approved' ? 'success' : 'danger' }}">{{ $r->status }}</span></td>
              <td class="fs-9">{{ $r->decided_at?->diffForHumans() }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-body-tertiary py-4">Nothing yet.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
