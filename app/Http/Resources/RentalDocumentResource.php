<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'rental_application_id' => $this->rental_application_id,
            'document_type' => $this->document_type,
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'verification_status' => $this->verification_status,
            'uploaded_at' => $this->uploaded_at ? $this->uploaded_at->toIso8601String() : null,
            'url' => url("/api/v1/rental-documents/{$this->uuid}"),
        ];
    }
}
