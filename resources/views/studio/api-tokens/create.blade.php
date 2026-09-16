@extends('studio.layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Create API Token</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('api-tokens.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Token Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g., DAS Hub Integration" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Scopes</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="scopes[]" value="read" id="scope_read" checked>
                                        <label class="form-check-label" for="scope_read">Read</label>
                                        <small class="d-block text-muted">View links, analytics, forms</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="scopes[]" value="write" id="scope_write">
                                        <label class="form-check-label" for="scope_write">Write</label>
                                        <small class="d-block text-muted">Create/edit links, forms</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="scopes[]" value="admin" id="scope_admin">
                                        <label class="form-check-label" for="scope_admin">Admin</label>
                                        <small class="d-block text-muted">Delete resources, manage tokens</small>
                                    </div>
                                </div>
                            </div>
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

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Create Token</button>
                            <a href="{{ route('api-tokens.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">About API Tokens</h5>
                    <p class="text-muted">API tokens let third-party apps access your data.</p>
                    <h6 class="mt-3">Usage</h6>
                    <pre class="bg-light p-2 rounded"><code>Authorization: Bearer YOUR_TOKEN</code></pre>
                    <h6 class="mt-3">Scopes</h6>
                    <ul class="text-muted">
                        <li><strong>Read:</strong> View links, analytics, forms</li>
                        <li><strong>Write:</strong> Create/edit resources</li>
                        <li><strong>Admin:</strong> Delete, manage tokens</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
