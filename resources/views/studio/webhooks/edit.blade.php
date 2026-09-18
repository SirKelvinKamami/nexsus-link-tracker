@extends('layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Edit Webhook: {{ $webhook->name }}</h4>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('webhooks.update', $webhook->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $webhook->name }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="url" class="form-label">Endpoint URL</label>
                            <input type="url" class="form-control" id="url" name="url" value="{{ $webhook->url }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Events</label>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="events[]" value="click" id="event_click" {{ in_array('click', $webhook->events ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="event_click">Click</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="events[]" value="form_submit" id="event_form" {{ in_array('form_submit', $webhook->events ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="event_form">Form Submit</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="events[]" value="page_visit" id="event_visit" {{ in_array('page_visit', $webhook->events ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="event_visit">Page Visit</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="events[]" value="document_download" id="event_download" {{ in_array('document_download', $webhook->events ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="event_download">Document Download</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ $webhook->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="{{ route('webhooks.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Delivery Log</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Status</th>
                                    <th>Code</th>
                                    <th>Delivered</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deliveries as $delivery)
                                <tr>
                                    <td><span class="badge bg-info">{{ $delivery->event }}</span></td>
                                    <td>
                                        <span class="badge {{ $delivery->success ? 'bg-success' : 'bg-danger' }}">
                                            {{ $delivery->success ? 'OK' : 'Failed' }}
                                        </span>
                                    </td>
                                    <td>{{ $delivery->status_code ?? 'N/A' }}</td>
                                    <td>{{ $delivery->delivered_at->diffForHumans() }}</td>
                                    <td>
                                        <form action="{{ route('webhooks.redeliver', [$webhook->id, $delivery->id]) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Retry</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">No deliveries yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Webhook Info</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span>Created</span>
                            <span>{{ $webhook->created_at->format('M d, Y') }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span>Total Deliveries</span>
                            <span>{{ $webhook->deliveries()->count() }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span>Failures</span>
                            <span class="{{ $webhook->failure_count > 0 ? 'text-danger' : '' }}">{{ $webhook->failure_count }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-danger">
                <div class="card-body">
                    <h5 class="card-title text-danger">Danger Zone</h5>
                    <form action="{{ route('webhooks.destroy', $webhook->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">Delete Webhook</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
