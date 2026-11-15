@php
  $badge = ['active' => 'success', 'grace' => 'warning', 'expired' => 'danger'][$info['state']] ?? 'secondary';
  $sub = $info['subscription'];
@endphp
<hr class="my-5">
<h4 class="mb-3">Subscription &amp; billing</h4>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card h-100"><div class="card-body">
      <h5 class="mb-3">Current plan</h5>
      @if ($sub)
        <div class="fs-6 fw-bold">{{ $sub->package->name }}</div>
        <span class="badge badge-phoenix badge-phoenix-{{ $badge }} mb-3">{{ $info['state'] }}</span>
        <dl class="row mb-0">
          <dt class="col-5">Started</dt><dd class="col-7">{{ $sub->starts_at->format('d M Y') }}</dd>
          <dt class="col-5">Ends</dt><dd class="col-7">{{ $sub->ends_at->format('d M Y') }}</dd>
          @if ($info['state'] === 'active')
            <dt class="col-5">Days left</dt><dd class="col-7">{{ $info['days_left'] }}</dd>
          @elseif ($info['state'] === 'grace')
            <dt class="col-5">Grace left</dt><dd class="col-7 text-danger">{{ $info['grace_days_left'] }} day(s) (till {{ $info['grace_ends_at']->format('d M') }})</dd>
          @elseif ($info['state'] === 'expired')
            <dt class="col-5">Blocked since</dt><dd class="col-7 text-danger">{{ $info['grace_ends_at']->copy()->addDay()->format('d M Y') }}</dd>
          @endif
        </dl>
      @else
        <p class="text-body-tertiary mb-0">No subscription yet. The trial starts automatically on the portal's first login, or activate a package here.</p>
      @endif
    </div></div>
  </div>

  <div class="col-lg-8">
    <div class="card h-100"><div class="card-body">
      <h5 class="mb-3">Record payment &amp; activate / renew</h5>
      <form method="POST" action="{{ route('admin.portals.payments.store', $portal) }}">
        @csrf
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Package</label>
            <select class="form-select" name="package_id" id="pay_package">
              @foreach ($packages as $pkg)
                <option value="{{ $pkg->id }}" data-price="{{ $pkg->price }}" data-trial="{{ $pkg->is_trial ? 1 : 0 }}" @selected($pkg->slug === 'package-1')>
                  {{ $pkg->name }} – {{ number_format($pkg->price) }} tk / {{ $pkg->duration_days }} days
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Amount received (tk)</label>
            <input class="form-control" type="number" step="0.01" min="0" name="amount" id="pay_amount" required>
            @if ($extras > 0)<div class="form-text">Includes {{ number_format($extras) }} tk additional charges</div>@endif
          </div>
          <div class="col-md-2">
            <label class="form-label">Method</label>
            <select class="form-select" name="method">
              @foreach (['cash' => 'Cash', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket', 'bank' => 'Bank', 'other' => 'Other'] as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Trx ID <span class="text-body-tertiary">(optional)</span></label>
            <input class="form-control" name="trx_id">
          </div>
          <div class="col-12">
            <label class="form-label">Note <span class="text-body-tertiary">(optional)</span></label>
            <input class="form-control" name="note">
          </div>
        </div>
        <button class="btn btn-primary mt-3" type="submit">Save payment &amp; activate</button>
        <div class="form-text mt-2">If the current plan is still running, the new period starts the day after it ends.</div>
      </form>
    </div></div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100"><div class="card-body">
      <h5 class="mb-3">Additional charges</h5>
      <form method="POST" action="{{ route('admin.portals.charges.store', $portal) }}" class="row g-2 mb-3">
        @csrf
        <div class="col-md-5"><input class="form-control" name="purpose" placeholder="Purpose (e.g. setup fee)" required></div>
        <div class="col-md-3"><input class="form-control" type="number" min="1" step="0.01" name="amount" placeholder="Amount" required></div>
        <div class="col-md-2">
          <select class="form-select" name="type"><option value="one_time">One time</option><option value="cycle">Every cycle</option></select>
        </div>
        <div class="col-md-2"><button class="btn btn-phoenix-primary w-100" type="submit">Add</button></div>
      </form>
      <table class="table table-sm mb-0">
        <thead><tr><th>Purpose</th><th>Amount</th><th>Type</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($charges as $c)
          <tr>
            <td>{{ $c->purpose }}</td>
            <td>{{ number_format($c->amount) }}</td>
            <td>{{ $c->type === 'cycle' ? 'Every cycle' : 'One time' }}</td>
            <td>
              @if ($c->type === 'one_time' && $c->paid_at)<span class="badge badge-phoenix badge-phoenix-success">paid</span>
              @else<span class="badge badge-phoenix badge-phoenix-warning">due</span>@endif
            </td>
            <td class="text-end">
              <form method="POST" action="{{ route('admin.portals.charges.destroy', [$portal, $c]) }}" onsubmit="return confirm('Remove this charge?')">
                @csrf @method('DELETE')<button class="btn btn-sm btn-link text-danger p-0" type="submit">remove</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-body-tertiary">None</td></tr>
        @endforelse
        </tbody>
      </table>
    </div></div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100"><div class="card-body">
      <h5 class="mb-3">Payment history</h5>
      <table class="table table-sm mb-0">
        <thead><tr><th>Date</th><th>Package</th><th>Amount</th><th>Method</th><th>Trx</th></tr></thead>
        <tbody>
        @forelse ($payments as $pay)
          <tr>
            <td>{{ $pay->paid_at->format('d M Y') }}</td>
            <td>{{ $pay->package?->name ?? '—' }}</td>
            <td>{{ number_format($pay->amount) }}</td>
            <td>{{ ucfirst($pay->method) }}</td>
            <td>{{ $pay->trx_id ?: '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-body-tertiary">No payments yet</td></tr>
        @endforelse
        </tbody>
      </table>
    </div></div>
  </div>
</div>

<script>
  (function () {
    var sel = document.getElementById('pay_package'), amt = document.getElementById('pay_amount');
    var extras = {{ (float) $extras }};
    function fill() {
      var o = sel.options[sel.selectedIndex];
      amt.value = (parseFloat(o.dataset.price) + (o.dataset.trial === '1' ? 0 : extras)).toFixed(0);
    }
    sel.addEventListener('change', fill);
    fill();
  })();
</script>
