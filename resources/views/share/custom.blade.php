@extends('layouts.guest')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <img src="{{ asset('assets/nexsus/images/logo.svg') }}" alt="Nexsus" style="width: 80px; height: 80px;">
                    </div>
                    <h3 class="mb-3">{{ $shareLink->name }}</h3>
                    <p class="text-muted mb-4">This is a custom share page. Configure content in the share link settings.</p>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Custom share pages can be customized to show any content you want.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection