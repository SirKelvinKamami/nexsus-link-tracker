@extends('studio.layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Create Form</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('forms.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="title" class="form-label">Form Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="collect_email" name="collect_email" value="1" {{ old('collect_email') ? 'checked' : '' }}>
                                <label class="form-check-label" for="collect_email">Collect email addresses</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="one_response_per_ip" name="one_response_per_ip" value="1" {{ old('one_response_per_ip') ? 'checked' : '' }}>
                                <label class="form-check-label" for="one_response_per_ip">One response per IP address</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Create Form</button>
                            <a href="{{ route('forms.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Builder</h5>
                    <p class="text-muted">After creating your form, you'll be able to add fields using the drag-and-drop builder.</p>
                    <p class="text-muted">Supported field types:</p>
                    <ul class="text-muted">
                        <li>Text Input</li>
                        <li>Email</li>
                        <li>Phone</li>
                        <li>URL</li>
                        <li>Number</li>
                        <li>Textarea</li>
                        <li>Select Dropdown</li>
                        <li>Radio Buttons</li>
                        <li>Checkboxes</li>
                        <li>Date Picker</li>
                        <li>File Upload</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
