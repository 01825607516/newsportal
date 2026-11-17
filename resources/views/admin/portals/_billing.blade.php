@php
  $badge = ['active' => 'success', 'grace' => 'warning', 'expired' => 'danger'][$info['state']] ?? 'secondary';
  $sub = $info['subscription'];
  $defaultId = $sub?->package_id ?? optional($packages->firstWhere('slug', 'package-1'))->id;
@endphp

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card h-100"><div class="card-body">
      <h5 class="mb-3">Current plan</h5>
      @if ($sub)
        <div class="fs-6 fw-bold">{{ $sub->package->name }}</div>
        <span class="badge badge-phoenix badge-phoenix-{{ $badge }} mb-3">{{ $info['state'] }}</span>
        <dl class="row mb-0">
          <dt class="col-5">Term started</dt><dd class="col-7">{{ $sub->starts_at->format('d M Y') }}</dd>
          <dt class="col-5">Paid till</dt><dd class="col-7">{{ $sub->ends_at->format('d M Y') }}</dd>
          @if ($sub->term_months > 1)
            <dt class="col-5">Months paid</dt><dd class="col-7">{{ $sub->paid_months }} / {{ $sub->term_months }}</dd>
          @endif
          @if ($info['state'] === 'active')
            <dt class="col-5">Days left</dt><dd class="col-7">{{ $info['days_left'] }}</dd>
          @elseif ($info['state'] === 'grace')
            <dt class="col-5">Grace left</dt><dd class="col-7 text-danger">{{ $info['grace_days_left'] }} day(s) (till {{ $info['grace_ends_at']->format('d M') }})</dd>
          @elseif ($info['state'] === 'expired')
            <dt class="col-5">Blocked since</dt><dd class="col-7 text-danger">{{ $info['grace_ends_at']->copy()->addDay()->format('d M Y') }}</dd>
          @endif
          @if ($info['due_months'] > 0)
            <dt class="col-5">Dues</dt>
            <dd class="col-7 text-danger fw-bold">{{ $info['due_months'] }} month(s) = {{ number_format($info['due_months'] * $sub->price) }} tk</dd>
          @endif
        </dl>
      @else
        <p class="text-body-tertiary mb-0">No subscription yet. The trial starts automatically on the portal's first login, or record a payment here.</p>
      @endif
    </div></div>
  </div>

  <div class="col-lg-8">
    <div class="card h-100"><div class="card-body">
      <h5 class="mb-3">Record payment</h5>
      <form method="POST" action="{{ route('admin.portals.payments.store', $portal) }}">
        @csrf
        <div class="row g-3">
          <div class="col-md-5">
            <label class="form-label">Package</label>
            <select class="form-select" name="package_id" id="pay_package">
              @foreach ($packages as $pkg)
                @php($qt = $quotes[$pkg->id])
                <option value="{{ $pkg->id }}" data-price="{{ $pkg->price }}" data-trial="{{ $pkg->is_trial ? 1 : 0 }}"
                        data-due="{{ $qt['due'] }}" data-remaining="{{ $qt['remaining'] }}" @selected($pkg->id === $defaultId)>
                  {{ $pkg->name }} – {{ number_format($pkg->price) }} tk/{{ $pkg->is_trial ? $pkg->duration_days . ' days' : 'month' }}{{ $pkg->term_months > 1 ? ' · ' . $pkg->term_months . ' month term' : '' }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Advance months</label>
            <input class="form-control" type="number" min="0" value="{{ old('advance_months', 0) }}" name="advance_months" id="pay_advance">
            <div class="form-text" id="pay_hint"></div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Amount received (tk)</label>
            <input class="form-control" type="number" step="0.01" min="0" name="amount" id="pay_amount" required>
            @if ($extras['one_time'] > 0 || $extras['cycle'] > 0)
              <div class="form-text">Includes additional charges ({{ number_format($extras['one_time']) }} one-time + {{ number_format($extras['cycle']) }}/month)</div>
            @endif
          </div>
          <div class="col-md-3">
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
          <div class="col-md-6">
            <label class="form-label">Note <span class="text-body-tertiary">(optional)</span></label>
            <input class="form-control" name="note">
          </div>
        </div>
        <button class="btn btn-primary mt-3" type="submit">Save payment</button>
        <div class="form-text mt-2">
          Any skipped month is already counted in "due" and is paid together with this month. Early payment extends the paid period without losing days.
        </div>
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
          <select class="form-select" name="type"><option value="one_time">One time</option><option value="cycle">Every month</option></select>
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
            <td>{{ $c->type === 'cycle' ? 'Every month' : 'One time' }}</td>
            <td>
              @if ($c->type === 'one_time' && $c->paid_at)<span class="badge badge-phoenix badge-phoenix-success">paid</span>
              @else<span class="badge badge-phoenix badge-phoenix-warning">due</span>@endif
            </td>
            <td class="text-end">
              <form method="POST" action="{{ route('admin.portals.charges.destroy', [$portal, $c]) }}" data-confirm="Remove this charge?">
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
        <thead><tr><th>Date</th><th>Package</th><th>Months</th><th>Amount</th><th>Method</th><th>Trx</th></tr></thead>
        <tbody>
        @forelse ($payments as $pay)
          <tr>
            <td>{{ $pay->paid_at->format('d M Y') }}</td>
            <td>{{ $pay->package?->name ?? '—' }}</td>
            <td>{{ $pay->months }}</td>
            <td>{{ number_format($pay->amount) }}</td>
            <td>{{ ucfirst($pay->method) }}</td>
            <td>{{ $pay->trx_id ?: '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-body-tertiary">No payments yet</td></tr>
        @endforelse
        </tbody>
      </table>
    </div></div>
  </div>
</div>

<script>
  (function () {
    var sel = document.getElementById('pay_package'),
        adv = document.getElementById('pay_advance'),
        amt = document.getElementById('pay_amount'),
        hint = document.getElementById('pay_hint');
    var oneTime = {{ (float) $extras['one_time'] }}, cycle = {{ (float) $extras['cycle'] }};

    function fill() {
      var o = sel.options[sel.selectedIndex];
      var due = parseInt(o.dataset.due, 10), remaining = parseInt(o.dataset.remaining, 10);
      var price = parseFloat(o.dataset.price), trial = o.dataset.trial === '1';
      var maxAdv = Math.max(0, remaining - due);
      adv.max = maxAdv;
      if ((parseInt(adv.value, 10) || 0) > maxAdv) adv.value = maxAdv;
      var months = due + (parseInt(adv.value, 10) || 0);
      var total = price * months + (trial ? 0 : oneTime + cycle * months);
      amt.value = Math.round(total);
      hint.textContent = 'Due: ' + due + ' month(s) · paying ' + months + ' · max advance ' + maxAdv;
    }

    sel.addEventListener('change', function () { adv.value = 0; fill(); });
    adv.addEventListener('input', fill);
    fill();
  })();
</script>
