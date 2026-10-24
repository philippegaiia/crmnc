<?php

namespace App\Models;

use App\Enums\ContactRole;
use App\Enums\Language;
use App\Enums\UserRole;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'first_name',
    'last_name',
    'role',
    'email',
    'phone',
    'is_primary',
    'preferred_language',
    'is_active',
    'notes',
])]
class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory;

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @param  Builder<Contact>  $query
     * @return Builder<Contact>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->role === UserRole::Adherent) {
            return $query->where('company_id', $user->company_id);
        }

        return $query;
    }

    protected function casts(): array
    {
        return [
            'role' => ContactRole::class,
            'preferred_language' => Language::class,
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
