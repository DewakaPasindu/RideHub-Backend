<?php

namespace App\Models;

use App\Core\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'documentable_type',
        'documentable_id',
        'document_type',
        'original_name',
        'stored_name',
        'disk',
        'file_path',
        'mime_type',
        'extension',
        'file_size',
        'status',
        'verified_by',
        'verified_at',
        'remarks',
        'uploaded_by',
    ];

    protected $casts = [
        'document_type' => DocumentType::class,
        'status' => DocumentStatus::class,
        'verified_at' => 'datetime',
        'file_size' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Document $document) {
            if (empty($document->uuid)) {
                $document->uuid = (string) Str::uuid();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Polymorphic Relationship
    |--------------------------------------------------------------------------
    */

    public function documentable()
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Reviewer
    |--------------------------------------------------------------------------
    */

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Uploader
    |--------------------------------------------------------------------------
    */

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}