@extends('layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Create Landing Page</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('landing-pages.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="title" class="form-label">Page Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="2">{{ old('description') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="meta_title" class="form-label">Meta Title</label>
                                    <input type="text" class="form-control" id="meta_title" name="meta_title" value="{{ old('meta_title') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="meta_description" class="form-label">Meta Description</label>
                                    <input type="text" class="form-control" id="meta_description" name="meta_description" value="{{ old('meta_description') }}">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Create Page</button>
                            <a href="{{ route('landing-pages.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Landing Page Builder</h5>
                    <p class="text-muted">After creating your page, you'll be able to customize it using the visual builder.</p>
                    <p class="text-muted">Available blocks:</p>
                    <ul class="text-muted">
                        <li>Hero Section</li>
                        <li>Text Content</li>
                        <li>Image</li>
                        <li>Call to Action</li>
                        <li>Form Embed</li>
                        <li>Features</li>
                        <li>Testimonials</li>
                        <li>Spacer</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
