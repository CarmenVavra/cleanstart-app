<?php

namespace App\Policies;

use App\Models\CopingItem;
use App\Models\User;

class CopingItemPolicy
{
    /**
     * Nur die Besitzerin bzw. der Besitzer darf eigene Einträge löschen.
     */
    public function delete(User $user, CopingItem $copingItem): bool
    {
        return $copingItem->user_id === $user->id;
    }
}
