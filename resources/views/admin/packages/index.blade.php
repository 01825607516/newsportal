@extends('layouts.admin')
@section('title', 'Packages · Admin')
@section('content')
<h3 class="mb-1">Packages</h3>
<p class="text-body-tertiary">Changes apply to new payments only. Running subscriptions keep the price, duration and grace period they were started with.</p>

<div class="row g-3">
@foreach ($packages as $pkg)
  <div class="col-lg-6">
    <div class="card"><div class="card-body">
      <form method="POST" action="{{ route('admin.packages.update', $pkg) }}">
        @csrf @method('PUT')
        <div class="d-flex justify-content-between mb-3">
          <h5 class="mb-0">{{ $pkg->name }}</h5>
          @if ($pkg->is_trial)<span class="badge badge-phoenix badge-phoenix-info">one-time trial</span>@endif
        </div>
        <div class="row g-3">
          <div class="col-12"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ $pkg->name }}" required></div>
          <div class="col-md-4"><label class="form-label">Charge (tk)</label><input class="form-control" type="number" step="0.01" min="0" name="price" value="{{ $pkg->price }}" required></div>
          <div class="col-md-4"><label class="form-label">Duration (days)</label><input class="form-control" type="number" min="1" name="duration_days" value="{{ $pkg->duration_days }}" required></div>
          <div class="col-md-4"><label class="form-label">Grace period (days)</label><input class="form-control" type="number" min="0" name="grace_days" value="{{ $pkg->grace_days }}" required></div>
          <div class="col-12">
            <div class="form-check"><input class="form-check-input" type="checkbox" name="active" value="1" id="active{{ $pkg->id }}" @checked($pkg->active)><label class="form-check-label" for="active{{ $pkg->id }}">Active (available to assign)</label></div>
          </div>
        </div>
        <button class="btn btn-primary mt-3" type="submit">Save</button>
      </form>
    </div></div>
  </div>
@endforeach
</div>
@endsection
