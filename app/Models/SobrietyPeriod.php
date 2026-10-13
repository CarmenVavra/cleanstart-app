<?php

namespace App\Models;

use App\Enums\Trigger;
use Database\Factories\SobrietyPeriodFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Abgeschlossene Clean-Serie (endet mit einem Neustart nach einem Rückfall).
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon $started_at
 * @property Carbon $ended_at
 * @property string|null $reflection_encrypted
 * @property array<int, string> $triggers
 */
class SobrietyPeriod extends Model
{
    /** @use HasFactory<SobrietyPeriodFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'started_at',
        'ended_at',
        'reflection_encrypted',
        'triggers',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'reflection_encrypted' => 'encrypted',
            'triggers' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Volle Tage dieser Serie.
     */
    public function days(): int
    {
        return intdiv((int) max(0, $this->started_at->diffInSeconds($this->ended_at, false)), 86400);
    }

    /**
     * Deutsche Labels der notierten Trigger.
     *
     * @return list<string>
     */
    public function triggerLabels(): array
    {
        return Trigger::labelsFor($this->triggers);
    }
}
