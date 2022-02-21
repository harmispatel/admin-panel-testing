@extends('layouts.app')

@section('content')
<div class="container-fluid h-100">
    <div class="row justify-content-center align-items-center h-100">
        <div class="col col-sm-6 col-md-6 col-lg-5 col-xl-4">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="" style="margin-top: 140px">
                    <h2 class="text-center">Login</h2>
                    <div class="col-md-12">
                        <h3 for="email" class="col-form-label">{{ __('Email Address') }}</h3>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        
                    </div>
                </div>
                
                <div class="">
                    
                    <div class="col-md-12">
                        <h3 for="password" class="col-form-label">{{ __('Password') }}</h3>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                        
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>
                
                {{-- <div class="row mb-3">
                    <div class="col-md-6 offset-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            
                            <label class="form-check-label" for="remember">
                                {{ __('Remember Me') }}
                            </label>
                        </div>
                    </div>
                </div> --}}
                
                {{-- <div class="row mb-0"> --}}
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Login') }}
                        </button>
                        
                        {{-- @if (Route::has('password.request'))
                        <a class="btn btn-link" href="{{ route('password.request') }}">
                            {{ __('Forgot Your Password?') }}
                        </a>
                        @endif --}}
                    </div>
                    {{-- </div> --}}
                </form>
            </div>
        </div>
    </div>
    @endsection
    