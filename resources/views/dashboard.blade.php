<!DOCTYPE html>
<html lang="en-US" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Phoenix Project Management</title>
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicons/favicon-32x32.png') }}">
    <script src="{{ asset('vendors/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <link href="{{ asset('vendors/choices/choices.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/dhtmlx-gantt/dhtmlxgantt.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/flatpickr/flatpickr.min.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&amp;display=swap" rel="stylesheet">
    <link href="{{ asset('vendors/simplebar/simplebar.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/theme-rtl.min.css') }}" rel="stylesheet" id="style-rtl">
    <link href="{{ asset('assets/css/theme.min.css') }}" rel="stylesheet" id="style-default">
    <link href="{{ asset('assets/css/user-rtl.min.css') }}" rel="stylesheet" id="user-style-rtl">
    <link href="{{ asset('assets/css/user.min.css') }}" rel="stylesheet" id="user-style-default">
    <script>
      var phoenixIsRTL = window.config.config.phoenixIsRTL;
      if (phoenixIsRTL) {
        document.getElementById('style-default').disabled = true;
        document.getElementById('user-style-default').disabled = true;
        document.documentElement.setAttribute('dir', 'rtl');
      } else {
        document.getElementById('style-rtl').disabled = true;
        document.getElementById('user-style-rtl').disabled = true;
      }
    </script>
    @vite('v1.23.0/src/project-management.js')
</head>
<body>
@php
    $money = fn ($n) => '৳' . rtrim(rtrim(number_format($n, 2, '.', ','), '0'), '.');
    $b = $billing ?? null;
    $methods = config('billing.methods');
    $reopen = old('_form');
@endphp
<style>
    .sub-toast { position: fixed; top: 5rem; left: 50%; transform: translateX(-50%); z-index: 2100; width: min(34rem, 92vw); }
    .plan-card { cursor: pointer; display: block; border: 1px solid var(--phoenix-border-color); border-radius: .5rem; padding: .75rem 1rem; margin-bottom: .6rem; }
    .plan-card:hover { border-color: var(--phoenix-primary); }
    .plan-card input:checked ~ .plan-body { color: var(--phoenix-primary); }
    .plan-card:has(input:checked) { border-color: var(--phoenix-primary); background: var(--phoenix-primary-bg-subtle); }
    .plan-price { font-size: 1.15rem; font-weight: 800; }
    .plan-price small { font-weight: 400; font-size: .8rem; color: var(--phoenix-tertiary-color); }
</style>

{{-- These two blocks are moved into the Phoenix top navbar by the script at the bottom. --}}
<div id="sub-nav-store" style="display:none"
     data-portal-name="{{ $currentPortal->portal_name ?? '' }}"
     data-portal-sub="{{ trim(($currentPortal->editor_name ?? '') . ' ' . (($currentPortal->editor_name ?? '') ? '· ' : '') . ($currentPortal->editor_mobile ?? '')) }}">
    @php
        $subState = $subscriptionInfo['state'] ?? null;
        $subDays = $subscriptionInfo['days_left'] ?? null;
        $subTone = $subState === 'grace' ? 'danger' : (($subDays !== null && $subDays <= (int) config('billing.warn_days')) ? 'warning' : 'primary');
    @endphp
    <div id="sub-nav-left" class="d-none d-lg-flex align-items-center flex-nowrap ms-1">
        @if ($subState === 'grace')
            <span class="badge badge-phoenix badge-phoenix-danger fs-10 text-nowrap py-2 px-3 rounded-pill">
                <span class="fa-regular fa-clock me-1"></span>Overdue · {{ $subscriptionInfo['grace_days_left'] }} day(s) of grace left
            </span>
        @elseif ($subState === 'active')
            <span class="badge badge-phoenix badge-phoenix-{{ $subTone }} fs-10 text-nowrap py-2 px-3 rounded-pill">
                <span class="fa-regular fa-clock me-1"></span>{{ $subscriptionInfo['subscription']->package->name }} · {{ $subDays }} day(s) left
            </span>
        @endif
    </div>

    <div id="sub-nav-right" class="d-flex align-items-center gap-2 me-2">
        @if ($b)
            <button type="button" class="btn btn-sm btn-outline-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#graceModal">
                {{ $b['pendingGrace'] ? 'Grace Requested' : 'Grace Period' }}
            </button>
        @endif
        <button type="button" class="btn btn-sm btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#renewModal">Purchase</button>
    </div>

    <form id="sub-logout-form" method="POST" action="{{ route('portal.logout') }}">@csrf</form>
</div>

@if (session('billing_success'))
    <div class="sub-toast alert alert-subtle-success alert-dismissible" role="alert">
        {{ session('billing_success') }}
        <button type="button" class="btn-close" aria-label="Close" onclick="this.parentElement.remove()"></button>
    </div>
@endif

@if ($b && $b['banner']['show'])
    <div id="subscription-banner" class="alert alert-subtle-{{ $b['banner']['is_grace'] ? 'danger' : 'warning' }} flex-wrap align-items-center justify-content-between gap-2 mb-4 d-none" role="alert">
        <div class="d-flex align-items-center">
            <span class="fa-solid fa-triangle-exclamation me-2"></span>
            <span><strong>{{ $b['banner']['is_grace'] ? 'Subscription Expired!' : 'Subscription Expiring Soon!' }}</strong> {{ $b['banner']['message'] }}</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if ($b['grace']['can_request'] || $b['pendingGrace'])
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#graceModal">
                    <span class="fa-regular fa-clock me-1"></span>{{ $b['pendingGrace'] ? 'Grace Requested' : 'Grace Period' }}
                </button>
            @endif
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#renewModal">
                <span class="fa-regular fa-credit-card me-1"></span>Renew
            </button>
            <button type="button" class="btn-close" id="subscription-banner-close" aria-label="Close"></button>
        </div>
    </div>
@endif

<div id="app"></div>

@if ($b)
{{-- ============ Grace period popup ============ --}}
<div class="modal fade" id="graceModal" tabindex="-1" aria-labelledby="graceModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('portal.grace.request') }}">
                @csrf
                <input type="hidden" name="_form" value="grace">
                <div class="modal-header">
                    <h5 class="modal-title" id="graceModalTitle">Request Grace Period Extension</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($b['pendingGrace'])
                        <div class="alert alert-subtle-info mb-0">
                            Your request to extend the grace period until <strong>{{ $b['pendingGrace']->until_date->format('d F Y') }}</strong>
                            is waiting for the administrator's approval.
                        </div>
                    @elseif (!$b['grace']['can_request'])
                        <div class="alert alert-subtle-warning mb-0">The maximum grace period is already in place for your subscription. Please renew to keep using the portal.</div>
                    @else
                        @if ($b['lastGrace'])
                            <div class="alert alert-subtle-{{ $b['lastGrace']->status === 'approved' ? 'success' : 'danger' }} fs-9 py-2">
                                Your last request (until {{ $b['lastGrace']->until_date->format('d M Y') }}) was <strong>{{ $b['lastGrace']->status }}</strong>@if ($b['lastGrace']->admin_note): {{ $b['lastGrace']->admin_note }}@endif.
                            </div>
                        @endif
                        <label class="form-label" for="until_date">Grace period end date</label>
                        <div class="alert alert-subtle-info py-2">
                            <strong>Current subscription end: {{ $b['info']['subscription']->ends_at->format('d F Y') }}</strong>
                            @if ($b['info']['subscription']->grace_days > 0)
                                <br><span class="fs-9">Current grace ends: {{ $b['info']['grace_ends_at']->format('d F Y') }}</span>
                            @endif
                        </div>
                        <input class="form-control @error('until_date') is-invalid @enderror" type="date" id="until_date" name="until_date"
                               min="{{ $b['grace']['min']->format('Y-m-d') }}" max="{{ $b['grace']['max']->format('Y-m-d') }}"
                               value="{{ old('until_date') }}" required>
                        @error('until_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text mb-3">Choose the date you would like the grace period to run until (between {{ $b['grace']['min']->format('d M Y') }} and {{ $b['grace']['max']->format('d M Y') }}).</div>

                        <label class="form-label" for="grace_reason">Reason <span class="text-body-tertiary">(optional)</span></label>
                        <textarea class="form-control @error('reason') is-invalid @enderror" id="grace_reason" name="reason" rows="2" maxlength="500">{{ old('reason') }}</textarea>
                        @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    @if (!$b['pendingGrace'] && $b['grace']['can_request'])
                        <button type="submit" class="btn btn-primary">Request Extension</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============ Renew / purchase popup ============ --}}
<div class="modal fade" id="renewModal" tabindex="-1" aria-labelledby="renewModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('portal.purchase.request') }}" id="renewForm">
                @csrf
                <input type="hidden" name="_form" value="renew">
                <div class="modal-header">
                    <h5 class="modal-title" id="renewModalTitle">Renew Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($b['pendingPurchase'])
                        <div class="alert alert-subtle-info mb-0">
                            Your renewal request is waiting for the administrator's approval:<br>
                            <strong>{{ $b['pendingPurchase']->package->name }}</strong> · {{ $b['pendingPurchase']->months }} month(s) · {{ $money($b['pendingPurchase']->amount) }}
                            via {{ $methods[$b['pendingPurchase']->method] ?? ucfirst($b['pendingPurchase']->method) }}@if ($b['pendingPurchase']->trx_id) (Trx {{ $b['pendingPurchase']->trx_id }})@endif.
                        </div>
                    @elseif ($b['packages']->isEmpty())
                        <div class="alert alert-subtle-warning mb-0">No plans are available right now. Please contact the administrator.</div>
                    @else
                        @if ($b['lastPurchase'])
                            <div class="alert alert-subtle-{{ $b['lastPurchase']->status === 'approved' ? 'success' : 'danger' }} fs-9 py-2">
                                Your last renewal request ({{ $b['lastPurchase']->package->name }}, {{ $b['lastPurchase']->months }} month(s)) was <strong>{{ $b['lastPurchase']->status }}</strong>@if ($b['lastPurchase']->admin_note): {{ $b['lastPurchase']->admin_note }}@endif.
                            </div>
                        @endif

                        <div class="border border-primary rounded-2 overflow-hidden mb-4">
                            <div class="bg-primary text-white px-3 py-2"><h5 class="mb-0 text-white">Choose Your Plan</h5></div>
                            <div class="p-3">
                                <div class="fw-bold fs-9 mb-2">Choose Duration:</div>
                                @foreach ($b['packages'] as $p)
                                    <label class="plan-card">
                                        <div class="d-flex align-items-start gap-2">
                                            <input class="form-check-input mt-1" type="radio" name="package_id" value="{{ $p['id'] }}"
                                                   @checked((string) old('package_id', $b['packages']->first()['id']) === (string) $p['id'])>
                                            <div class="plan-body">
                                                <div class="plan-price">{{ $money($p['price']) }}<small>/month</small></div>
                                                <div class="fs-9">
                                                    {{ $p['name'] }}@if ($p['term_months'] > 1) · For a {{ $p['term_months'] }}-months term.@endif
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                                @error('package_id')<div class="text-danger fs-9">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="renew_months">Months to pay</label>
                            <input class="form-control @error('months') is-invalid @enderror" type="number" id="renew_months" name="months" min="1" value="{{ old('months', 1) }}" required>
                            @error('months')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text" id="renew_hint"></div>
                        </div>

                        <div class="alert alert-subtle-info py-2 d-flex justify-content-between align-items-center">
                            <span>Total to pay</span><strong class="fs-8" id="renew_total">৳0</strong>
                        </div>
                        @if ($b['extras']['one_time'] > 0 || $b['extras']['cycle'] > 0)
                            <div class="form-text mb-3">Includes additional charges ({{ $money($b['extras']['one_time']) }} one-time + {{ $money($b['extras']['cycle']) }}/month).</div>
                        @endif

                        <div class="alert alert-subtle-secondary fs-9 py-2">
                            @if (config('billing.payment_info'))
                                {!! nl2br(e(config('billing.payment_info'))) !!}
                            @else
                                Send the amount to the administrator (bKash, Nagad, Rocket, bank or cash), then enter the payment details below.
                            @endif
                            Your plan is extended once the administrator confirms the payment.
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-5">
                                <label class="form-label" for="renew_method">Payment method</label>
                                <select class="form-select @error('method') is-invalid @enderror" id="renew_method" name="method">
                                    @foreach ($methods as $k => $v)
                                        <option value="{{ $k }}" @selected(old('method', 'bkash') === $k)>{{ $v }}</option>
                                    @endforeach
                                </select>
                                @error('method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-7">
                                <label class="form-label" for="renew_trx">Transaction ID <span class="text-body-tertiary" id="renew_trx_hint">(required)</span></label>
                                <input class="form-control @error('trx_id') is-invalid @enderror" id="renew_trx" name="trx_id" maxlength="100" value="{{ old('trx_id') }}">
                                @error('trx_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="renew_note">Note <span class="text-body-tertiary">(optional)</span></label>
                                <input class="form-control @error('note') is-invalid @enderror" id="renew_note" name="note" maxlength="255" value="{{ old('note') }}">
                                @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    @if (!$b['pendingPurchase'] && $b['packages']->isNotEmpty())
                        <button type="submit" class="btn btn-primary">Renew</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    // ---- put the banner at the top of the Phoenix page content once Vue has drawn it ----
    var banner = document.getElementById('subscription-banner');
    if (banner) {
        var closed = false;
        try { closed = sessionStorage.getItem('subBannerClosed') === '1'; } catch (e) {}
        var closeBtn = document.getElementById('subscription-banner-close');
        if (closeBtn) closeBtn.addEventListener('click', function () {
            banner.remove();
            try { sessionStorage.setItem('subBannerClosed', '1'); } catch (e) {}
        });
        if (closed) {
            banner.remove();
        } else {
            var place = function () {
                var content = document.querySelector('.content');
                if (!content) return false;
                content.insertBefore(banner, content.firstChild);
                banner.classList.remove('d-none');
                banner.classList.add('d-flex');
                return true;
            };
            if (!place()) {
                var obs = new MutationObserver(function () { if (place()) obs.disconnect(); });
                obs.observe(document.body, { childList: true, subtree: true });
            }
        }
    }

    // ---- renew popup: live months / total ----
    var form = document.getElementById('renewForm');
    var packages = @json($b['packages']->values());
    var extras = @json($b['extras']);
    var months = document.getElementById('renew_months');

    if (form && months && packages.length) {
        var hint = document.getElementById('renew_hint');
        var total = document.getElementById('renew_total');
        var method = document.getElementById('renew_method');
        var trx = document.getElementById('renew_trx');
        var trxHint = document.getElementById('renew_trx_hint');

        var current = function () {
            var r = form.querySelector('input[name=package_id]:checked');
            if (!r) return null;
            for (var i = 0; i < packages.length; i++) if (String(packages[i].id) === r.value) return packages[i];
            return null;
        };
        var fmt = function (n) { return '৳' + (Math.round(n * 100) / 100).toLocaleString('en-US', { maximumFractionDigits: 2 }); };

        var update = function (reset) {
            var p = current();
            if (!p) return;
            months.min = p.min_months;
            months.max = p.max_months;
            var m = parseInt(months.value, 10);
            if (reset || isNaN(m) || m < p.min_months || m > p.max_months) { m = p.min_months; months.value = m; }
            hint.textContent = (p.due > 0 ? 'Due now: ' + p.due + ' month(s). ' : '') + 'You can pay ' + p.min_months + ' to ' + p.max_months + ' month(s).';
            total.textContent = fmt(p.price * m + extras.one_time + extras.cycle * m);
        };

        form.querySelectorAll('input[name=package_id]').forEach(function (r) {
            r.addEventListener('change', function () { update(true); });
        });
        months.addEventListener('input', function () { update(false); });

        var toggleTrx = function () {
            var cash = method.value === 'cash';
            trxHint.textContent = cash ? '(optional for cash)' : '(required)';
        };
        method.addEventListener('change', toggleTrx);

        update(false);
        toggleTrx();
    }

    // ---- if the last submit had errors, open the popup it came from ----
    var reopen = @json($reopen);
    @if ($errors->any() && $reopen)
    var target = document.getElementById(reopen === 'grace' ? 'graceModal' : 'renewModal');
    var tries = 0;
    var timer = setInterval(function () {
        tries++;
        if (window.bootstrap && window.bootstrap.Modal && target) {
            clearInterval(timer);
            window.bootstrap.Modal.getOrCreateInstance(target).show();
        } else if (tries > 100) {
            clearInterval(timer);
        }
    }, 100);
    @endif
})();
</script>
@endif
<script>
(function () {
    var store = document.getElementById('sub-nav-store');
    var left = document.getElementById('sub-nav-left');
    var right = document.getElementById('sub-nav-right');
    var logoutForm = document.getElementById('sub-logout-form');
    if (!store || !left || !right) return;

    var place = function () {
        var nav = document.querySelector('#navbarDefault');
        if (!nav) return false;
        var logo = nav.querySelector('.navbar-logo');
        var icons = nav.querySelector('.navbar-nav-icons');
        var profile = nav.querySelector('.dropdown-profile');
        if (!logo || !icons || !profile) return false;

        // plan badge: next to the logo
        logo.appendChild(left);

        // Grace / Purchase buttons: before the theme toggle
        var li = document.createElement('li');
        li.className = 'nav-item d-flex align-items-center';
        li.appendChild(right);
        icons.insertBefore(li, icons.firstChild);

        // user dropdown: show this portal instead of the demo user
        var name = profile.querySelector('h6');
        if (name) {
            name.textContent = store.dataset.portalName || name.textContent;
            var sub = (store.dataset.portalSub || '').trim();
            if (sub) {
                var p = document.createElement('p');
                p.className = 'fs-9 text-body-tertiary mb-0';
                p.textContent = sub;
                name.parentNode.insertBefore(p, name.nextSibling);
            }
        }

        // "Sign out" in the dropdown really logs out
        var signOut = profile.querySelector('.card-footer a.btn');
        if (signOut && logoutForm) {
            signOut.addEventListener('click', function (e) {
                e.preventDefault();
                logoutForm.submit();
            });
        }
        return true;
    };

    if (!place()) {
        var obs = new MutationObserver(function () { if (place()) obs.disconnect(); });
        obs.observe(document.body, { childList: true, subtree: true });
    }
})();
</script>
</body>
</html>
