<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\Appearance;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AppearanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_receive_the_default_light_and_dark_themes(): void
    {
        $light = $this->createTheme(Theme::DEFAULT_LIGHT_SLUG, 'light');
        $dark = $this->createTheme(Theme::DEFAULT_DARK_SLUG, 'dark');

        $user = User::factory()->create();

        $this->assertSame($dark->id, $user->theme_id);
        $this->assertSame($light->id, $user->light_theme_id);
        $this->assertSame($dark->id, $user->dark_theme_id);
    }

    public function test_user_can_configure_a_theme_for_each_appearance(): void
    {
        $light = $this->createTheme('cupcake', 'light');
        $dark = $this->createTheme('forest', 'dark');
        $user = User::factory()->create([
            'theme_id' => $dark->id,
            'light_theme_id' => null,
            'dark_theme_id' => $dark->id,
        ]);

        Livewire::actingAs($user)
            ->test(Appearance::class)
            ->call('selectTheme', $light->slug)
            ->assertSet('selectedTheme', $light->slug)
            ->assertSet('lightTheme', $light->slug)
            ->assertDispatched('theme-changed', theme: $light->slug, appearance: 'light');

        $user->refresh();

        $this->assertSame($light->id, $user->theme_id);
        $this->assertSame($light->id, $user->light_theme_id);
        $this->assertSame($dark->id, $user->dark_theme_id);
    }

    public function test_sidebar_toggle_uses_the_configured_theme_for_the_opposite_mode(): void
    {
        $light = $this->createTheme('cupcake', 'light');
        $dark = $this->createTheme('dracula', 'dark');
        $user = User::factory()->create([
            'theme_id' => $dark->id,
            'light_theme_id' => $light->id,
            'dark_theme_id' => $dark->id,
        ]);

        $this->actingAs($user)
            ->postJson(route('settings.appearance.toggle'))
            ->assertOk()
            ->assertJson([
                'theme' => 'cupcake',
                'appearance' => 'light',
            ]);

        $this->assertSame($light->id, $user->refresh()->theme_id);
    }

    private function createTheme(string $slug, string $appearance): Theme
    {
        return Theme::query()->create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'appearance' => $appearance,
            'light_palette' => [],
            'dark_palette' => [],
            'border_radius' => 8,
            'is_active' => false,
            'is_system' => true,
        ]);
    }
}
