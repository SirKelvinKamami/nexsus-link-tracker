@extends('layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Edit Project: {{ $project->name }}</h4>
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
                    <form action="{{ route('projects.update', $project->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Project Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $project->name }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2">{{ $project->description }}</textarea>
                        </div>

                        <hr>

                        <h5 class="mb-3">Tracking Settings</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ga4_id" class="form-label">GA4 Measurement ID</label>
                                    <input type="text" class="form-control" id="ga4_id" name="ga4_id" value="{{ $project->getSetting('ga4_id') }}" placeholder="G-XXXXXXXXXX">
                                    <small class="text-muted">Google Analytics 4 tracking ID</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gtm_id" class="form-label">GTM Container ID</label>
                                    <input type="text" class="form-control" id="gtm_id" name="gtm_id" value="{{ $project->getSetting('gtm_id') }}" placeholder="GTM-XXXXXXX">
                                    <small class="text-muted">Google Tag Manager container ID</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="primary_color" class="form-label">Primary Color</label>
                            <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color" value="{{ $project->getSetting('primary_color', '#667eea') }}">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Project Info</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span>Created</span>
                            <span>{{ $project->created_at->format('M d, Y') }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span>Links</span>
                            <span>{{ $project->links()->count() }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span>Forms</span>
                            <span>{{ $project->forms()->count() }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span>Landing Pages</span>
                            <span>{{ $project->landingPages()->count() }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-danger">
                <div class="card-body">
                    <h5 class="card-title text-danger">Danger Zone</h5>
                    <p class="text-muted">Deleting a project will move all its items to the default project.</p>
                    <form action="{{ route('projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this project?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">Delete Project</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
