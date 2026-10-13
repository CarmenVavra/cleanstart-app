<?php

namespace Tests\Feature;

use App\Enums\CopingType;
use App\Models\CopingItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CopingPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_page_renders_both_sections(): void
    {
        $user = User::factory()->create();
        CopingItem::factory()->for($user)->reason()->create(['content' => 'Für meinen Sohn']);

        $this->actingAs($user)
            ->get(route('coping.index'))
            ->assertOk()
            ->assertSee('Mein Warum')
            ->assertSee('Was mir hilft')
            ->assertSee('Für meinen Sohn')
            ->assertSee('1 von 10');
    }

    public function test_items_can_be_added_and_are_encrypted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('coping.store'), ['type' => 'strategy', 'content' => '  Kalt duschen  '])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('coping.index'));

        $item = $user->copingItems()->sole();
        $this->assertSame(CopingType::Strategy, $item->type);
        $this->assertSame('Kalt duschen', $item->content);
        $this->assertStringNotContainsString('duschen', (string) DB::table('coping_items')->value('content'));
    }

    public function test_validation_errors_are_scoped_to_their_section(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('coping.store'), ['type' => 'reason', 'content' => ''])
            ->assertSessionHasErrorsIn('reason', 'content');

        $this->actingAs($user)
            ->post(route('coping.store'), ['type' => 'invalid', 'content' => 'x'])
            ->assertSessionHasErrors('type');

        $this->assertSame(0, CopingItem::count());
    }

    public function test_each_section_is_limited(): void
    {
        $user = User::factory()->create();
        CopingItem::factory()->for($user)->strategy()->count(CopingType::MAX_ITEMS)->create();

        $this->actingAs($user)
            ->post(route('coping.store'), ['type' => 'strategy', 'content' => 'Noch einer'])
            ->assertSessionHasErrorsIn('strategy', 'content');

        $this->assertSame(CopingType::MAX_ITEMS, $user->copingItems()->count());
    }

    public function test_users_can_delete_their_own_items_only(): void
    {
        $user = User::factory()->create();
        $own = CopingItem::factory()->for($user)->create();
        $foreign = CopingItem::factory()->for(User::factory())->create();

        $this->actingAs($user)->delete(route('coping.destroy', $foreign))->assertForbidden();
        $this->actingAs($user)->delete(route('coping.destroy', $own))->assertRedirect(route('coping.index'));

        $this->assertModelMissing($own);
        $this->assertModelExists($foreign);
    }

    public function test_emergency_sheet_shows_the_personal_plan(): void
    {
        $user = User::factory()->create();
        CopingItem::factory()->for($user)->reason()->create(['content' => 'Für meinen Sohn']);
        CopingItem::factory()->for($user)->strategy()->create(['content' => 'Kalt duschen']);
        CopingItem::factory()->for(User::factory())->reason()->create(['content' => 'Fremder Eintrag']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSee('Dein Warum')
            ->assertSee('Für meinen Sohn')
            ->assertSee('Was dir jetzt helfen kann')
            ->assertSee('Kalt duschen')
            ->assertDontSee('Fremder Eintrag')
            ->assertDontSee('Notfallplan anlegen');
    }

    public function test_emergency_sheet_invites_to_create_a_plan_when_empty(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertSee('Notfallplan anlegen');
    }
}
