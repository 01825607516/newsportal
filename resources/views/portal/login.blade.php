@extends('layouts.base')
@section('title', 'Portal Login')
@section('body')
<main class="main">
  <div class="container">
    <div class="row flex-center min-vh-100 py-5">
      <div class="col-sm-10 col-md-8 col-lg-5 col-xl-5">
        <div class="text-center mb-7">
          <img src="{{ asset('assets/img/icons/logo.png') }}" alt="" width="48" onerror="this.style.display='none'">
          <h3 class="text-body-highlight mt-3">Portal Login</h3>
          <p class="text-body-tertiary">Enter your portal information to continue</p>
        </div>

        <form method="POST" action="{{ route('portal.login.submit') }}" novalidate>
          @csrf

          <div class="mb-3">
            <label class="form-label" for="portal_name">Portal name <span class="text-danger">*</span></label>
            <input class="form-control @error('portal_name') is-invalid @enderror" id="portal_name" name="portal_name" type="text" value="{{ old('portal_name') }}" required autofocus>
            @error('portal_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-label" for="portal_reg_no">Portal reg. no <span class="text-body-tertiary">(optional)</span></label>
            <input class="form-control @error('portal_reg_no') is-invalid @enderror" id="portal_reg_no" name="portal_reg_no" type="text" value="{{ old('portal_reg_no') }}">
            @error('portal_reg_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label" for="editor_name">Editor name <span class="text-body-tertiary">(optional)</span></label>
              <input class="form-control @error('editor_name') is-invalid @enderror" id="editor_name" name="editor_name" type="text" value="{{ old('editor_name') }}">
              @error('editor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="editor_email">Editor email <span class="text-body-tertiary">(optional)</span></label>
              <input class="form-control @error('editor_email') is-invalid @enderror" id="editor_email" name="editor_email" type="email" value="{{ old('editor_email') }}">
              @error('editor_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="editor_mobile">Editor mobile <span class="text-danger">*</span></label>
            <input class="form-control @error('editor_mobile') is-invalid @enderror" id="editor_mobile" name="editor_mobile" type="tel" placeholder="01XXXXXXXXX" value="{{ old('editor_mobile') }}" required>
            @error('editor_mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="mb-3">
            <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
            <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" autocomplete="current-password" required>
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="mb-4">
            <label class="form-label" for="password_confirmation">Confirm password <span class="text-body-tertiary">(first-time setup only)</span></label>
            <input class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
            @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">New portal registering? Enter the same password again here. Returning users can leave this blank.</div>
          </div>

          <button class="btn btn-primary w-100" type="submit">Login</button>
        </form>
      </div>
    </div>
  </div>
</main>
@endsection
