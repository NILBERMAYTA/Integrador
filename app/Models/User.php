<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, LogsActivity, Notifiable, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if ($user->theme_id && $user->light_theme_id && $user->dark_theme_id) {
                return;
            }

            $themes = Theme::query()
                ->whereNull('user_id')
                ->where('is_system', true)
                ->whereIn('slug', [
                    Theme::DEFAULT_LIGHT_SLUG,
                    Theme::DEFAULT_DARK_SLUG,
                ])
                ->pluck('id', 'slug');

            $defaultLightThemeId = $themes->get(Theme::DEFAULT_LIGHT_SLUG);
            $defaultDarkThemeId = $themes->get(Theme::DEFAULT_DARK_SLUG);

            $user->light_theme_id ??= $defaultLightThemeId;
            $user->dark_theme_id ??= $defaultDarkThemeId;
            $user->theme_id ??= $defaultDarkThemeId;
        });
    }

    protected $fillable = [
        'name',
        'cedula',
        'apellido_paterno',
        'apellido_materno',
        'nivel',
        'email',
        'password',
        'role',
        'can_login',
        'rango',
        'rango_codigo',
        'grado_codigo',
        'cargo',
        'numero_escalafon',
        'celular',
        'sigep',
        'salida_haberes_codigo',
        'fecha_ingreso',
        'fecha_nacimiento',
        'post_grado_codigo_1',
        'categoria_codigo',
        'post_grado_codigo_2',
        'marca',
        'expedido',
        'sexo',
        'promocion',
        'foto',
        'unidad_id',
        'destino_id',
        'theme_id',
        'light_theme_id',
        'dark_theme_id',
        'remember_token',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'fecha_ingreso' => 'date',
        'fecha_nacimiento' => 'date',
        'can_login' => 'boolean',
        'role' => 'string',
        'unidad_id' => 'integer',
        'destino_id' => 'integer',
        'theme_id' => 'integer',
        'light_theme_id' => 'integer',
        'dark_theme_id' => 'integer',
    ];

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    public function destino(): BelongsTo
    {
        return $this->belongsTo(Destino::class);
    }

    public function asignacionesUnidad(): HasMany
    {
        return $this->hasMany(UserUnidadAsignacion::class, 'user_id');
    }

    public function unidadActualAsignacion(): HasOne
    {
        return $this->hasOne(UserUnidadAsignacion::class, 'user_id')->latestOfMany('fecha_transferencia');
    }

    public function themes(): HasMany
    {
        return $this->hasMany(Theme::class);
    }

    public function activeTheme(): HasOne
    {
        return $this->hasOne(Theme::class)->where('is_active', true);
    }

    public function selectedTheme(): BelongsTo
    {
        return $this->belongsTo(Theme::class, 'theme_id');
    }

    public function lightTheme(): BelongsTo
    {
        return $this->belongsTo(Theme::class, 'light_theme_id');
    }

    public function darkTheme(): BelongsTo
    {
        return $this->belongsTo(Theme::class, 'dark_theme_id');
    }

    public function getUnidadActualAttribute(): ?Unidad
    {
        return $this->relationLoaded('unidad')
            ? $this->getRelation('unidad')
            : $this->unidad()->first();
    }

    public function getUnidadActualIdAttribute(): ?int
    {
        return $this->unidad_id;
    }

    // Ordena por el apellido completo concatenado
    public function scopeOrderByApellidos(Builder $query, string $direction = 'asc'): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';

        return $query
            ->orderByRaw("LOWER(apellido_paterno) $direction NULLS LAST")
            ->orderByRaw("LOWER(apellido_materno) $direction NULLS LAST");
    }

    /**
     * Obtener el nombre completo del usuario
     */
    public function getNombreCompletoAttribute(): string
    {
        $partes = array_filter([
            $this->name,
            $this->apellido_paterno,
            $this->apellido_materno,
        ]);

        return implode(' ', $partes);
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->isAdministradorGeneral() || $this->isAdministradorUnidad();
    }

    public function isAdministradorGeneral(): bool
    {
        return $this->hasRole('administrador_general');
    }

    public function isAdministradorUnidad(): bool
    {
        return $this->hasRole('administrador_unidad');
    }

    /**
     * Verificar si el usuario es furriel
     */
    public function isFurriel(): bool
    {
        return $this->hasRole('furriel');
    }

    /**
     * Verificar si el usuario es policía
     */
    public function isPolicia(): bool
    {
        return $this->hasRole('policia');
    }

    // Operaciones donde el usuario es el receptor/afectado (policía)
    public function operacionesComoPolicia()
    {
        return $this->hasMany(Operacion::class, 'usuario_destino_id');
    }

    // Operaciones registradas por el usuario (furriel/admin)
    public function operacionesRegistradas()
    {
        return $this->hasMany(Operacion::class, 'actor_id');
    }

    public function mantenimientosCreados()
    {
        return $this->hasMany(Mantenimiento::class, 'creado_por');
    }

    public function inspeccionesRealizadas()
    {
        return $this->hasMany(Inspeccion::class, 'inspector_id');
    }

    public function incidenciasCreadas()
    {
        return $this->hasMany(Incidencia::class, 'creado_por');
    }

    public function incidenciasComoPolicia()
    {
        return $this->hasMany(Incidencia::class, 'policia_id');
    }

    public function scopeForUnidad(Builder $query, ?int $unidadId): Builder
    {
        return $query->when($unidadId, fn (Builder $builder) => $builder->where('unidad_id', $unidadId));
    }

    public function scopeVisibleTo(Builder $query, ?self $actor): Builder
    {
        if (! $actor || $actor->isAdministradorGeneral()) {
            return $query;
        }

        return $query->where('unidad_id', $actor->unidad_id);
    }

    public function initials(): string
    {
        // Toma name o, si no hay, el usuario del email (antes del @)
        $name = trim($this->name ?: (string) str($this->email)->before('@'));

        if ($name === '') {
            return 'U'; // fallback
        }

        // Obtiene 2 iniciales máximo (soporta acentos/UTF-8)
        $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
        $letters = [];
        foreach ($parts as $p) {
            $letters[] = mb_strtoupper(mb_substr($p, 0, 1));
            if (count($letters) === 2) {
                break;
            }
        }

        return implode('', $letters);
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (empty($this->foto) || ! Storage::disk('public')->exists($this->foto)) {
            return null;
        }

        return Storage::disk('public')->url($this->foto);
    }

    /**
     * Get the activity log options for the model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly([
                'updated_at',
                'remember_token',
            ])
            ->setDescriptionForEvent(fn (string $event) => "Usuario fue {$event}"
            );
    }
}
