<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ToggleAppearanceController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('selectedTheme');
        $targetAppearance = $user->selectedTheme?->appearance === 'dark' ? 'light' : 'dark';
        $preferredThemeId = $targetAppearance === 'dark'
            ? $user->dark_theme_id
            : $user->light_theme_id;

        $theme = $preferredThemeId
            ? Theme::query()
                ->whereKey($preferredThemeId)
                ->where('is_system', true)
                ->where('appearance', $targetAppearance)
                ->first()
            : null;

        if (! $theme) {
            $theme = Theme::query()
                ->where('is_system', true)
                ->where('appearance', $targetAppearance)
                ->where(
                    'slug',
                    $targetAppearance === 'dark'
                        ? Theme::DEFAULT_DARK_SLUG
                        : Theme::DEFAULT_LIGHT_SLUG
                )
                ->first()
                ?? Theme::query()
                    ->where('is_system', true)
                    ->where('appearance', $targetAppearance)
                    ->orderBy('name')
                    ->firstOrFail();
        }

        $user->update([
            'theme_id' => $theme->id,
            "{$targetAppearance}_theme_id" => $theme->id,
        ]);

        return response()->json([
            'theme' => $theme->slug,
            'appearance' => $theme->appearance,
        ]);
    }
}
