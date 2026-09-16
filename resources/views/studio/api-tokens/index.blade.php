@extends('studio.layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('api-tokens.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Create Token
                    </a>
                </div>
                <h4 class="page-title">API Tokens</h4>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @forelse($tokens as $token)
                    <div class="d-flex justify-content-between align-items-center py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <h6 class="mb-1">{{ $token->name }}</h6>
                            <div class="text-muted">
                                <small>
                                    <code>{{ $token->prefix }}****</code>
                                    · Scopes: {{ implode(', ', $token->scopes ?? []) }}
                                    @if($token->expires_at)
                                    · Expires: {{ $token->expires_at->format('M d, Y') }}
                                    @endif
                                </small>
                            </div>
                            <div class="text-muted">
                                <small>
                                    Used {{ $token->usage_count }} times
                                    @if($token->last_used_at)
                                    · Last used {{ $token->last_used_at->diffForHumans() }}
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @if($token->is_active)
                            <span class="badge bg-success">Active</span>
                            <form action="{{ route('api-tokens.revoke', $token->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning">Revoke</button>
                            </form>
                            @else
                            <span class="badge bg-secondary">Revoked</span>
                            <form action="{{ route('api-tokens.activate', $token->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success">Activate</button>
                            </form>
                            @endif
                            <form action="{{ route('api-tokens.destroy', $token->id) }}" method="POST" onsubmit="return confirm('Delete this token?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="bi bi-key h1 text-muted"></i>
                        <h5 class="mt-3">No API tokens</h5>
                        <p class="text-muted">Create a token to access the API from third-party apps.</p>
                        <a href="{{ route('api-tokens.create') }}" class="btn btn-primary">Create Token</a>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
