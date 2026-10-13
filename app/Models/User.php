<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $username
 * @property string $email
 * @property Carbon $sobriety_start_at
 * @property string|null $trusted_contact_name
 * @property string|null $trusted_contact_phone
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'sobriety_start_at',
        'trusted_contact_name',
        'trusted_contact_phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'trusted_contact_name',
        'trusted_contact_phone',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'sobriety_start_at' => 'datetime',
            'trusted_contact_name' => 'encrypted',
            'trusted_contact_phone' => 'encrypted',
        ];
    }

    /**
     * @return HasMany<DailyCheckin, $this>
     */
    public function dailyCheckins(): HasMany
    {
        return $this->hasMany(DailyCheckin::class);
    }

    /**
     * @return BelongsToMany<Badge, $this>
     */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class)->withPivot('unlocked_at');
    }

    /**
     * Check-in des heutigen Tages (falls vorhanden).
     */
    public function todaysCheckin(): ?DailyCheckin
    {
        return $this->dailyCheckins()->whereDate('checkin_date', today())->first();
    }

    /**
     * Vergangene Clean-Zeit in Tagen (mit Nachkommastellen, nie negativ).
     */
    public function cleanDays(?Carbon $now = null): float
    {
        $seconds = $this->sobriety_start_at->diffInSeconds($now ?? now(), false);

        return max(0, $seconds) / 86400;
    }

    /**
     * Initiale des Pseudonyms für den Profil-Button.
     */
    public function initial(): string
    {
        return mb_strtoupper(mb_substr($this->username, 0, 1));
    }

    /**
     * Telefonnummer der Vertrauensperson als wählbarer tel:-Link (nur Ziffern und ein führendes +).
     */
    public function trustedContactTelHref(): ?string
    {
        if (blank($this->trusted_contact_phone)) {
            return null;
        }

        $phone = trim($this->trusted_contact_phone);
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        return 'tel:'.(str_starts_with($phone, '+') ? '+' : '').$digits;
    }

    /**
     * Ob eine vollständige Vertrauensperson (Name + wählbare Nummer) hinterlegt ist.
     */
    public function hasTrustedContact(): bool
    {
        return filled($this->trusted_contact_name) && $this->trustedContactTelHref() !== null;
    }
}
