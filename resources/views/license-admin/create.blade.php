@extends('license-admin.layout')

@section('title', 'Register code')

@section('content')
    <div class="mb-4">
        <a href="{{ route('license-admin.dashboard') }}" class="text-decoration-none">&larr; Back</a>
        <h2 class="mt-2">Register purchase code</h2>
        <p class="text-muted">After saving, the buyer can activate on <code>/license</code> with this code and Envato username.</p>
    </div>

    <div class="card p-4 col-lg-8">
        <form method="POST" action="{{ route('license-admin.store') }}">
            @csrf
            @include('license-admin._form', ['license' => null])
            <button type="submit" class="btn btn-primary">Save &amp; activate code</button>
        </form>
    </div>
@endsection

