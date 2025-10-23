<?php

use App\Livewire\Categorias\Create as CategoriasCreate;
use App\Livewire\Categorias\Delete as CategoriasDelete;
use App\Livewire\Categorias\Index as CategoriasIndex;
use App\Livewire\Categorias\Update as CategoriasUpdate;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Users\Create;
use App\Livewire\Users\Delete;
use App\Livewire\Users\Index;
use App\Livewire\Users\Update;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('users', Index::class)->name('users.index');
    Route::get('users/create', Create::class)->name('users.create');
    Route::get('users/{user}/update', Update::class)->name('users.update');
    Route::get('users/deleted', Delete::class)->name('users.delete.index');

    Route::get('categorias', CategoriasIndex::class)->name('categorias.index');
    Route::get('categorias/create', CategoriasCreate::class)->name('categorias.create');
    Route::get('categorias/{categoria}/update', CategoriasUpdate::class)->name('categorias.update');
    Route::get('categorias/deleted', CategoriasDelete::class)->name('categorias.delete.index');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

require __DIR__.'/auth.php';
