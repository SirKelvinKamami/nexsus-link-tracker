@extends('layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('projects.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Create Project
                    </a>
                </div>
                <h4 class="page-title">Projects</h4>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        @foreach($projects as $project)
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 {{ $currentProjectId == $project->id ? 'border-primary' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0">{{ $project->name }}</h5>
                        @if($project->is_default)
                        <span class="badge bg-primary">Default</span>
                        @endif
                    </div>
                    @if($project->description)
                    <p class="text-muted">{{ Str::limit($project->description, 100) }}</p>
                    @endif
                    
                    <div class="mb-3">
                        <small class="text-muted">
                            {{ $project->links_count }} links · 
                            {{ $project->forms_count }} forms · 
                            {{ $project->landing_pages_count }} pages
                        </small>
                    </div>

                    @if($project->getSetting('ga4_id') || $project->getSetting('gtm_id'))
                    <div class="mb-3">
                        @if($project->getSetting('ga4_id'))
                        <span class="badge bg-success">GA4: {{ $project->getSetting('ga4_id') }}</span>
                        @endif
                        @if($project->getSetting('gtm_id'))
                        <span class="badge bg-info">GTM: {{ $project->getSetting('gtm_id') }}</span>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="card-footer bg-transparent">
                    <div class="btn-group w-100" role="group">
                        @if($currentProjectId != $project->id)
                        <form action="{{ route('projects.switch', $project->id) }}" method="POST" class="d-inline flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-arrow-left-right"></i> Switch
                            </button>
                        </form>
                        @else
                        <button class="btn btn-primary btn-sm flex-fill" disabled>
                            <i class="bi bi-check-lg"></i> Active
                        </button>
                        @endif
                        
                        @if(!$project->is_default)
                        <form action="{{ route('projects.default', $project->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm" title="Set as Default">
                                <i class="bi bi-star"></i>
                            </button>
                        </form>
                        @endif
                        
                        <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-gear"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
