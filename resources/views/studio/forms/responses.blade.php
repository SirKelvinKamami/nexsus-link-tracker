@extends('layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('forms.export', $form->id) }}" class="btn btn-success">
                        <i class="bi bi-download"></i> Export CSV
                    </a>
                    <a href="{{ route('forms.edit', $form->id) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil"></i> Edit Form
                    </a>
                </div>
                <h4 class="page-title">Responses: {{ $form->title }}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($responses->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h5 class="mt-3">No Responses Yet</h5>
                        <p class="text-muted">Share your form to start collecting responses.</p>
                        <code>{{ url('/f/' . $form->slug) }}</code>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Submitted</th>
                                    @if($form->collect_email)
                                    <th>Email</th>
                                    @endif
                                    @foreach($form->fields as $field)
                                    <th>{{ $field->label }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($responses as $response)
                                <tr>
                                    <td>{{ $response->id }}</td>
                                    <td>
                                        <small>{{ $response->created_at->format('M d, Y H:i') }}</small>
                                    </td>
                                    @if($form->collect_email)
                                    <td>{{ $response->email }}</td>
                                    @endif
                                    @foreach($form->fields as $field)
                                    <td>
                                        {{ $response->answers[$field->label] ?? '-' }}
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $responses->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
