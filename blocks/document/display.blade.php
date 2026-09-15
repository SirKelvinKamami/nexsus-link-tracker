@php
    $typeParams = json_decode($link->type_params, true) ?? [];
    $extension = $typeParams['extension'] ?? pathinfo($link->link, PATHINFO_EXTENSION);
    $originalName = $typeParams['original_name'] ?? $link->title;
    $fileSize = $typeParams['file_size'] ?? 0;
    $description = $typeParams['description'] ?? '';
    $mimeType = $typeParams['mime_type'] ?? '';

    $fileUrl = '/document/' . $link->id;

    $categories = ['jpg'=>'image','jpeg'=>'image','png'=>'image','gif'=>'image','webp'=>'image','pdf'=>'pdf','doc'=>'document','docx'=>'document','xls'=>'spreadsheet','xlsx'=>'spreadsheet','zip'=>'archive'];
    $category = $categories[$extension] ?? 'file';

    $icons = ['image'=>'bi-image','pdf'=>'bi-file-earmark-pdf','document'=>'bi-file-earmark-word','spreadsheet'=>'bi-file-earmark-excel','archive'=>'bi-file-earmark-zip','file'=>'bi-file-earmark'];
    $icon = $icons[$category];

    $colors = ['image'=>'#10b981','pdf'=>'#ef4444','document'=>'#3b82f6','spreadsheet'=>'#22c55e','archive'=>'#f59e0b','file'=>'#6b7280'];
    $color = $colors[$category];

    $formattedSize = $fileSize > 1048576 ? round($fileSize/1048576,1).' MB' : ($fileSize > 1024 ? round($fileSize/1024,1).' KB' : $fileSize.' B');
@endphp

<div class="fadein">
    <a href="/download/{{ $link->id }}" class="document-card" 
       style="display:flex;align-items:center;gap:12px;padding:14px 16px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:12px;text-decoration:none;color:inherit;transition:all 0.2s;margin-bottom:8px;"
       onmouseover="this.style.background='rgba(255,255,255,0.06)';this.style.borderColor='{{ $color }}'"
       onmouseout="this.style.background='rgba(255,255,255,0.03)';this.style.borderColor='rgba(255,255,255,0.06)'">
        
        <div style="width:44px;height:44px;border-radius:10px;background:{{ $color }}20;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="bi {{ $icon }}" style="font-size:20px;color:{{ $color }};"></i>
        </div>
        
        <div style="flex:1;min-width:0;">
            <div style="font-weight:600;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ $link->title }}
            </div>
            @if($description)
            <div style="font-size:12px;opacity:0.6;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;">
                {{ $description }}
            </div>
            @endif
            <div style="font-size:11px;opacity:0.4;margin-top:2px;">
                {{ strtoupper($extension) }} · {{ $formattedSize }}
            </div>
        </div>
        
        <div style="flex-shrink:0;">
            <i class="bi bi-download" style="font-size:16px;opacity:0.5;"></i>
        </div>
    </a>
</div>
