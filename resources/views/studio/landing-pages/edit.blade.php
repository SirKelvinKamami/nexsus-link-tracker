@extends('studio.layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    @if($page->is_published)
                    <a href="{{ url('/lp/' . $page->slug) }}" class="btn btn-outline-primary" target="_blank">
                        <i class="bi bi-eye"></i> View Live
                    </a>
                    @endif
                    <button type="button" class="btn btn-success" id="publish-btn">
                        <i class="bi bi-{{ $page->is_published ? 'eye-slash' : 'eye' }}"></i>
                        {{ $page->is_published ? 'Unpublish' : 'Publish' }}
                    </button>
                </div>
                <h4 class="page-title">Edit: {{ $page->title }}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Page Settings</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('landing-pages.update', $page->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="title" class="form-label">Page Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ $page->title }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2">{{ $page->description }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="meta_title" class="form-label">Meta Title</label>
                                    <input type="text" class="form-control" id="meta_title" name="meta_title" value="{{ $page->meta_title }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="meta_description" class="form-label">Meta Description</label>
                                    <input type="text" class="form-control" id="meta_description" name="meta_description" value="{{ $page->meta_description }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="form_id" class="form-label">Embedded Form</label>
                                    <select class="form-select" id="form_id" name="form_id">
                                        <option value="">None</option>
                                        @foreach($forms as $form)
                                        <option value="{{ $form->id }}" {{ $page->form_id == $form->id ? 'selected' : '' }}>
                                            {{ $form->title }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="collect_emails" name="collect_emails" value="1" {{ $page->collect_emails ? 'checked' : '' }}>
                                        <label class="form-check-label" for="collect_emails">Collect Emails</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Save Settings</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Page Content</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-plus"></i> Add Block
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item add-block" href="#" data-type="hero">Hero Section</a></li>
                            <li><a class="dropdown-item add-block" href="#" data-type="text">Text Content</a></li>
                            <li><a class="dropdown-item add-block" href="#" data-type="image">Image</a></li>
                            <li><a class="dropdown-item add-block" href="#" data-type="cta">Call to Action</a></li>
                            <li><a class="dropdown-item add-block" href="#" data-type="features">Features</a></li>
                            <li><a class="dropdown-item add-block" href="#" data-type="testimonial">Testimonial</a></li>
                            <li><a class="dropdown-item add-block" href="#" data-type="spacer">Spacer</a></li>
                            <li><a class="dropdown-item add-block" href="#" data-type="form">Form Embed</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div id="blocks-container">
                        @foreach($page->getBlocks() as $index => $block)
                        <div class="block-item card mb-3" data-index="{{ $index }}" data-type="{{ $block['type'] }}">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span class="badge bg-primary">{{ ucfirst($block['type']) }} Block</span>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary btn-move-up" {{ $index === 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-up"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-move-down" {{ $index === count($page->getBlocks()) - 1 ? 'disabled' : '' }}>
                                        <i class="bi bi-arrow-down"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-delete-block">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($block['type'] === 'hero')
                                <div class="mb-3">
                                    <label class="form-label">Heading</label>
                                    <input type="text" class="form-control block-data" data-field="heading" value="{{ $block['data']['heading'] ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Subheading</label>
                                    <input type="text" class="form-control block-data" data-field="subheading" value="{{ $block['data']['subheading'] ?? '' }}">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Background Color</label>
                                        <input type="color" class="form-control form-control-color block-data" data-field="background_color" value="{{ $block['data']['background_color'] ?? '#667eea' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Text Color</label>
                                        <input type="color" class="form-control form-control-color block-data" data-field="text_color" value="{{ $block['data']['text_color'] ?? '#ffffff' }}">
                                    </div>
                                </div>

                                @elseif($block['type'] === 'text')
                                <div class="mb-3">
                                    <label class="form-label">Content</label>
                                    <textarea class="form-control block-data" data-field="content" rows="4">{{ $block['data']['content'] ?? '' }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Alignment</label>
                                    <select class="form-select block-data" data-field="alignment">
                                        <option value="left" {{ ($block['data']['alignment'] ?? '') === 'left' ? 'selected' : '' }}>Left</option>
                                        <option value="center" {{ ($block['data']['alignment'] ?? '') === 'center' ? 'selected' : '' }}>Center</option>
                                        <option value="right" {{ ($block['data']['alignment'] ?? '') === 'right' ? 'selected' : '' }}>Right</option>
                                    </select>
                                </div>

                                @elseif($block['type'] === 'image')
                                <div class="mb-3">
                                    <label class="form-label">Image URL</label>
                                    <input type="url" class="form-control block-data" data-field="url" value="{{ $block['data']['url'] ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Alt Text</label>
                                    <input type="text" class="form-control block-data" data-field="alt" value="{{ $block['data']['alt'] ?? '' }}">
                                </div>

                                @elseif($block['type'] === 'cta')
                                <div class="mb-3">
                                    <label class="form-label">Heading</label>
                                    <input type="text" class="form-control block-data" data-field="heading" value="{{ $block['data']['heading'] ?? '' }}">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Button Text</label>
                                        <input type="text" class="form-control block-data" data-field="button_text" value="{{ $block['data']['button_text'] ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Button URL</label>
                                        <input type="url" class="form-control block-data" data-field="button_url" value="{{ $block['data']['button_url'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label">Button Color</label>
                                    <input type="color" class="form-control form-control-color block-data" data-field="button_color" value="{{ $block['data']['button_color'] ?? '#667eea' }}">
                                </div>

                                @elseif($block['type'] === 'features')
                                <div class="mb-3">
                                    <label class="form-label">Features (one per line: Icon | Title | Description)</label>
                                    <textarea class="form-control block-data" data-field="features" rows="4">{{ $block['data']['features'] ?? '' }}</textarea>
                                </div>

                                @elseif($block['type'] === 'testimonial')
                                <div class="mb-3">
                                    <label class="form-label">Quote</label>
                                    <textarea class="form-control block-data" data-field="quote" rows="2">{{ $block['data']['quote'] ?? '' }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Author</label>
                                    <input type="text" class="form-control block-data" data-field="author" value="{{ $block['data']['author'] ?? '' }}">
                                </div>

                                @elseif($block['type'] === 'spacer')
                                <div class="mb-3">
                                    <label class="form-label">Height (px)</label>
                                    <input type="number" class="form-control block-data" data-field="height" value="{{ $block['data']['height'] ?? 60 }}">
                                </div>

                                @elseif($block['type'] === 'form')
                                <div class="mb-3">
                                    <label class="form-label">Form ID</label>
                                    <input type="number" class="form-control block-data" data-field="form_id" value="{{ $block['data']['form_id'] ?? '' }}">
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Page Link</h5>
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ url('/lp/' . $page->slug) }}" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <small class="text-muted">Share this link when published.</small>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Design Settings</h5>
                    <form id="settings-form">
                        <div class="mb-3">
                            <label class="form-label">Background Color</label>
                            <input type="color" class="form-control form-control-color" id="settings-bg" value="{{ $page->settings['background_color'] ?? '#ffffff' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Text Color</label>
                            <input type="color" class="form-control form-control-color" id="settings-text" value="{{ $page->settings['text_color'] ?? '#333333' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Container Width</label>
                            <input type="text" class="form-control" id="settings-width" value="{{ $page->settings['container_width'] ?? '800px' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Padding</label>
                            <input type="text" class="form-control" id="settings-padding" value="{{ $page->settings['padding'] ?? '60px 20px' }}">
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" id="save-settings-btn">Save Design</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const pageId = {{ $page->id }};
const csrfToken = '{{ csrf_token() }}';
let blocks = @json($page->content['blocks'] ?? []);

// Save content
async function saveContent() {
    const response = await fetch(`/studio/landing-pages/${pageId}/content`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ content: { blocks } }),
    });
    
    if (response.ok) {
        showToast('Content saved');
    }
}

