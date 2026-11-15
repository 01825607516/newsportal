@extends('layouts.admin')
@section('title', 'Dashboard · Admin')
@section('page_title', 'Dashboard')
@section('content')
<div class="row g-3 mb-4">
  @foreach ([['Total portals', $stats['total']], ['Active', $stats['active']], ['Suspended', $stats['suspended']], ['Requests (24h)', $stats['requests24']]] as [$label, $value])
    <div class="col-6 col-xl-3"><div class="card"><div class="card-body">
      <div class="stat-label">{{ $label }}</div><div class="stat-value">{{ number_format($value) }}</div>
    </div></div></div>
  @endforeach
</div>

<div class="card mb-4"><div class="card-body">
  <h5 class="mb-3">Needs attention</h5>
  @forelse ($flagged as $f)
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 py-3 border-top">
      <div>
        <a class="fw-bold" href="{{ route('admin.portals.show', [$f['portal'], 'tab' => 'activity']) }}">{{ $f['portal']->portal_name }}</a>
        @foreach ($f['alerts'] as $a)
          <div class="fs-9 text-{{ $a['level'] === 'danger' ? 'danger' : 'warning-dark' }}">• {{ $a['message'] }}</div>
        @endforeach
      </div>
      <div class="d-flex gap-2">
        <a class="btn btn-sm btn-phoenix-secondary" href="{{ route('admin.portals.show', [$f['portal'], 'tab' => 'activity']) }}">Review</a>
        <form method="POST" action="{{ route('admin.portals.toggle', $f['portal']) }}" onsubmit="return confirm('Suspend this portal?')">
          @csrf<button class="btn btn-sm btn-danger" type="submit">Suspend</button>
        </form>
      </div>
    </div>
  @empty
    <div class="text-body-tertiary">Nothing suspicious right now.</div>
  @endforelse
</div></div>

<div class="row g-4">
  <div class="col-xl-8">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="mb-0">Latest activity</h5><a class="fs-9" href="{{ route('admin.activity.index') }}">View all</a>
    </div>
    @include('admin.partials._activity_table', ['logs' => new \Illuminate\Pagination\LengthAwarePaginator($recent, $recent->count(), max(1, $recent->count())), 'showPortal' => true])
  </div>
  <div class="col-xl-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="mb-0">New portals</h5><a class="fs-9" href="{{ route('admin.portals.index') }}">View all</a>
    </div>
    <div class="card"><ul class="list-group list-group-flush">
      @forelse ($recentPortals as $p)
        <li class="list-group-item d-flex justify-content-between">
          <a href="{{ route('admin.portals.show', $p) }}">{{ $p->portal_name }}</a>
          <span class="fs-9 text-body-tertiary">{{ $p->created_at->diffForHumans() }}</span>
        </li>
      @empty
        <li class="list-group-item text-body-tertiary">No portals yet.</li>
      @endforelse
    </ul></div>
  </div>
</div>
@endsection
