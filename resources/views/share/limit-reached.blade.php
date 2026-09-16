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
                    <h4 class="mb-3">Usage Limit Reached</h4>
                    <p class="text-muted">This shareable link has reached its maximum number of uses.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection