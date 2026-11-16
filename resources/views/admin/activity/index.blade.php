@extends('layouts.admin')
@section('title', 'Activity monitor · Admin')
@section('page_title', 'Activity monitor')
@section('content')
<p class="text-body-tertiary">Every request made by a portal user (path, status, IP) and how many queries it ran on the portal's own database. Request bodies and passwords are never stored.</p>

@foreach ($flagged as $f)
  <div class="alert alert-subtle-warning d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div><b>{{ $f['portal']->portal_name }}</b> — {{ collect($f['alerts'])->pluck('message')->implode(' ') }}</div>
    <a class="btn btn-sm btn-phoenix-warning" href="{{ route('admin.portals.show', [$f['portal'], 'tab' => 'activity']) }}">Review</a>
  </div>
@endforeach

@include('admin.partials._activity_filters', ['ranges' => $ranges, 'portals' => $portals])
@include('admin.partials._activity_table', ['logs' => $logs, 'showPortal' => true])
@endsection
