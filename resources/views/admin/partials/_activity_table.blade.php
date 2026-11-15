{{-- vars: $logs (paginator), $showPortal (bool) --}}
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover table-sm mb-0 align-middle">
      <thead><tr>
        <th>Time</th>@if ($showPortal)<th>Portal</th>@endif
        <th>Method</th><th>Path</th><th>Status</th><th>IP</th><th title="Tenant DB queries / writes">DB q / w</th><th>ms</th>
      </tr></thead>
      <tbody>
      @forelse ($logs as $log)
        @php($sc = $log->status >= 500 ? 'danger' : ($log->status >= 400 ? 'warning' : ($log->status >= 300 ? 'info' : 'success')))
        <tr title="{{ $log->user_agent }}">
          <td class="text-nowrap fs-9">{{ $log->created_at?->format('d M H:i:s') }}</td>
          @if ($showPortal)
            <td><a href="{{ route('admin.portals.show', [$log->portal_id, 'tab' => 'activity']) }}">{{ $log->portal?->portal_name ?? '#' . $log->portal_id }}</a></td>
          @endif
          <td><code>{{ $log->method }}</code></td>
          <td class="path-cell">
            @if ($log->event === 'login')<span class="badge badge-phoenix badge-phoenix-primary">login</span>
            @elseif ($log->event === 'login_blocked')<span class="badge badge-phoenix badge-phoenix-danger">login blocked</span>
            @endif
            {{ $log->path }}
          </td>
          <td><span class="badge badge-phoenix badge-phoenix-{{ $sc }}">{{ $log->status }}</span></td>
          <td class="fs-9">{{ $log->ip }}</td>
          <td class="fs-9">{{ $log->db_queries }} / <span class="{{ $log->db_writes > 0 ? 'fw-bold' : '' }}">{{ $log->db_writes }}</span></td>
          <td class="fs-9">{{ $log->duration_ms }}</td>
        </tr>
      @empty
        <tr><td colspan="{{ $showPortal ? 8 : 7 }}" class="text-center text-body-tertiary py-5">No activity found for this filter.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
<div class="mt-3">{{ $logs->links('pagination::bootstrap-5') }}</div>
