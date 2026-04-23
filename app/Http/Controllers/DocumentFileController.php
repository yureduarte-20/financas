<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DocumentFileController extends Controller
{
    public function show(Request $request, Document $document): Response
    {
        abort_unless($document->user_id === $request->user()->id, 403);

        $ext = strtolower(pathinfo($document->name, PATHINFO_EXTENSION) ?: 'bin');
        $relativePath = 'documents/'.$document->user_id.'/'.$document->id.'.'.$ext;

        if (! Storage::disk('local')->exists($relativePath)) {
            abort(404);
        }

        $mime = match ($ext) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream',
        };

        $absolutePath = Storage::disk('local')->path($relativePath);

        return response()->file($absolutePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$document->name.'"',
        ]);
    }
}
