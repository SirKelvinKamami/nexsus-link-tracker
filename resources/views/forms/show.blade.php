<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $form->title }} — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .form-card {
            max-width: 600px;
            width: 100%;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        .form-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem 1rem 0 0;
        }
        .form-body {
            padding: 2rem;
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem 2rem;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card form-card mx-auto">
            <div class="form-header">
                <h3 class="mb-2">{{ $form->title }}</h3>
                @if($form->description)
                <p class="mb-0 opacity-75">{{ $form->description }}</p>
                @endif
            </div>
            <div class="form-body">
                @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
                @endif

                <form action="{{ route('forms.submit', $form->slug) }}" method="POST">
                    @csrf

                    @if($form->collect_email)
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif

                    @foreach($form->fields as $field)
                    <div class="mb-3">
                        <label for="field_{{ $field->id }}" class="form-label">
                            {{ $field->label }}
                            @if($field->is_required)
                            <span class="text-danger">*</span>
                            @endif
                        </label>

                        @if($field->type === 'text' || $field->type === 'phone' || $field->type === 'url')
                        <input type="{{ $field->type === 'phone' ? 'tel' : $field->type }}" 
                               class="form-control @error('field_'.$field->id) is-invalid @enderror" 
                               id="field_{{ $field->id }}" 
                               name="field_{{ $field->id }}" 
                               value="{{ old('field_'.$field->id, $field->default_value) }}"
                               placeholder="{{ $field->placeholder }}"
                               @if($field->is_required) required @endif>
                        
                        @elseif($field->type === 'email')
                        <input type="email" 
                               class="form-control @error('field_'.$field->id) is-invalid @enderror" 
                               id="field_{{ $field->id }}" 
                               name="field_{{ $field->id }}" 
                               value="{{ old('field_'.$field->id, $field->default_value) }}"
                               placeholder="{{ $field->placeholder }}"
                               @if($field->is_required) required @endif>
                        
                        @elseif($field->type === 'number')
                        <input type="number" 
                               class="form-control @error('field_'.$field->id) is-invalid @enderror" 
                               id="field_{{ $field->id }}" 
                               name="field_{{ $field->id }}" 
                               value="{{ old('field_'.$field->id, $field->default_value) }}"
                               placeholder="{{ $field->placeholder }}"
                               @if($field->is_required) required @endif>
                        
                        @elseif($field->type === 'date')
                        <input type="date" 
                               class="form-control @error('field_'.$field->id) is-invalid @enderror" 
                               id="field_{{ $field->id }}" 
                               name="field_{{ $field->id }}" 
                               value="{{ old('field_'.$field->id, $field->default_value) }}"
                               @if($field->is_required) required @endif>
                        
                        @elseif($field->type === 'textarea')
                        <textarea class="form-control @error('field_'.$field->id) is-invalid @enderror" 
                                  id="field_{{ $field->id }}" 
                                  name="field_{{ $field->id }}" 
                                  rows="4"
                                  placeholder="{{ $field->placeholder }}"
                                  @if($field->is_required) required @endif>{{ old('field_'.$field->id, $field->default_value) }}</textarea>
                        
                        @elseif($field->type === 'select')
                        <select class="form-select @error('field_'.$field->id) is-invalid @enderror" 
                                id="field_{{ $field->id }}" 
                                name="field_{{ $field->id }}"
                                @if($field->is_required) required @endif>
                            <option value="">Select an option...</option>
                            @foreach($field->options ?? [] as $option)
                            <option value="{{ $option }}" {{ old('field_'.$field->id) == $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                            @endforeach
                        </select>
                        
                        @elseif($field->type === 'radio')
                        <div>
                            @foreach($field->options ?? [] as $option)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" 
                                       name="field_{{ $field->id }}" 
                                       id="field_{{ $field->id }}_{{ $loop->index }}" 
                                       value="{{ $option }}"
                                       {{ old('field_'.$field->id) == $option ? 'checked' : '' }}
                                       @if($field->is_required) required @endif>
                                <label class="form-check-label" for="field_{{ $field->id }}_{{ $loop->index }}">
                                    {{ $option }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        
                        @elseif($field->type === 'checkbox')
                        <div>
                            @foreach($field->options ?? [] as $option)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="field_{{ $field->id }}[]" 
                                       id="field_{{ $field->id }}_{{ $loop->index }}" 
                                       value="{{ $option }}"
                                       {{ in_array($option, old('field_'.$field->id, [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="field_{{ $field->id }}_{{ $loop->index }}">
                                    {{ $option }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        
                        @elseif($field->type === 'file')
                        <input type="file" 
                               class="form-control @error('field_'.$field->id) is-invalid @enderror" 
                               id="field_{{ $field->id }}" 
                               name="field_{{ $field->id }}"
                               @if($field->is_required) required @endif>
                        @endif

                        @error('field_'.$field->id)
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @endforeach

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center mt-4">
            <small class="text-muted">
                Powered by <a href="{{ url('/') }}">{{ config('app.name') }}</a>
            </small>
        </div>
    </div>
</body>
</html>
