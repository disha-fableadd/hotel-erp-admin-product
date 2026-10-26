@extends('license-admin.layout')

@section('title', 'Login')

@section('content')
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card p-4">
                <h3 class="mb-1">License server admin</h3>
                <p class="text-muted small mb-4">Register and manage Envato purchase codes.</p>

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('license-admin.login.submit') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Sign in</button>
                </form>
            </div>
        </div>
    </div>
@endsection

