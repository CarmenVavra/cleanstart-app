<?php

namespace App\Models;

use Database\Factories\BadgeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Meilenstein-Abzeichen, das nach einer bestimmten Anzahl cleaner Tage freigeschaltet wird.
 *
 * @property int $id
 * @property string $title
 * @property string $icon_slug
 * @property string $short_label
 * @property int $hue
 * @property int $days_required
 */
class Badge extends Model
{
    /** @use HasFactory<BadgeFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'icon_slug',
        'short_label',
        'hue',
        'days_required',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hue' => 'integer',
            'days_required' => 'integer',
        ];
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('unlocked_at');
    }

    /**
     * Hintergrundfarbe des freigeschalteten Badges.
     */
    public function color(): string
    {
        return "oklch(0.5 0.09 {$this->hue})";
    }

    /**
     * Farbe des Halos um das freigeschaltete Badge.
     */
    public function haloColor(): string
    {
        return "oklch(0.93 0.03 {$this->hue})";
    }
}
