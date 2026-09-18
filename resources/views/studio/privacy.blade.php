@extends('layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Privacy Settings</h4>
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
                    <form action="{{ route('editPrivacy') }}" method="POST">
                        @csrf

                        <h5 class="mb-3">Profile Visibility</h5>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="profile_visible" name="profile_visible" value="1" {{ $privacy->profile_visible ? 'checked' : '' }}>
                            <label class="form-check-label" for="profile_visible">Profile page visible to public</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="email_visible" name="email_visible" value="1" {{ $privacy->email_visible ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_visible">Show email on profile</label>
                        </div>

                        <h5 class="mb-3 mt-4">Link Visibility</h5>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="links_visible" name="links_visible" value="1" {{ $privacy->links_visible ? 'checked' : '' }}>
                            <label class="form-check-label" for="links_visible">Links visible to public</label>
                        </div>

                        <h5 class="mb-3 mt-4">Analytics</h5>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="analytics_visible" name="analytics_visible" value="1" {{ $privacy->analytics_visible ? 'checked' : '' }}>
                            <label class="form-check-label" for="analytics_visible">Analytics visible in shared views</label>
                        </div>

                        <h5 class="mb-3 mt-4">Comments</h5>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="allow_comments" name="allow_comments" value="1" {{ $privacy->allow_comments ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_comments">Allow comments on shared links</label>
                        </div>

                        <h5 class="mb-3 mt-4">Default Link Permission</h5>
                        <p class="text-muted">When you create a new link, who can see it by default?</p>
                        <select class="form-select" id="default_link_permission" name="default_link_permission">
                            <option value="private" {{ $privacy->default_link_permission === 'private' ? 'selected' : '' }}>Private (only me)</option>
                            <option value="viewer" {{ $privacy->default_link_permission === 'viewer' ? 'selected' : '' }}>Viewers</option>
                            <option value="commenter" {{ $privacy->default_link_permission === 'commenter' ? 'selected' : '' }}>Commenters</option>
                            <option value="editor" {{ $privacy->default_link_permission === 'editor' ? 'selected' : '' }}>Editors</option>
                            <option value="public" {{ $privacy->default_link_permission === 'public' ? 'selected' : '' }}>Public</option>
                        </select>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Save Privacy Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Roles Explained</h5>
                    <div class="mb-3">
                        <strong>Viewer</strong>
                        <p class="text-muted small mb-0">Can view shared links and profile. No comments.</p>
                    </div>
                    <div class="mb-3">
                        <strong>Commenter</strong>
                        <p class="text-muted small mb-0">Can view and comment on shared links.</p>
                    </div>
                    <div class="mb-3">
                        <strong>Editor</strong>
                        <p class="text-muted small mb-0">Can view, comment, and edit shared content.</p>
                    </div>
                    <div class="mb-3">
                        <strong>Admin</strong>
                        <p class="text-muted small mb-0">Full access to all features and user management.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
