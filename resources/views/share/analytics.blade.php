@extends('layouts.guest')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <img src="{{ asset('assets/nexsus/images/logo.svg') }}" alt="Nexsus" style="width: 40px; height: 40px;" class="me-2">
                            <h4 class="d-inline mb-0">{{ $shareLink->name }}</h4>
                            @if($shareLink->expires_at)
                            <span class="badge bg-warning ms-2">Expires {{ $shareLink->expires_at->format('M d, Y') }}</span>
                            @endif
                        </div>
                        <span class="badge bg-info">Shared Analytics</span>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h2 class="mb-0">1,234</h2>
                                    <small>Total Clicks</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h2 class="mb-0">892</h2>
                                    <small>Unique Visitors</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h2 class="mb-0">2.3s</h2>
                                    <small>Avg Time</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h2 class="mb-0">34%</h2>
                                    <small>Bounce Rate</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Top Countries</h6>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between"><span>United States</span><span>45%</span></li>
                                        <li class="list-group-item d-flex justify-content-between"><span>Canada</span><span>18%</span></li>
                                        <li class="list-group-item d-flex justify-content-between"><span>United Kingdom</span><span>12%</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Top Referrers</h6>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between"><span>Direct</span><span>52%</span></li>
                                        <li class="list-group-item d-flex justify-content-between"><span>Google</span><span>28%</span></li>
                                        <li class="list-group-item d-flex justify-content-between"><span>Twitter</span><span>12%</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection