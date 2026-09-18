@extends('layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('forms.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Create Form
                    </a>
                </div>
                <h4 class="page-title">Forms</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($forms->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-ui-checks display-1 text-muted"></i>
                        <h5 class="mt-3">No Forms Yet</h5>
                        <p class="text-muted">Create your first form to start collecting responses.</p>
                        <a href="{{ route('forms.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Create Form
                        </a>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Form</th>
                                    <th>Status</th>
                                    <th>Responses</th>
                                    <th>Link</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($forms as $form)
                                <tr>
                                    <td>
                                        <div>
                                            <h6 class="mb-0">{{ $form->title }}</h6>
                                            <small class="text-muted">{{ Str::limit($form->description, 50) }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($form->is_active)
                                        <span class="badge bg-success">Active</span>
                                        @else
                                        <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $form->responses_count }}</span>
                                    </td>
                                    <td>
                                        <code>{{ url('/f/' . $form->slug) }}</code>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $form->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ url('/f/' . $form->slug) }}" class="btn btn-outline-primary" target="_blank" title="Preview">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('forms.edit', $form->id) }}" class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('forms.responses', $form->id) }}" class="btn btn-outline-info" title="Responses">
                                                <i class="bi bi-list-check"></i>
                                            </a>
                                            <a href="{{ route('forms.export', $form->id) }}" class="btn btn-outline-success" title="Export CSV">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <form action="{{ route('forms.destroy', $form->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this form?')">
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
@endsection
