@extends('layouts.guest')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <img src="{{ asset('assets/nexsus/images/logo.svg') }}" alt="Nexsus" style="width: 60px; height: 60px;">
                    </div>
                    <h4 class="mb-3">{{ $shareLink->name }}</h4>
                    <p class="text-muted mb-4">This link is password protected</p>

                    @if(isset($error))
                    <div class="alert alert-danger">{{ $error }}</div>
                    @endif

                    <form method="POST" action="{{ route('share.access', $shareLink->token) }}">
                        @csrf
                        <div class="mb-3">
                            <input type="password" class="form-control form-control-lg" name="password" placeholder="Enter password" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">Access Link</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
