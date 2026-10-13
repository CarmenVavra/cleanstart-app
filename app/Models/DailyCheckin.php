<?php

namespace App\Models;

use App\Enums\Mood;
use App\Enums\Trigger;
use Database\Factories\DailyCheckinFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property Mood $mood_rating
 * @property array<int, string> $triggers
 * @property string|null $notes_encrypted
 * @property Carbon $checkin_date
 */
class DailyCheckin extends Model
{
    /** @use HasFactory<DailyCheckinFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'mood_rating',
        'triggers',
        'notes_encrypted',
        'checkin_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mood_rating' => Mood::class,
            'triggers' => 'array',
            'notes_encrypted' => 'encrypted',
            'checkin_date' => 'date',
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
     * Deutsche Labels der notierten Trigger.
     *
     * @return list<string>
     */
    public function triggerLabels(): array
    {
        return Trigger::labelsFor($this->triggers);
    }
}
