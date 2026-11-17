{{-- vars: $alerts, $portal --}}
@if (count($alerts))
  <div class="alert alert-subtle-{{ collect($alerts)->contains('level', 'danger') ? 'danger' : 'warning' }} mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
      <div>
        <div class="fw-bold mb-1">Suspicious activity detected</div>
        @foreach ($alerts as $a)<div>• {{ $a['message'] }}</div>@endforeach
      </div>
      @if ($portal->isActive())
        <form method="POST" action="{{ route('admin.portals.toggle', $portal) }}" data-confirm="Suspend {{ $portal->portal_name }}? The editor will be blocked from logging in.">
          @csrf<button class="btn btn-danger" type="submit">Suspend portal</button>
        </form>
      @endif
    </div>
  </div>
@else
  <div class="alert alert-subtle-success mb-3">No suspicious patterns detected in the last hour.</div>
@endif
