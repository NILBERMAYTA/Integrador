<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            'light' => ['Claro', 'light'],
            'dark' => ['Oscuro', 'dark'],
            'cupcake' => ['Cupcake', 'light'],
            'bumblebee' => ['Bumblebee', 'light'],
            'emerald' => ['Esmeralda', 'light'],
            'corporate' => ['Corporativo', 'light'],
            'synthwave' => ['Synthwave', 'dark'],
            'retro' => ['Retro', 'light'],
            'cyberpunk' => ['Cyberpunk', 'light'],
            'valentine' => ['Valentine', 'light'],
            'halloween' => ['Halloween', 'dark'],
            'garden' => ['Jardin', 'light'],
            'forest' => ['Bosque', 'dark'],
            'aqua' => ['Aqua', 'dark'],
            'lofi' => ['Lo-Fi', 'light'],
            'pastel' => ['Pastel', 'light'],
            'fantasy' => ['Fantasia', 'light'],
            'wireframe' => ['Wireframe', 'light'],
            'black' => ['Negro', 'dark'],
            'luxury' => ['Lujo', 'dark'],
            'dracula' => ['Dracula', 'dark'],
            'cmyk' => ['CMYK', 'light'],
            'autumn' => ['Otono', 'light'],
            'business' => ['Negocios', 'dark'],
            'acid' => ['Acido', 'light'],
            'lemonade' => ['Limonada', 'light'],
            'night' => ['Noche', 'dark'],
            'coffee' => ['Cafe', 'dark'],
            'winter' => ['Invierno', 'light'],
            'dim' => ['Tenue', 'dark'],
            'nord' => ['Nord', 'light'],
            'sunset' => ['Atardecer', 'dark'],
            'caramellatte' => ['Caramel Latte', 'light'],
            'abyss' => ['Abismo', 'dark'],
            'silk' => ['Seda', 'light'],
        ];

        foreach ($themes as $slug => [$name, $appearance]) {
            Theme::withTrashed()->updateOrCreate(
                ['user_id' => null, 'slug' => $slug],
                [
                    'name' => $name,
                    'appearance' => $appearance,
                    'light_palette' => [],
                    'dark_palette' => [],
                    'font_family' => null,
                    'border_radius' => 8,
                    'is_active' => false,
                    'is_system' => true,
                    'deleted_at' => null,
                ]
            );
        }

        $defaultDarkThemeId = Theme::query()
            ->whereNull('user_id')
            ->where('slug', Theme::DEFAULT_DARK_SLUG)
            ->value('id');
        $defaultLightThemeId = Theme::query()
            ->whereNull('user_id')
            ->where('slug', Theme::DEFAULT_LIGHT_SLUG)
            ->value('id');

        DB::table('users')
            ->whereNull('theme_id')
            ->update(['theme_id' => $defaultDarkThemeId]);

        DB::table('users')
            ->whereNull('light_theme_id')
            ->update(['light_theme_id' => $defaultLightThemeId]);

        DB::table('users')
            ->whereNull('dark_theme_id')
            ->update(['dark_theme_id' => $defaultDarkThemeId]);
    }
}
