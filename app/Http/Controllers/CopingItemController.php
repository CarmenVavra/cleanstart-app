<?php

namespace App\Http\Controllers;

use App\Enums\CopingType;
use App\Http\Requests\StoreCopingItemRequest;
use App\Models\CopingItem;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Persönlicher Notfallplan: „Mein Warum“ und „Was mir hilft“.
 */
class CopingItemController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $itemsByType = $user->copingItems()->orderBy('id')->get()->groupBy(fn (CopingItem $item) => $item->type->value);
        $types = CopingType::cases();

        return view('coping.index', compact('itemsByType', 'types'));
    }

    public function store(StoreCopingItemRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $type = $request->type();

        $user->copingItems()->create([
            'type' => $type,
            'content' => trim($request->string('content')->toString()),
        ]);

        return redirect()->route('coping.index')->with('status', "Eintrag zu „{$type->label()}“ gespeichert.");
    }

    public function destroy(CopingItem $copingItem): RedirectResponse
    {
        Gate::authorize('delete', $copingItem);

        $copingItem->delete();

        return redirect()->route('coping.index')->with('status', 'Eintrag gelöscht.');
    }
}
