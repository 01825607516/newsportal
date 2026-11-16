@extends('layouts.admin')
@section('title', 'Requests · Admin')
@section('page_title', 'Requests')
@section('page_subtitle', 'Grace period and renewal requests sent by portals')
@section('content')
@php($methods = config('billing.methods'))

{{-- ================= Renewal / purchase ================= --}}
<div class="d-flex align-items-center gap-2 mb-3">
  <h4 class="mb-0">Renewal requests</h4>
  <span class="badge badge-phoenix badge-phoenix-{{ $pendingPurchases->count() ? 'warning' : 'secondary' }}">{{ $pendingPurchases->count() }} pending</span>
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
                    onclick="return confirm('Reject this renewal request?')">Reject</button>
          </div>
          <div class="form-text mt-2">Approving records the payment and extends the subscription, exactly like "Record payment" in the Billing tab.</div>
        </form>
      </div>
    </div>
  </div></div>
@empty
  <div class="card mb-3"><div class="card-body text-body-tertiary">No pending renewal requests.</div></div>
@endforelse

{{-- ================= Grace period ================= --}}
<div class="d-flex align-items-center gap-2 mb-3 mt-5">
  <h4 class="mb-0">Grace period requests</h4>
  <span class="badge badge-phoenix badge-phoenix-{{ $pendingGraces->count() ? 'warning' : 'secondary' }}">{{ $pendingGraces->count() }} pending</span>
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
                    onclick="return confirm('Reject this grace period request?')">Reject</button>
          </div>
          <div class="form-text mt-2">You can change the date before approving. The portal can keep working until that day.</div>
        </form>
      </div>
    </div>
  </div></div>
@empty
  <div class="card mb-3"><div class="card-body text-body-tertiary">No pending grace period requests.</div></div>
@endforelse

{{-- ================= History ================= --}}
<h4 class="mb-3 mt-5">Recently handled</h4>
<div class="row g-4">
  <div class="col-xl-6">
    <div class="card">
      <div class="card-header"><h5 class="mb-0">Renewals</h5></div>
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
      <div class="card-header"><h5 class="mb-0">Grace periods</h5></div>
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
