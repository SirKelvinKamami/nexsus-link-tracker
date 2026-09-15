<form action="{{ route('forms.submit', $form->slug) }}" method="POST" class="row g-3">
    @csrf
    @foreach($form->fields as $field)
    <div class="col-12">
        <label class="form-label">{{ $field->label }} @if($field->is_required) <span class="text-danger">*</span> @endif</label>
        @if(in_array($field->type, ['text', 'email', 'phone', 'url', 'number', 'date']))
        <input type="{{ $field->type === 'phone' ? 'tel' : $field->type }}" 
               class="form-control" 
               name="field_{{ $field->id }}" 
               placeholder="{{ $field->placeholder }}"
               @if($field->is_required) required @endif>
        @elseif($field->type === 'textarea')
        <textarea class="form-control" name="field_{{ $field->id }}" rows="3" placeholder="{{ $field->placeholder }}" @if($field->is_required) required @endif></textarea>
        @elseif($field->type === 'select')
        <select class="form-select" name="field_{{ $field->id }}" @if($field->is_required) required @endif>
            <option value="">Select...</option>
            @foreach($field->options ?? [] as $option)
            <option value="{{ $option }}">{{ $option }}</option>
            @endforeach
        </select>
        @endif
    </div>
    @endforeach
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</form>
