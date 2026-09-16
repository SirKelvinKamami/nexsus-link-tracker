@extends('layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Create Shareable Link</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('share-links.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Link Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g., My Dashboard" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Link Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="dashboard">Dashboard</option>
                                <option value="analytics">Analytics</option>
                                <option value="form">Form</option>
                                <option value="landing_page">Landing Page</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>

                        <div class="mb-3" id="target_id_group">
                            <label for="target_id" class="form-label">Target ID (optional)</label>
                            <input type="text" class="form-control" id="target_id" name="target_id" value="{{ old('target_id') }}" placeholder="Form slug or Landing Page slug">
                            <small class="text-muted">For forms or landing pages, enter the slug</small>
                        </div>

                        <div class="mb-3">
                            <label for="expires_in" class="form-label">Expiration</label>
                            <select class="form-select" id="expires_in" name="expires_in">
                                <option value="">Never</option>
                                <option value="7">7 days</option>
                                <option value="30">30 days</option>
                                <option value="90">90 days</option>
                                <option value="180">180 days</option>
                                <option value="365">1 year</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password Protection (optional)</label>
                            <input type="password" class="form-control" id="password" name="password" value="{{ old('password') }}" placeholder="Leave empty for no password">
                        </div>

                        <div class="mb-3">
                            <label for="max_uses" class="form-label">Max Uses (optional)</label>
                            <input type="number" class="form-control" id="max_uses" name="max_uses" value="{{ old('max_uses') }}" placeholder="Leave empty for unlimited" min="1">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Create Share Link</button>
                            <a href="{{ route('share-links.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">About Shareable Links</h5>
                    <p class="text-muted">Create permanent links to share your dashboard, analytics, or specific content.</p>
                    <h6 class="mt-3">Link Types</h6>
                    <ul class="text-muted">
                        <li><strong>Dashboard:</strong> Share your main dashboard</li>
                        <li><strong>Analytics:</strong> Share analytics view</li>
                        <li><strong>Form:</strong> Share a specific form</li>
                        <li><strong>Landing Page:</strong> Share a landing page</li>
                        <li><strong>Custom:</strong> Custom share page</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
