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
                        <span class="badge bg-success">Shared Dashboard</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h2 class="mb-0">12</h2>
                                    <small>Links</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h2 class="mb-0">1,234</h2>
                                    <small>Total Clicks</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h2 class="mb-0">892</h2>
                                    <small>Unique Visitors</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h2 class="mb-0">3</h2>
                                    <small>Projects</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>Recent Links</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Link</th>
                                        <th>Clicks</th>
                                        <th>CTR</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><a href="#">nexsus.link/abc123</a></td>
                                        <td>456</td>
                                        <td>12.3%</td>
                                        <td>Jan 15</td>
                                    </tr>
                                    <tr>
                                        <td><a href="#">nexsus.link/def456</a></td>
                                        <td>321</td>
                                        <td>8.7%</td>
                                        <td>Jan 10</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection