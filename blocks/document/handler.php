<?php

use Illuminate\Support\Facades\Storage;

function handleLinkType($request, $linkType)
{
    $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'document' => 'required|file',
    ];

    $linkData = [];

    if ($request->hasFile('document')) {
        $file = $request->file('document');
        $extension = strtolower($file->getClientOriginalExtension());

        $allowedExtensions = config('documents.allowed_extensions', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip']);
        if (!in_array($extension, $allowedExtensions)) {
            return [
                'rules' => ['document' => 'in:' . implode(',', $allowedExtensions)],
                'linkData' => [],
            ];
        }

        $maxSize = config('documents.max_file_size', 10240) * 1024;
        if ($file->getSize() > $maxSize) {
            $maxMb = config('documents.max_file_size', 10240) / 1024;
            return [
                'rules' => ['document' => "max:{$maxMb}"],
                'linkData' => [],
            ];
        }

        $disk = config('documents.storage_disk', 'local');
        $userId = auth()->id();
        $storedName = \Illuminate\Support\Str::uuid() . '.' . $extension;
        $path = "documents/{$userId}/{$storedName}";

        $file->storeAs("documents/{$userId}", $storedName, $disk);

        $linkData = [
            'link' => $path,
            'title' => $request->input('title'),
            'button_id' => 97,
            'type_params' => json_encode([
                'description' => $request->input('description', ''),
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'extension' => $extension,
            ]),
        ];
    }

    return [
        'rules' => $rules,
        'linkData' => $linkData,
    ];
}
