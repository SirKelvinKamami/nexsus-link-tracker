<div class="mb-3">
    <label for="title" class="form-label">Document Title <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="title" name="title" value="{{ $title }}" required>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description (optional)</label>
    <textarea class="form-control" id="description" name="description" rows="2">{{ $params['description'] ?? '' }}</textarea>
</div>

<div class="mb-3">
    <label for="document" class="form-label">Upload Document <span class="text-danger">*</span></label>
    <input type="file" class="form-control" id="document" name="document" 
           accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.jpg,.jpeg,.png,.gif,.webp" required>
    <div class="form-text">
        Allowed: PDF, DOC, DOCX, XLS, XLSX, ZIP, JPG, PNG, GIF, WEBP (max 10MB)
    </div>
</div>

@if(!empty($params['original_name']))
<div class="mb-3">
    <label class="form-label">Current File</label>
    <div class="alert alert-info">
        <i class="bi bi-file-earmark"></i> {{ $params['original_name'] }}
        <span class="text-muted">({{ round(($params['file_size'] ?? 0) / 1024, 1) }}KB)</span>
    </div>
</div>
@endif
