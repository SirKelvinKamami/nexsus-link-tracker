@extends('studio.layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ url('/f/' . $form->slug) }}" class="btn btn-outline-primary" target="_blank">
                        <i class="bi bi-eye"></i> Preview
                    </a>
                    <a href="{{ route('forms.responses', $form->id) }}" class="btn btn-outline-info">
                        <i class="bi bi-list-check"></i> Responses ({{ $form->responses()->count() }})
                    </a>
                </div>
                <h4 class="page-title">Edit Form: {{ $form->title }}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Form Settings</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('forms.update', $form->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="title" class="form-label">Form Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ $form->title }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2">{{ $form->description }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ $form->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="collect_email" name="collect_email" value="1" {{ $form->collect_email ? 'checked' : '' }}>
                                    <label class="form-check-label" for="collect_email">Collect Email</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="one_response_per_ip" name="one_response_per_ip" value="1" {{ $form->one_response_per_ip ? 'checked' : '' }}>
                                    <label class="form-check-label" for="one_response_per_ip">One Response/IP</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm mt-2">Save Settings</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Form Fields</h5>
                </div>
                <div class="card-body">
                    <div id="fields-container">
                        @foreach($form->fields as $field)
                        <div class="field-item card mb-3" data-field-id="{{ $field->id }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">{{ $field->label }}</h6>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-warning btn-edit-field" data-field='@json($field)'>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-delete-field" data-field-id="{{ $field->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="text-muted small">
                                    <span class="badge bg-light text-dark">{{ $field->type }}</span>
                                    @if($field->is_required)
                                    <span class="badge bg-danger">Required</span>
                                    @endif
                                    @if($field->placeholder)
                                    <span>Placeholder: {{ $field->placeholder }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <hr>

                    <h6>Add New Field</h6>
                    <form id="add-field-form">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Label</label>
                                <input type="text" class="form-control" id="field-label" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type</label>
                                <select class="form-select" id="field-type">
                                    <option value="text">Text</option>
                                    <option value="email">Email</option>
                                    <option value="phone">Phone</option>
                                    <option value="url">URL</option>
                                    <option value="number">Number</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="select">Select</option>
                                    <option value="radio">Radio</option>
                                    <option value="checkbox">Checkbox</option>
                                    <option value="date">Date</option>
                                    <option value="file">File</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Placeholder</label>
                                <input type="text" class="form-control" id="field-placeholder">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Default Value</label>
                                <input type="text" class="form-control" id="field-default">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="field-required">
                                    <label class="form-check-label" for="field-required">Required</label>
                                </div>
                            </div>
                            <div class="col-12" id="options-container" style="display:none;">
                                <label class="form-label">Options (one per line)</label>
                                <textarea class="form-control" id="field-options" rows="3" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Add Field</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Link</h5>
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ url('/f/' . $form->slug) }}" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <small class="text-muted">Share this link to collect responses.</small>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Quick Stats</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span>Total Responses</span>
                            <strong>{{ $form->responses()->count() }}</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span>Today</span>
                            <strong>{{ $form->responses()->whereDate('created_at', now())->count() }}</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span>Fields</span>
                            <strong>{{ $form->fields()->count() }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Field Modal -->
<div class="modal fade" id="editFieldModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Field</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-field-form">
                    <input type="hidden" id="edit-field-id">
                    <div class="mb-3">
                        <label class="form-label">Label</label>
                        <input type="text" class="form-control" id="edit-field-label" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select" id="edit-field-type">
                            <option value="text">Text</option>
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                            <option value="url">URL</option>
                            <option value="number">Number</option>
                            <option value="textarea">Textarea</option>
                            <option value="select">Select</option>
                            <option value="radio">Radio</option>
                            <option value="checkbox">Checkbox</option>
                            <option value="date">Date</option>
                            <option value="file">File</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Placeholder</label>
                        <input type="text" class="form-control" id="edit-field-placeholder">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Default Value</label>
                        <input type="text" class="form-control" id="edit-field-default">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit-field-required">
                            <label class="form-check-label" for="edit-field-required">Required</label>
                        </div>
                    </div>
                    <div class="mb-3" id="edit-options-container" style="display:none;">
                        <label class="form-label">Options (one per line)</label>
                        <textarea class="form-control" id="edit-field-options" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-field-btn">Save Field</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const formId = {{ $form->id }};
const csrfToken = '{{ csrf_token() }}';

document.getElementById('field-type').addEventListener('change', function() {
    const optionsContainer = document.getElementById('options-container');
    if (['select', 'radio', 'checkbox'].includes(this.value)) {
        optionsContainer.style.display = 'block';
    } else {
        optionsContainer.style.display = 'none';
    }
});

document.getElementById('add-field-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const data = {
        label: document.getElementById('field-label').value,
        type: document.getElementById('field-type').value,
        placeholder: document.getElementById('field-placeholder').value,
        default_value: document.getElementById('field-default').value,
        is_required: document.getElementById('field-required').checked,
    };
    
    if (['select', 'radio', 'checkbox'].includes(data.type)) {
        const optionsText = document.getElementById('field-options').value;
        data.options = optionsText.split('\n').filter(o => o.trim());
    }
    
    const response = await fetch(`/studio/forms/${formId}/fields`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(data),
    });
    
    if (response.ok) {
        location.reload();
    }
});

document.querySelectorAll('.btn-edit-field').forEach(btn => {
    btn.addEventListener('click', function() {
        const field = JSON.parse(this.dataset.field);
        document.getElementById('edit-field-id').value = field.id;
        document.getElementById('edit-field-label').value = field.label;
        document.getElementById('edit-field-type').value = field.type;
        document.getElementById('edit-field-placeholder').value = field.placeholder || '';
        document.getElementById('edit-field-default').value = field.default_value || '';
        document.getElementById('edit-field-required').checked = field.is_required;
        
        if (field.options) {
            document.getElementById('edit-field-options').value = field.options.join('\n');
            document.getElementById('edit-options-container').style.display = 'block';
        }
        
        new bootstrap.Modal(document.getElementById('editFieldModal')).show();
    });
});

document.getElementById('edit-field-type').addEventListener('change', function() {
    const optionsContainer = document.getElementById('edit-options-container');
    if (['select', 'radio', 'checkbox'].includes(this.value)) {
        optionsContainer.style.display = 'block';
    } else {
        optionsContainer.style.display = 'none';
    }
});

document.getElementById('save-field-btn').addEventListener('click', async function() {
    const fieldId = document.getElementById('edit-field-id').value;
    const data = {
        label: document.getElementById('edit-field-label').value,
        type: document.getElementById('edit-field-type').value,
        placeholder: document.getElementById('edit-field-placeholder').value,
        default_value: document.getElementById('edit-field-default').value,
        is_required: document.getElementById('edit-field-required').checked,
    };
    
    if (['select', 'radio', 'checkbox'].includes(data.type)) {
        const optionsText = document.getElementById('edit-field-options').value;
        data.options = optionsText.split('\n').filter(o => o.trim());
    }
    
    const response = await fetch(`/studio/forms/${formId}/fields/${fieldId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(data),
    });
    
    if (response.ok) {
        location.reload();
    }
});

document.querySelectorAll('.btn-delete-field').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!confirm('Are you sure you want to delete this field?')) return;
        
        const fieldId = this.dataset.fieldId;
        const response = await fetch(`/studio/forms/${formId}/fields/${fieldId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
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
