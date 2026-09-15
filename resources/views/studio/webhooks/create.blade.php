@extends('studio.layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Create Webhook</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('webhooks.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="url" class="form-label">Endpoint URL</label>
                            <input type="url" class="form-control @error('url') is-invalid @enderror" id="url" name="url" value="{{ old('url') }}" placeholder="https://example.com/webhook" required>
                            @error('url')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Events</label>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="events[]" value="click" id="event_click" checked>
                                        <label class="form-check-label" for="event_click">Click</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="events[]" value="form_submit" id="event_form" checked>
                                        <label class="form-check-label" for="event_form">Form Submit</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="events[]" value="page_visit" id="event_visit" checked>
                                        <label class="form-check-label" for="event_visit">Page Visit</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="events[]" value="document_download" id="event_download">
                                        <label class="form-check-label" for="event_download">Document Download</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Create Webhook</button>
                            <a href="{{ route('webhooks.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">About Webhooks</h5>
                    <p class="text-muted">Webhooks send HTTP POST requests to your URL when events occur.</p>
                    <h6 class="mt-3">Payload Format</h6>
                    <pre class="bg-light p-2 rounded"><code>{
  "event": "click",
  "data": { ... },
  "timestamp": "2024-01-01T00:00:00Z"
}</code></pre>
                    <h6 class="mt-3">Headers</h6>
                    <ul class="text-muted">
                        <li><code>X-Webhook-Event</code>: Event type</li>
                        <li><code>X-Webhook-Signature</code>: HMAC-SHA256</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
