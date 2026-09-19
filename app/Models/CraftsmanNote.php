<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CraftsmanStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['craftsman_id', 'status', 'note', 'user_id'])]
class CraftsmanNote extends Model
{
    /**
     * @return BelongsTo<Craftsman, $this>
     */
    public function craftsman(): BelongsTo
    {
        return $this->belongsTo(Craftsman::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CraftsmanStatus::class,
        ];
    }
}
