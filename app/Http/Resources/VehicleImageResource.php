<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,

            'image_type' => $this->image_type,

            'original_name' => $this->original_name,

            'stored_name' => $this->stored_name,

            'disk' => $this->disk,

            'file_path' => $this->file_path,

            'mime_type' => $this->mime_type,

            'extension' => $this->extension,

            'file_size' => $this->file_size,

            'is_primary' => $this->is_primary,

            'sort_order' => $this->sort_order,

            'uploaded_by' => $this->uploaded_by,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,
        ];
    }
}