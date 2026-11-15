@extends('layouts.base')
@section('title', 'Admin Login')
@section('body')
<main class="main">
  <div class="container">
    <div class="row flex-center min-vh-100 py-5">
      <div class="col-sm-10 col-md-6 col-lg-4">
        <div class="text-center mb-6">
          <h3 class="text-body-highlight">Admin Panel</h3>
          <p class="text-body-tertiary">Sign in to manage portals</p>
        </div>
        <form method="POST" action="{{ route('admin.login.submit') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-4">
            <label class="form-label" for="password">Password</label>
            <input class="form-control" id="password" name="password" type="password" required>
          </div>
          <button class="btn btn-primary w-100" type="submit">Sign in</button>
        </form>
      </div>
    </div>
  </div>
</main>
@endsection
