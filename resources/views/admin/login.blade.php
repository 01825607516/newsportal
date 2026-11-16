@extends('layouts.base')
@section('title', 'Admin Login')
@section('body')
<main class="main">
  <div class="container">
    <div class="row flex-center min-vh-100 py-5">
      <div class="col-sm-10 col-md-8 col-lg-5 col-xl-5">
        <div class="text-center mb-7">
          <img src="{{ asset('assets/img/icons/logo.png') }}" alt="" width="48" onerror="this.style.display='none'">
          <h3 class="text-body-highlight mt-3">Admin Login</h3>
          <p class="text-body-tertiary">Sign in to manage portals</p>
        </div>

        <form method="POST" action="{{ route('admin.login.submit') }}" novalidate>
          @csrf

          <div class="mb-3">
            <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="mb-4">
            <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="current-password" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <button class="btn btn-primary w-100" type="submit">Login</button>
        </form>
      </div>
    </div>
  </div>
</main>
@endsection
