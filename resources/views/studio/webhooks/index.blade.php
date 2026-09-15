@extends('studio.layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('webhooks.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Create Webhook
                    </a>
                </div>
                <h4 class="page-title">Webhooks</h4>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        @forelse($webhooks as $webhook)
        <div class="col-lg-6 mb-4">
            <div class="card {{ !$webhook->is_active ? 'border-secondary' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-1">{{ $webhook->name }}</h5>
                            <p class="text-muted mb-2">{{ Str::limit($webhook->url, 50) }}</p>
                        </div>
                        <span class="badge {{ $webhook->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $webhook->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">
                            Events: {{ implode(', ', $webhook->events ?? []) }}
                        </small>
                    </div>
                    @if($webhook->failure_count > 0)
                    <div class="text-danger">
                        <small>{{ $webhook->failure_count }} consecutive failures</small>
                    </div>
                    @endif
                </div>
                <div class="card-footer bg-transparent">
                    <a href="{{ route('webhooks.edit', $webhook->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form action="{{ route('webhooks.test', $webhook->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Test</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-webhook h1 text-muted"></i>
                    <h5 class="mt-3">No webhooks yet</h5>
                    <p class="text-muted">Create a webhook to receive real-time event notifications.</p>
                    <a href="{{ route('webhooks.create') }}" class="btn btn-primary">Create Webhook</a>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
