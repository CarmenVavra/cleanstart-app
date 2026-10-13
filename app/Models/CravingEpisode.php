<?php

namespace App\Models;

use Database\Factories\CravingEpisodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Ein mit dem Craving-Timer überstandener Suchtdruck.
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon $overcome_at
 */
class CravingEpisode extends Model
{
    /** @use HasFactory<CravingEpisodeFactory> */
    use HasFactory;

    /** Dauer des Craving-Timers: Suchtdruck flacht meist nach 15–20 Minuten ab. */
    public const TIMER_MINUTES = 15;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'overcome_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'overcome_at' => 'datetime',
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
