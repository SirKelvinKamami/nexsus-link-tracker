@extends('layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('landing-pages.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Create Page
                    </a>
                </div>
                <h4 class="page-title">Landing Pages</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($pages->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-window display-1 text-muted"></i>
                        <h5 class="mt-3">No Landing Pages Yet</h5>
                        <p class="text-muted">Create your first landing page to start collecting leads.</p>
                        <a href="{{ route('landing-pages.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Create Page
                        </a>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Page</th>
                                    <th>Status</th>
                                    <th>Link</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pages as $page)
                                <tr>
                                    <td>
                                        <div>
                                            <h6 class="mb-0">{{ $page->title }}</h6>
                                            <small class="text-muted">{{ Str::limit($page->description, 50) }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($page->is_published)
                                        <span class="badge bg-success">Published</span>
                                        @else
                                        <span class="badge bg-secondary">Draft</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($page->is_published)
                                        <code>{{ url('/lp/' . $page->slug) }}</code>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $page->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if($page->is_published)
                                            <a href="{{ url('/lp/' . $page->slug) }}" class="btn btn-outline-primary" target="_blank" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @endif
                                            <a href="{{ route('landing-pages.edit', $page->id) }}" class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-info btn-toggle-publish" data-id="{{ $page->id }}" title="Toggle Publish">
                                                <i class="bi bi-{{ $page->is_published ? 'eye-slash' : 'eye' }}"></i>
                                            </button>
                                            <form action="{{ route('landing-pages.destroy', $page->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this page?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.btn-toggle-publish').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        const response = await fetch(`/studio/landing-pages/${id}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
        });
        
        if (response.ok) {
            location.reload();
        }
    });
});
</script>
@endpush
@endsection
