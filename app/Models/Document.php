<?php

namespace App\Models;

use App\Enums\DocumentAccessLevel;
use App\Enums\DocumentControlStatus;
use App\Enums\DocumentType;
use App\Enums\IngredientReviewStatus;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * @property int $documentable_id
 * @property class-string<Model> $documentable_type
 * @property DocumentType $type
 */
#[Fillable([
    'type',
    'file_path',
    'original_name',
    'mime_type',
    'size',
    'sha256',
    'version',
    'received_date',
    'issuer',
    'valid_from',
    'valid_until',
    'control_status',
    'verified_by',
    'verified_at',
    'replaces_id',
    'access_level',
])]
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    protected $attributes = [
        'version' => 1,
        'control_status' => DocumentControlStatus::Received->value,
        'access_level' => DocumentAccessLevel::Adherent->value,
    ];

    /** @return MorphTo<Model, $this> */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /** @return BelongsTo<Document, $this> */
    public function replacedDocument(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaces_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'received_date' => 'date',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'control_status' => DocumentControlStatus::class,
            'verified_at' => 'datetime',
            'access_level' => DocumentAccessLevel::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Document $document): void {
            if ($document->belongsToPendingIngredientReview()) {
                throw ValidationException::withMessages([
                    'documents' => 'Les documents soumis ne peuvent pas être modifiés pendant la revue.',
                ]);
            }

            $storedMimeType = $document->getAttribute('mime_type');
            $storedSize = $document->getAttribute('size');
            $storedChecksum = $document->getAttribute('sha256');

            if (is_string($storedMimeType) && is_int($storedSize) && is_string($storedChecksum)) {
                return;
            }

            $disk = Storage::disk('local');

            if (! $disk->exists($document->file_path)) {
                throw ValidationException::withMessages([
                    'file_path' => 'Le fichier privé associé au document est introuvable.',
                ]);
            }

            $mimeType = $disk->mimeType($document->file_path);

            if ($mimeType === false) {
                throw ValidationException::withMessages([
                    'file_path' => 'Le type MIME du document ne peut pas être déterminé.',
                ]);
            }

            $fileSize = $disk->size($document->file_path);

            if ($fileSize < 0) {
                throw ValidationException::withMessages([
                    'file_path' => 'La taille du document ne peut pas être déterminée.',
                ]);
            }

            $document->mime_type = $mimeType;
            $document->size = $fileSize;
            $checksum = hash_file('sha256', $disk->path($document->file_path));

            if ($checksum === false) {
                throw ValidationException::withMessages([
                    'file_path' => "L'empreinte du document ne peut pas être calculée.",
                ]);
            }

            $document->sha256 = $checksum;
        });

        static::updating(function (Document $document): void {
            if ($document->isDirty([
                'type',
                'file_path',
                'original_name',
                'issuer',
                'valid_from',
                'valid_until',
                'replaces_id',
                'access_level',
            ]) && $document->belongsToPendingIngredientReview()) {
                throw ValidationException::withMessages([
                    'documents' => 'Les documents soumis ne peuvent pas être modifiés pendant la revue.',
                ]);
            }
        });

        static::deleting(function (Document $document): void {
            if ($document->belongsToPendingIngredientReview()) {
                throw ValidationException::withMessages([
                    'documents' => 'Les documents soumis ne peuvent pas être supprimés pendant la revue.',
                ]);
            }
        });
    }

    private function belongsToPendingIngredientReview(): bool
    {
        if ($this->documentable_type !== LabIngredient::class) {
            return false;
        }

        return IngredientReview::query()
            ->where('lab_ingredient_id', $this->documentable_id)
            ->whereIn('status', [
                IngredientReviewStatus::Submitted,
                IngredientReviewStatus::UnderReview,
            ])
            ->exists();
    }
}
