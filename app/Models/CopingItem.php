<?php

namespace App\Models;

use App\Enums\CopingType;
use Database\Factories\CopingItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eintrag im persönlichen Notfallplan („Mein Warum“ / „Was mir hilft“).
 *
 * @property int $id
 * @property int $user_id
 * @property CopingType $type
 * @property string $content
 */
class CopingItem extends Model
{
    /** @use HasFactory<CopingItemFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'content',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CopingType::class,
            'content' => 'encrypted',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
