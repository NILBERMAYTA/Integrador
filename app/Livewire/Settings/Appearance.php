<?php

namespace App\Livewire\Settings;

use App\Models\Theme;
use Livewire\Component;

class Appearance extends Component
{
    public string $selectedTheme = Theme::DEFAULT_DARK_SLUG;

    public string $lightTheme = Theme::DEFAULT_LIGHT_SLUG;

    public string $darkTheme = Theme::DEFAULT_DARK_SLUG;

    public function mount(): void
    {
        $user = auth()->user()->loadMissing(['selectedTheme', 'lightTheme', 'darkTheme']);

        $this->selectedTheme = $user->selectedTheme?->slug ?? Theme::DEFAULT_DARK_SLUG;
        $this->lightTheme = $user->lightTheme?->slug ?? Theme::DEFAULT_LIGHT_SLUG;
        $this->darkTheme = $user->darkTheme?->slug ?? Theme::DEFAULT_DARK_SLUG;
    }

    public function selectTheme(string $slug): void
    {
        $theme = Theme::query()
            ->where('slug', $slug)
            ->where('is_system', true)
            ->firstOrFail();

        auth()->user()->update([
            'theme_id' => $theme->id,
            "{$theme->appearance}_theme_id" => $theme->id,
        ]);

        $this->selectedTheme = $theme->slug;
        $this->{$theme->appearance.'Theme'} = $theme->slug;

        $this->dispatch('theme-changed', theme: $theme->slug, appearance: $theme->appearance);
        session()->flash('success', "Tema {$theme->name} configurado para el modo {$theme->appearance}.");
    }

    public function render()
    {
        return view('livewire.settings.appearance', [
            'themes' => Theme::query()
                ->where('is_system', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'appearance'])
                ->groupBy('appearance'),
        ]);
    }
}
