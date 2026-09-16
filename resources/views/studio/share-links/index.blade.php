@extends('layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('share-links.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Create Share Link
                    </a>
                </div>
                <h4 class="page-title">Shareable Links</h4>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @forelse($shareLinks as $link)
                    <div class="d-flex justify-content-between align-items-center py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <h6 class="mb-1">{{ $link->name }}</h6>
                            <div class="text-muted">
                                <small>
                                    Type: {{ ucfirst(str_replace('_', ' ', $link->type)) }}
                                    · Uses: {{ $link->use_count }}{{ $link->max_uses ? "/{$link->max_uses}" : '' }}
                                    @if($link->expires_at)
                                    · Expires: {{ $link->expires_at->format('M d, Y') }}
                                    @endif
                                    @if($link->password)
                                    · <i class="bi bi-lock"></i> Protected
                                    @endif
                                </small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @if($link->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                            <button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('{{ $link->share_url }}')">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                            <form action="{{ route('share-links.toggle', $link->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                    {{ $link->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <form action="{{ route('share-links.destroy', $link->id) }}" method="POST" onsubmit="return confirm('Delete this link?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="bi bi-share h1 text-muted"></i>
                        <h5 class="mt-3">No shareable links</h5>
                        <p class="text-muted">Create a shareable link to share your dashboard or analytics.</p>
                        <a href="{{ route('share-links.create') }}" class="btn btn-primary">Create Share Link</a>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text);
    alert('Link copied to clipboard!');
}
</script>
@endsection