// Save settings
document.getElementById('save-settings-btn').addEventListener('click', async function() {
    const settings = {
        background_color: document.getElementById('settings-bg').value,
        text_color: document.getElementById('settings-text').value,
        container_width: document.getElementById('settings-width').value,
        padding: document.getElementById('settings-padding').value,
    };
    
    const response = await fetch(`/studio/landing-pages/${pageId}/settings`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ settings }),
    });
    
    if (response.ok) {
        showToast('Settings saved');
    }
});

// Add block
document.querySelectorAll('.add-block').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const type = this.dataset.type;
        const block = {
            id: Date.now().toString(36),
            type: type,
            data: getDefaultBlockData(type),
        };
        blocks.push(block);
        saveContent();
        location.reload();
    });
});

// Delete block
document.querySelectorAll('.btn-delete-block').forEach((btn, index) => {
    btn.addEventListener('click', function() {
        blocks.splice(index, 1);
        saveContent();
        location.reload();
    });
});

// Move block up/down
document.querySelectorAll('.btn-move-up').forEach((btn, index) => {
    btn.addEventListener('click', function() {
        if (index > 0) {
            [blocks[index], blocks[index - 1]] = [blocks[index - 1], blocks[index]];
            saveContent();
            location.reload();
        }
    });
});

document.querySelectorAll('.btn-move-down').forEach((btn, index) => {
    btn.addEventListener('click', function() {
        if (index < blocks.length - 1) {
            [blocks[index], blocks[index + 1]] = [blocks[index + 1], blocks[index]];
            saveContent();
            location.reload();
        }
    });
});

// Block data changes
document.querySelectorAll('.block-data').forEach(input => {
    input.addEventListener('change', function() {
        const blockIndex = this.closest('.block-item').dataset.index;
        const field = this.dataset.field;
        blocks[blockIndex].data[field] = this.value;
        saveContent();
    });
});

// Toggle publish
document.getElementById('publish-btn').addEventListener('click', async function() {
    const response = await fetch(`/studio/landing-pages/${pageId}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
        },
    });
    
    if (response.ok) {
        location.reload();
    }
});

function getDefaultBlockData(type) {
    const defaults = {
        hero: { heading: 'Welcome', subheading: 'Your subheading', background_color: '#667eea', text_color: '#ffffff' },
        text: { content: '<p>Your content here</p>', alignment: 'center' },
        image: { url: '', alt: '' },
        cta: { heading: 'Get Started', button_text: 'Click Here', button_url: '#', button_color: '#667eea' },
        features: { features: '' },
        testimonial: { quote: '', author: '' },
        spacer: { height: 60 },
        form: { form_id: '' },
    };
    return defaults[type] || {};
}

function showToast(message) {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = 'position-fixed bottom-0 end-0 p-3';
    toast.style.zIndex = '9999';
    toast.innerHTML = `<div class="toast show" role="alert"><div class="toast-body">${message}</div></div>`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2000);
}
</script>
@endpush
@endsection
