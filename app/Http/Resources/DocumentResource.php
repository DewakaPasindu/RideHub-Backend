<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,

            'document_type' => $this->document_type?->value,

            'original_name' => $this->original_name,

            'stored_name' => $this->stored_name,

            'disk' => $this->disk,

            'file_path' => $this->file_path,

            'mime_type' => $this->mime_type,

            'extension' => $this->extension,

            'file_size' => $this->file_size,

            'status' => $this->status?->value,

            'verified_at' => $this->verified_at,

            'remarks' => $this->remarks,

            'uploaded_by' => $this->uploaded_by,

            'verified_by' => $this->verified_by,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}