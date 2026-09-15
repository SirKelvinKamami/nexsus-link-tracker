<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Link;

class DocumentController extends Controller
{
    /**
     * Serve a document file for viewing/download.
     */
    public function serve(Request $request, $id)
    {
        $link = Link::findOrFail($id);

        if ($link->type !== 'document') {
            abort(404);
        }

        $filePath = $link->link;
        $disk = config('documents.storage_disk', 'local');

        if ($disk === 'local') {
            $fullPath = storage_path('app/' . $filePath);
            if (!file_exists($fullPath)) {
                abort(404);
            }

            $typeParams = json_decode($link->type_params, true) ?? [];
            $mimeType = $typeParams['mime_type'] ?? 'application/octet-stream';

            return response()->file($fullPath, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=3600',
            ]);
        } else {
            if (!Storage::disk($disk)->exists($filePath)) {
                abort(404);
            }

            return Storage::disk($disk)->download($filePath);
        }
    }
}
