@extends('layouts.sidebar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('showButtons') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Add Document
                    </a>
                </div>
                <h4 class="page-title">Documents</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($documents->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-plus display-1 text-muted"></i>
                        <h5 class="mt-3">No Documents Yet</h5>
                        <p class="text-muted">Upload your first document to get started.</p>
                        <a href="{{ route('showButtons') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Add Document
                        </a>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Downloads</th>
                                    <th>Added</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documents as $doc)
                                @php
                                    $typeParams = json_decode($doc->type_params, true) ?? [];
                                    $extension = $typeParams['extension'] ?? 'file';
                                    $fileSize = $typeParams['file_size'] ?? 0;
                                    $originalName = $typeParams['original_name'] ?? $doc->title;
                                    
                                    $categories = ['jpg'=>'image','jpeg'=>'image','png'=>'image','gif'=>'image','webp'=>'image','pdf'=>'pdf','doc'=>'document','docx'=>'document','xls'=>'spreadsheet','xlsx'=>'spreadsheet','zip'=>'archive'];
                                    $category = $categories[$extension] ?? 'file';
                                    
                                    $icons = ['image'=>'bi-image text-success','pdf'=>'bi-file-earmark-pdf text-danger','document'=>'bi-file-earmark-word text-primary','spreadsheet'=>'bi-file-earmark-excel text-success','archive'=>'bi-file-earmark-zip text-warning','file'=>'bi-file-earmark text-secondary'];
                                    $icon = $icons[$category];
                                    
                                    $formattedSize = $fileSize > 1048576 ? round($fileSize/1048576,1).' MB' : ($fileSize > 1024 ? round($fileSize/1024,1).' KB' : $fileSize.' B');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi {{ $icon }} fs-4 me-3"></i>
                                            <div>
                                                <h6 class="mb-0">{{ $doc->title }}</h6>
                                                <small class="text-muted">{{ $originalName }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ strtoupper($extension) }}</span>
                                    </td>
                                    <td>{{ $formattedSize }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $doc->click_number }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $doc->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/document/{{ $doc->id }}" class="btn btn-outline-primary" target="_blank" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="/download/{{ $doc->id }}" class="btn btn-outline-success" title="Download">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <a href="{{ route('editLink', ['id' => $doc->id]) }}" class="btn btn-outline-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('deleteLink', ['id' => $doc->id]) }}" class="btn btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this document?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
