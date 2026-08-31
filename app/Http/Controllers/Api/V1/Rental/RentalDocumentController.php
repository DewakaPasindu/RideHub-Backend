<?php

namespace App\Http\Controllers\Api\V1\Rental;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\RentalDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RentalDocumentController extends BaseApiController
{
    /**
     * Securely stream verification documents from local storage.
     */
    public function show(string $uuid)
    {
        $doc = RentalDocument::with(['rentalApplication'])->where('uuid', $uuid)->first();

        if (!$doc) {
            return abort(404, 'Document not found.');
        }

        // Authorize viewing the document using the parent application policy
        if (auth()->user()->cannot('view', $doc->rentalApplication)) {
            return abort(403, 'Unauthorized to view this document.');
        }

        if (!Storage::disk('local')->exists($doc->document_path)) {
            return abort(404, 'Document file not found in storage.');
        }

        $fullPath = Storage::disk('local')->path($doc->document_path);

        return response()->file($fullPath, [
            'Content-Type' => $doc->mime_type,
            'Content-Disposition' => 'inline; filename="' . $doc->original_filename . '"',
        ]);
    }
}
