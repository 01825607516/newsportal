{{-- vars: $ranges, $hidden (array of hidden inputs), $portals (optional) --}}
<form method="GET" class="row g-2 mb-3 align-items-end">
  @foreach (($hidden ?? []) as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
  @isset($portals)
    <div class="col-md-3">
      <select class="form-select" name="portal_id">
        <option value="">All portals</option>
        @foreach ($portals as $p)<option value="{{ $p->id }}" @selected((string) request('portal_id') === (string) $p->id)>{{ $p->portal_name }}</option>@endforeach
      </select>
    </div>
  @endisset
  <div class="col-md-2">
    <select class="form-select" name="range">
      @foreach ($ranges as $k => $label)<option value="{{ $k }}" @selected(request('range', '24h') === $k)>{{ $label }}</option>@endforeach
    </select>
  </div>
  <div class="col-md-2">
    <select class="form-select" name="event">
      <option value="">All events</option>
      <option value="request" @selected(request('event') === 'request')>Requests</option>
      <option value="login" @selected(request('event') === 'login')>Logins</option>
      <option value="login_blocked" @selected(request('event') === 'login_blocked')>Blocked logins</option>
    </select>
  </div>
  <div class="col-md-2">
    <select class="form-select" name="status">
      <option value="">Any status</option>
      <option value="error" @selected(request('status') === 'error')>Errors (4xx + 5xx)</option>
      <option value="2xx" @selected(request('status') === '2xx')>2xx</option>
      <option value="3xx" @selected(request('status') === '3xx')>3xx</option>
      <option value="4xx" @selected(request('status') === '4xx')>4xx</option>
      <option value="5xx" @selected(request('status') === '5xx')>5xx</option>
    </select>
  </div>
  <div class="col-md-2"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Path or IP"></div>
  <div class="col-auto"><button class="btn btn-primary" type="submit">Filter</button></div>
</form>
