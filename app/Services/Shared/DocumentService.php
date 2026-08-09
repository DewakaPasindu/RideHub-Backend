<?php

namespace App\Services\Shared;

use App\Core\Enums\DocumentStatus;
use App\Models\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    /**
     * Upload a document for a polymorphic model.
     */
    public function upload(
        Model $documentable,
        UploadedFile $file,
        string $documentType,
        int $uploadedBy
    ): Document {
        return DB::transaction(function () use (
            $documentable,
            $file,
            $documentType,
            $uploadedBy
        ) {
            $extension = strtolower($file->getClientOriginalExtension());

            $storedName = Str::uuid() . '.' . $extension;

            $directory = 'documents/' .
                Str::snake(class_basename($documentable)) .
                '/' .
                $documentable->getKey();

            $filePath = $file->storeAs(
                $directory,
                $storedName,
                'public'
            );

            return Document::create([
                'documentable_type' => $documentable->getMorphClass(),
                'documentable_id' => $documentable->getKey(),

                'document_type' => $documentType,

                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $storedName,

                'disk' => 'public',
                'file_path' => $filePath,

                'mime_type' => $file->getMimeType(),
                'extension' => $extension,
                'file_size' => $file->getSize(),

                'status' => DocumentStatus::PENDING->value,

                'uploaded_by' => $uploadedBy,
            ]);
        });
    }

    /**
     * Update document status and remarks.
     */
    public function update(
        Document $document,
        array $data,
        ?int $verifiedBy = null
    ): Document {
        return DB::transaction(function () use (
            $document,
            $data,
            $verifiedBy
        ) {
            $status = $data['status'] ?? null;

            $document->fill([
                'status' => $status,
                'remarks' => $data['remarks'] ?? $document->remarks,
            ]);

            if ($status === DocumentStatus::VERIFIED->value) {
                $document->verified_by = $verifiedBy;
                $document->verified_at = now();
            }

            if ($status === DocumentStatus::REJECTED->value) {
                $document->verified_by = $verifiedBy;
                $document->verified_at = now();
            }

            $document->save();

            return $document->fresh();
        });
    }

    /**
     * Delete a document and its stored file.
     */
    public function delete(Document $document): bool
    {
        return DB::transaction(function () use ($document) {
            if ($document->file_path) {
                Storage::disk($document->disk)->delete(
                    $document->file_path
                );
            }

            return (bool) $document->delete();
        });
    }
}