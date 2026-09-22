<?php

declare(strict_types=1);

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Support\AppLocale;
use Database\Factories\PrivacyPolicySectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'title_en',
    'description',
    'description_en',
    'order_column',
    'is_active',
])]
#[ScopedBy([ActiveScope::class])]
class PrivacyPolicySection extends Model
{
    /** @use HasFactory<PrivacyPolicySectionFactory> */
    use HasFactory;

    /**
     * @param  Builder<PrivacyPolicySection>  $query
     * @return Builder<PrivacyPolicySection>
     */
    public function scopeWithInactive(Builder $query): Builder
    {
        return $query->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * @param  Builder<PrivacyPolicySection>  $query
     * @return Builder<PrivacyPolicySection>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order_column')->orderBy('id');
    }

    public function displayTitle(): string
    {
        return $this->localized($this->title, $this->title_en);
    }

    public function displayDescription(): string
    {
        return $this->localized($this->description, $this->description_en);
    }

    private function localized(?string $arabic, ?string $english): string
    {
        if (AppLocale::current() === AppLocale::ENGLISH && filled($english)) {
            return $english;
        }

        return trim((string) $arabic);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_column' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
