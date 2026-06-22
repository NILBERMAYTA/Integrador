<?php

namespace App\Services\Chatbot;

use App\Models\Articulo;
use App\Models\ArticuloSerie;
use App\Models\Incidencia;
use App\Models\InventarioUnidadArticulo;
use App\Models\Mantenimiento;
use App\Models\Operacion;
use App\Models\Unidad;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChatbotService
{
    public function __construct(
        private readonly ChatbotIntentEngine $intents,
    ) {
    }

    public function answer(string $message, User $user): ChatbotResponse
    {
        $detected = $this->intents->detect($message);

        return match ($detected->intent) {
            ChatbotIntent::ArticleAvailability => $this->articleAvailability($detected, $user),
            ChatbotIntent::ArticlesInMaintenance => $this->articlesInMaintenance($detected, $user),
            ChatbotIntent::ActiveLoans => $this->activeLoans($detected, $user),
            ChatbotIntent::ReturnsWithIncidents => $this->returnsWithIncidents($detected, $user),
            ChatbotIntent::MostBorrowed => $this->mostBorrowed($detected, $user),
            ChatbotIntent::MaintenanceDue => $this->maintenanceDue($user),
            ChatbotIntent::InventorySummary => $this->inventorySummary($user),
            ChatbotIntent::LowStock => $this->lowStock($user),
            ChatbotIntent::Incidents => $this->incidents($detected, $user),
            ChatbotIntent::OperationSummary => $this->operationSummary($detected, $user),
            ChatbotIntent::Units => $this->units($user),
            ChatbotIntent::Help => $this->help(),
            ChatbotIntent::Unknown => $this->unknown(),
        };
    }

    private function articleAvailability(ChatbotIntentResult $intent, User $user): ChatbotResponse
    {
        $articles = $this->matchingArticles($intent->normalized);
        $article = $articles->first();

        if (! $article) {
            return $intent->status
                ? $this->articlesByStatus($intent->status, $user)
                : $this->inventorySummary($user);
        }

        if ($articles->count() > 1) {
            return $this->articleGroupAvailability($articles, $intent, $user);
        }

        if ($article->isCantidad()) {
            $inventory = InventarioUnidadArticulo::query()
                ->where('articulo_id', $article->id)
                ->when(! $user->isAdministradorGeneral(), fn (Builder $query) => $query->where('unidad_id', $user->unidad_id))
                ->selectRaw('COALESCE(SUM(cantidad_disponible), 0) AS disponibles')
                ->selectRaw('COALESCE(SUM(cantidad_asignada), 0) AS asignadas')
                ->selectRaw('COALESCE(SUM(cantidad_mantenimiento), 0) AS mantenimiento')
                ->first();

            $available = (float) ($inventory?->disponibles ?? 0);
            $assigned = (float) ($inventory?->asignadas ?? 0);
            $maintenance = (float) ($inventory?->mantenimiento ?? 0);

            if ($intent->status) {
                $value = match ($intent->status) {
                    'asignado' => $assigned,
                    'en_mantenimiento' => $maintenance,
                    default => $available,
                };

                return new ChatbotResponse(sprintf(
                    '%s tiene %s %s%s.',
                    $article->nombre,
                    $this->quantity($value),
                    $this->statusLabel($intent->status),
                    $this->scopeLabel($user),
                ));
            }

            return new ChatbotResponse(sprintf(
                '%s: hay %s disponibles%s. Además, %s están asignados y %s en mantenimiento.',
                $this->opening(),
                $this->quantity($available),
                $this->scopeLabel($user),
                $this->quantity($assigned),
                $this->quantity($maintenance),
            ));
        }

        $counts = ArticuloSerie::query()
            ->where('articulo_id', $article->id)
            ->when(! $user->isAdministradorGeneral(), fn (Builder $query) => $query->where('unidad_id', $user->unidad_id))
            ->selectRaw("SUM(CASE WHEN estado = 'disponible' THEN 1 ELSE 0 END) AS disponibles")
            ->selectRaw("SUM(CASE WHEN estado = 'asignado' THEN 1 ELSE 0 END) AS asignadas")
            ->selectRaw("SUM(CASE WHEN estado = 'en_mantenimiento' THEN 1 ELSE 0 END) AS mantenimiento")
            ->first();

        if ($intent->status) {
            $total = ArticuloSerie::query()
                ->where('articulo_id', $article->id)
                ->where('estado', $intent->status)
                ->when(! $user->isAdministradorGeneral(), fn (Builder $query) => $query->where('unidad_id', $user->unidad_id))
                ->count();

            return new ChatbotResponse(
                "{$article->nombre}: encontré {$total} series {$this->statusLabel($intent->status)}{$this->scopeLabel($user)}."
            );
        }

        return new ChatbotResponse(sprintf(
            '%s: encontré %d series disponibles%s. También hay %d asignadas y %d en mantenimiento.',
            $article->nombre,
            (int) ($counts?->disponibles ?? 0),
            $this->scopeLabel($user),
            (int) ($counts?->asignadas ?? 0),
            (int) ($counts?->mantenimiento ?? 0),
        ));
    }

    private function articlesInMaintenance(ChatbotIntentResult $intent, User $user): ChatbotResponse
    {
        $articles = $this->matchingArticles($intent->normalized);

        $serialized = ArticuloSerie::query()
            ->with('articulo:id,nombre')
            ->where('estado', 'en_mantenimiento')
            ->when($articles->isNotEmpty(), fn (Builder $query) => $query->whereIn('articulo_id', $articles->pluck('id')))
            ->when(! $user->isAdministradorGeneral(), fn (Builder $query) => $query->where('unidad_id', $user->unidad_id))
            ->get()
            ->groupBy(fn (ArticuloSerie $serie) => $serie->articulo?->nombre ?? 'Sin nombre')
            ->map->count();

        $consumables = InventarioUnidadArticulo::query()
            ->with('articulo:id,nombre')
            ->where('cantidad_mantenimiento', '>', 0)
            ->when($articles->isNotEmpty(), fn (Builder $query) => $query->whereIn('articulo_id', $articles->pluck('id')))
            ->when(! $user->isAdministradorGeneral(), fn (Builder $query) => $query->where('unidad_id', $user->unidad_id))
            ->get()
            ->groupBy(fn (InventarioUnidadArticulo $inventory) => $inventory->articulo?->nombre ?? 'Sin nombre')
            ->map(fn (Collection $rows) => (float) $rows->sum('cantidad_mantenimiento'));

        $items = $serialized
            ->mergeRecursive($consumables)
            ->map(fn ($value) => is_array($value) ? array_sum($value) : $value)
            ->sortDesc();

        if ($items->isEmpty()) {
            return new ChatbotResponse('Buenas noticias: no encontré artículos en mantenimiento'.$this->scopeLabel($user).'.');
        }

        return new ChatbotResponse(
            text: 'Actualmente hay '.$this->quantity((float) $items->sum()).' en mantenimiento'.$this->scopeLabel($user).'. Entre los principales están:',
            items: $items->take(config('chatbot.result_limit'))->map(
                fn ($total, $name) => "{$name}: {$this->quantity((float) $total)}"
            )->values()->all(),
        );
    }

    private function activeLoans(ChatbotIntentResult $intent, User $user): ChatbotResponse
    {
        $articles = $this->matchingArticles($intent->normalized);
        $article = $articles->first();
        $pendingReturnsQuestion = $intent->operation === 'devolucion';

        $operations = Operacion::query()
            ->with([
                'usuarioDestino:id,name,apellido_paterno,apellido_materno,numero_escalafon',
                'detalles.articulo:id,nombre,tipo',
                'detalles.series.serie:id,operacion_detalle_id_actual',
                'devoluciones.detalles:id,operacion_id,articulo_id,cantidad',
            ])
            ->where('tipo', 'asignacion')
            ->when($articles->isNotEmpty(), fn (Builder $query) => $query->whereHas(
                'detalles',
                fn (Builder $details) => $details->whereIn('articulo_id', $articles->pluck('id'))
            ))
            ->when(
                $user->isPolicia(),
                fn (Builder $query) => $query->where('usuario_destino_id', $user->id),
                fn (Builder $query) => $query->when(
                    ! $user->isAdministradorGeneral(),
                    fn (Builder $scoped) => $scoped->where('unidad_id', $user->unidad_id)
                )
            )
            ->latest('fecha')
            ->get()
            ->filter(fn (Operacion $operation) => $this->isActiveLoan($operation));

        if ($operations->isEmpty()) {
            return new ChatbotResponse(
                $user->isPolicia()
                    ? ($pendingReturnsQuestion
                        ? 'No tienes devoluciones pendientes en este momento.'
                        : 'No tienes préstamos activos en este momento.')
                    : ($pendingReturnsQuestion
                        ? 'No encontré devoluciones pendientes'.$this->scopeLabel($user).'.'
                        : 'No encontré préstamos activos'.$this->scopeLabel($user).'.')
            );
        }

        if ($user->isPolicia()) {
            return new ChatbotResponse(
                $pendingReturnsQuestion
                    ? 'Tienes '.$operations->count().' devolución'.($operations->count() === 1 ? '' : 'es').' pendiente'.($operations->count() === 1 ? '' : 's').'.'
                    : 'Tienes '.$operations->count().' préstamo'.($operations->count() === 1 ? '' : 's').' activo'.($operations->count() === 1 ? '' : 's').'.'
            );
        }

        $people = $operations
            ->groupBy('usuario_destino_id')
            ->map(function (Collection $loans): string {
                $user = $loans->first()?->usuarioDestino;
                $person = $user?->nombre_completo ?? 'Usuario sin nombre';
                $identifier = $user?->numero_escalafon ? " · Esc. {$user->numero_escalafon}" : '';

                return "{$person}{$identifier} ({$loans->count()})";
            })
            ->take(config('chatbot.result_limit'))
            ->values();

        return new ChatbotResponse(
            text: 'Encontré '.$operations->count().' '
                .($pendingReturnsQuestion ? 'devoluciones pendientes' : 'préstamos activos')
                .($article ? " relacionados con {$article->nombre}" : '')
                .($pendingReturnsQuestion ? ', distribuidas entre ' : ', distribuidos entre ')
                .$operations->pluck('usuario_destino_id')->unique()->count().' personas'
                .$this->scopeLabel($user).':',
            items: $people->all(),
        );
    }

    private function inventorySummary(User $user): ChatbotResponse
    {
        $series = ArticuloSerie::query()
            ->when(! $user->isAdministradorGeneral(), fn (Builder $query) => $query->where('unidad_id', $user->unidad_id))
            ->selectRaw("SUM(CASE WHEN estado = 'disponible' THEN 1 ELSE 0 END) AS disponibles")
            ->selectRaw("SUM(CASE WHEN estado = 'asignado' THEN 1 ELSE 0 END) AS asignadas")
            ->selectRaw("SUM(CASE WHEN estado = 'en_mantenimiento' THEN 1 ELSE 0 END) AS mantenimiento")
            ->selectRaw("SUM(CASE WHEN estado = 'inoperativo' THEN 1 ELSE 0 END) AS inoperativas")
            ->first();

        $consumables = InventarioUnidadArticulo::query()
            ->when(! $user->isAdministradorGeneral(), fn (Builder $query) => $query->where('unidad_id', $user->unidad_id))
            ->selectRaw('COALESCE(SUM(cantidad_disponible), 0) AS disponibles')
            ->selectRaw('COALESCE(SUM(cantidad_asignada), 0) AS asignadas')
            ->selectRaw('COALESCE(SUM(cantidad_mantenimiento), 0) AS mantenimiento')
            ->first();

        return new ChatbotResponse(
            text: 'Este es el panorama actual del inventario'.$this->scopeLabel($user).':',
            items: [
                'Series disponibles: '.(int) ($series?->disponibles ?? 0),
                'Series asignadas: '.(int) ($series?->asignadas ?? 0),
                'Series en mantenimiento: '.(int) ($series?->mantenimiento ?? 0),
                'Series inoperativas: '.(int) ($series?->inoperativas ?? 0),
                'Consumibles disponibles: '.$this->quantity((float) ($consumables?->disponibles ?? 0)),
                'Consumibles asignados: '.$this->quantity((float) ($consumables?->asignadas ?? 0)),
                'Consumibles en mantenimiento: '.$this->quantity((float) ($consumables?->mantenimiento ?? 0)),
            ],
        );
    }

    private function units(User $user): ChatbotResponse
    {
        $units = Unidad::query()
            ->when(
                ! $user->isAdministradorGeneral(),
                fn (Builder $query) => $query->whereKey($user->unidad_id)
            )
            ->orderBy('nombre')
            ->get(['nombre', 'sigla']);

        if ($units->isEmpty()) {
            return new ChatbotResponse('No encontré unidades disponibles para tu usuario.');
        }

        $scope = $user->isAdministradorGeneral()
            ? 'registradas en la institución'
            : 'asignada a tu usuario';

        return new ChatbotResponse(
            text: 'Hay '.$units->count().' '.($units->count() === 1 ? 'unidad' : 'unidades').' '.$scope.':',
            items: $units->map(
                fn (Unidad $unit) => $unit->sigla
                    ? "{$unit->nombre} ({$unit->sigla})"
                    : $unit->nombre
            )->all(),
        );
    }

    private function articlesByStatus(string $status, User $user): ChatbotResponse
    {
        if ($status === 'en_mantenimiento') {
            return $this->articlesInMaintenance(
                new ChatbotIntentResult(
                    ChatbotIntent::ArticlesInMaintenance,
                    '',
                    '',
                    status: 'en_mantenimiento',
                ),
                $user
            );
        }

        $items = ArticuloSerie::query()
            ->with('articulo:id,nombre')
            ->where('estado', $status)
            ->when(! $user->isAdministradorGeneral(), fn (Builder $query) => $query->where('unidad_id', $user->unidad_id))
            ->get()
            ->groupBy(fn (ArticuloSerie $series) => $series->articulo?->nombre ?? 'Sin nombre')
            ->map->count()
            ->sortDesc();

        if ($items->isEmpty()) {
            return new ChatbotResponse(
                'No encontré artículos '.$this->statusLabel($status).$this->scopeLabel($user).'.'
            );
        }

        return new ChatbotResponse(
            text: 'Encontré '.$items->sum().' series '.$this->statusLabel($status).$this->scopeLabel($user).':',
            items: $items->take(config('chatbot.result_limit'))
                ->map(fn ($total, $name) => "{$name}: {$total}")
                ->values()
                ->all(),
        );
    }

    private function lowStock(User $user): ChatbotResponse
    {
        $items = InventarioUnidadArticulo::query()
            ->with('articulo:id,nombre')
            ->where('stock_minimo', '>', 0)
            ->whereColumn('cantidad_disponible', '<=', 'stock_minimo')
            ->when(! $user->isAdministradorGeneral(), fn (Builder $query) => $query->where('unidad_id', $user->unidad_id))
            ->orderBy('cantidad_disponible')
            ->limit(config('chatbot.result_limit'))
            ->get();

        if ($items->isEmpty()) {
            return new ChatbotResponse('No encontré consumibles con stock bajo'.$this->scopeLabel($user).'.');
        }

        return new ChatbotResponse(
            text: 'Encontré '.$items->count().' artículos en nivel mínimo o por debajo'.$this->scopeLabel($user).':',
            items: $items->map(fn (InventarioUnidadArticulo $row) => sprintf(
                '%s: %s disponibles (mínimo %s)',
                $row->articulo?->nombre ?? 'Artículo',
                $this->quantity((float) $row->cantidad_disponible),
                $this->quantity((float) $row->stock_minimo),
            ))->all(),
        );
    }

    private function incidents(ChatbotIntentResult $intent, User $user): ChatbotResponse
    {
        $articles = $this->matchingArticles($intent->normalized);
        $article = $articles->first();
        $query = Incidencia::query()
            ->with('tipo:id,nombre')
            ->when($intent->from && $intent->to, fn (Builder $builder) => $builder->whereBetween('fecha', [$intent->from, $intent->to]))
            ->when($articles->isNotEmpty(), fn (Builder $builder) => $builder->whereIn('articulo_id', $articles->pluck('id')))
            ->when(! $user->isAdministradorGeneral(), fn (Builder $builder) => $builder->whereHas(
                'serie',
                fn (Builder $series) => $series->where('unidad_id', $user->unidad_id)
            ));

        $incidents = $query->get();

        if ($incidents->isEmpty()) {
            return new ChatbotResponse('No encontré incidencias para ese criterio'.$this->scopeLabel($user).'.');
        }

        $period = $this->periodLabel($intent);
        $groups = $incidents->groupBy(fn (Incidencia $incident) => $incident->tipo?->nombre ?? 'Sin tipo')
            ->map->count()
            ->sortDesc();

        return new ChatbotResponse(
            text: 'Encontré '.$incidents->count().' incidencias'.$period
                .($article ? " relacionadas con {$article->nombre}" : '')
                .$this->scopeLabel($user).':',
            items: $groups->take(config('chatbot.result_limit'))
                ->map(fn ($total, $name) => "{$name}: {$total}")
                ->values()
                ->all(),
        );
    }

    private function operationSummary(ChatbotIntentResult $intent, User $user): ChatbotResponse
    {
        $articles = $this->matchingArticles($intent->normalized);
        $article = $articles->first();
        $operation = $intent->operation ?? 'asignacion';

        $query = Operacion::query()
            ->with('usuarioDestino:id,name,apellido_paterno,apellido_materno')
            ->where('tipo', $operation)
            ->when($intent->from && $intent->to, fn (Builder $builder) => $builder->whereBetween('fecha', [$intent->from, $intent->to]))
            ->when($articles->isNotEmpty(), fn (Builder $builder) => $builder->whereHas(
                'detalles',
                fn (Builder $details) => $details->whereIn('articulo_id', $articles->pluck('id'))
            ))
            ->when(
                $user->isPolicia(),
                fn (Builder $builder) => $builder->where('usuario_destino_id', $user->id),
                fn (Builder $builder) => $builder->when(
                    ! $user->isAdministradorGeneral(),
                    fn (Builder $scoped) => $scoped->where('unidad_id', $user->unidad_id)
                )
            )
            ->latest('fecha');

        $total = (clone $query)->count();
        $recent = $query->limit(config('chatbot.result_limit'))->get();

        if ($total === 0) {
            return new ChatbotResponse(
                'No encontré '.$this->operationLabel($operation).' para ese criterio'.$this->scopeLabel($user).'.'
            );
        }

        return new ChatbotResponse(
            text: 'Encontré '.$total.' '.$this->operationLabel($operation)
                .$this->periodLabel($intent)
                .($article ? " de {$article->nombre}" : '')
                .$this->scopeLabel($user).'. Movimientos recientes:',
            items: $recent->map(function (Operacion $operation): string {
                $person = $operation->usuarioDestino?->nombre_completo;

                return $operation->fecha->format('d/m/Y H:i')
                    .($person ? " · {$person}" : '')
                    ." · Operación #{$operation->id}";
            })->all(),
        );
    }

    private function returnsWithIncidents(ChatbotIntentResult $intent, User $user): ChatbotResponse
    {
        $from = $intent->from;
        $to = $intent->to;

        $returns = Operacion::query()
            ->where('operaciones.tipo', 'devolucion')
            ->when($from && $to, fn (Builder $query) => $query->whereBetween('operaciones.fecha', [$from, $to]))
            ->when(! $user->isAdministradorGeneral(), fn (Builder $query) => $query->where('operaciones.unidad_id', $user->unidad_id))
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('operacion_detalles as od')
                    ->join('operacion_detalle_series as ods', 'ods.operacion_detalle_id', '=', 'od.id')
                    ->join('incidencias as i', 'i.serie_id', '=', 'ods.serie_id')
                    ->whereColumn('od.operacion_id', 'operaciones.id')
                    ->whereNull('od.deleted_at')
                    ->whereNull('ods.deleted_at')
                    ->whereNull('i.deleted_at');
            })
            ->count();

        $incidents = Incidencia::query()
            ->when($from && $to, fn (Builder $query) => $query->whereBetween('fecha', [$from, $to]))
            ->when(! $user->isAdministradorGeneral(), fn (Builder $query) => $query->whereHas(
                'serie',
                fn (Builder $series) => $series->where('unidad_id', $user->unidad_id)
            ))
            ->count();

        $period = $from && $to
            ? "Entre {$from->format('d/m/Y')} y {$to->format('d/m/Y')}"
            : 'En el historial disponible';

        return new ChatbotResponse(
            "{$period} encontré {$returns} devoluciones vinculadas a una incidencia. En ese alcance se registraron {$incidents} incidencias en total{$this->scopeLabel($user)}."
        );
    }

    private function mostBorrowed(ChatbotIntentResult $intent, User $user): ChatbotResponse
    {
        $from = $intent->from ?? CarbonImmutable::now()->startOfMonth();
        $to = $intent->to ?? CarbonImmutable::now()->endOfMonth();

        $row = DB::table('operacion_detalles as od')
            ->join('operaciones as o', 'o.id', '=', 'od.operacion_id')
            ->join('articulos as a', 'a.id', '=', 'od.articulo_id')
            ->whereNull('od.deleted_at')
            ->whereNull('o.deleted_at')
            ->whereNull('a.deleted_at')
            ->where('o.tipo', 'asignacion')
            ->whereBetween('o.fecha', [$from, $to])
            ->when(! $user->isAdministradorGeneral(), fn ($query) => $query->where('o.unidad_id', $user->unidad_id))
            ->select('a.nombre')
            ->selectRaw('SUM(od.cantidad) AS total')
            ->groupBy('a.id', 'a.nombre')
            ->orderByDesc('total')
            ->first();

        if (! $row) {
            return new ChatbotResponse(
                "No encontré préstamos registrados entre {$from->format('d/m/Y')} y {$to->format('d/m/Y')}{$this->scopeLabel($user)}."
            );
        }

        return new ChatbotResponse(
            "El artículo más prestado entre {$from->format('d/m/Y')} y {$to->format('d/m/Y')} fue {$row->nombre}, con {$this->quantity((float) $row->total)} entregados{$this->scopeLabel($user)}."
        );
    }

    private function maintenanceDue(User $user): ChatbotResponse
    {
        $cycleDays = (int) config('chatbot.maintenance_cycle_days', 180);
        $warningDays = (int) config('chatbot.maintenance_warning_days', 30);
        $deadline = CarbonImmutable::now()->addDays($warningDays);

        $lastMaintenance = Mantenimiento::query()
            ->select('serie_id')
            ->selectRaw('MAX(COALESCE(fecha_fin, fecha_inicio)) AS last_date')
            ->whereNotNull('serie_id')
            ->groupBy('serie_id');

        $series = ArticuloSerie::query()
            ->with(['articulo:id,nombre', 'unidad:id,nombre,sigla'])
            ->leftJoinSub($lastMaintenance, 'last_maintenance', function ($join) {
                $join->on('last_maintenance.serie_id', '=', 'articulo_series.id');
            })
            ->whereNotIn('articulo_series.estado', ['inoperativo', 'dado_de_baja', 'perdido', 'robado'])
            ->when(! $user->isAdministradorGeneral(), fn (Builder $query) => $query->where('articulo_series.unidad_id', $user->unidad_id))
            ->select('articulo_series.*', 'last_maintenance.last_date')
            ->get()
            ->map(function (ArticuloSerie $serie) use ($cycleDays) {
                $base = $serie->last_date
                    ? CarbonImmutable::parse($serie->last_date)
                    : CarbonImmutable::parse($serie->created_at);

                $serie->next_maintenance = $base->addDays($cycleDays);

                return $serie;
            })
            ->filter(fn (ArticuloSerie $serie) => $serie->next_maintenance->lessThanOrEqualTo($deadline))
            ->sortBy('next_maintenance')
            ->take(config('chatbot.result_limit'));

        if ($series->isEmpty()) {
            return new ChatbotResponse(
                "No encontré armamento con mantenimiento preventivo vencido o próximo en los siguientes {$warningDays} días{$this->scopeLabel($user)}."
            );
        }

        $items = $series->map(function (ArticuloSerie $serie): string {
            $status = $serie->next_maintenance->isPast() ? 'vencido' : 'vence';

            return sprintf(
                '%s %s (%s %s)',
                $serie->articulo?->nombre ?? 'Armamento',
                $serie->codigo_serie,
                $status,
                $serie->next_maintenance->format('d/m/Y')
            );
        });

        return new ChatbotResponse(
            text: 'Según el ciclo preventivo configurado de '.$cycleDays.' días, encontré '.$series->count().' series que requieren atención'.$this->scopeLabel($user).':',
            items: $items->all(),
        );
    }

    private function help(): ChatbotResponse
    {
        return new ChatbotResponse(
            'Puedo relacionar nombres de artículos, estados, operaciones y períodos. Consulta inventario, disponibilidad, asignaciones, devoluciones, consumos, incidencias, stock bajo, artículos perdidos o robados, estadísticas y mantenimiento. Las respuestas respetan tu unidad y permisos.'
        );
    }

    private function unknown(): ChatbotResponse
    {
        return new ChatbotResponse(
            'No pude relacionar esa consulta con los datos disponibles. Incluye uno o más elementos como el nombre del artículo, su estado, una operación o un período de tiempo.'
        );
    }

    /**
     * @return Collection<int, Articulo>
     */
    private function matchingArticles(string $message): Collection
    {
        $stopWords = [
            'cuantos', 'cuantas', 'cuanto', 'cuanta', 'hay', 'estan', 'esta', 'disponible',
            'disponibles', 'stock', 'existencia', 'quedan', 'articulo', 'articulos', 'equipo',
            'equipos', 'armamento', 'del', 'de', 'la', 'las', 'el', 'los', 'en', 'mi', 'unidad',
            'asignado', 'asignados', 'prestado', 'prestados', 'entregado', 'entregados',
            'mantenimiento', 'inoperativo', 'inoperativos', 'perdido', 'perdidos', 'robado',
            'robados', 'devolucion', 'devoluciones', 'prestamo', 'prestamos', 'incidencia',
            'incidencias', 'hoy', 'ayer', 'semana', 'mes', 'ano', 'actual', 'pasado',
            'que', 'cual', 'cuales', 'quien', 'quienes', 'mostrar', 'muestra', 'listar',
            'lista', 'dime', 'ver', 'tienen', 'tiene', 'hubo', 'registrados', 'registradas',
            'este', 'estos', 'estas', 'durante', 'ultimo', 'ultimos', 'ultima', 'ultimas',
            'dia', 'dias', 'periodo', 'entre', 'desde', 'hasta',
        ];

        $tokens = collect(explode(' ', $message))
            ->reject(fn (string $token) => in_array($token, $stopWords, true) || mb_strlen($token) < 3)
            ->values();

        if ($tokens->isEmpty()) {
            return collect();
        }

        $matches = Articulo::query()
            ->get()
            ->map(function (Articulo $article) use ($tokens) {
                $name = $this->intents->normalize($article->nombre);
                $score = $tokens->sum(function (string $token) use ($name) {
                    $variants = [$token];

                    if (str_ends_with($token, 'es') && mb_strlen($token) > 5) {
                        $variants[] = mb_substr($token, 0, -2);
                    }

                    if (str_ends_with($token, 's') && mb_strlen($token) > 4) {
                        $variants[] = mb_substr($token, 0, -1);
                    }

                    foreach (array_unique($variants) as $variant) {
                        if ($name === $variant) {
                            return 8;
                        }

                        if (str_starts_with($name, $variant.' ')) {
                            return 6;
                        }

                        if (str_contains($name, $variant) || str_contains($variant, $name)) {
                            return 4;
                        }
                    }

                    similar_text($token, $name, $percentage);

                    return $percentage >= 70 ? 2 : 0;
                });

                return [$article, $score];
            })
            ->sortByDesc(fn (array $match) => $match[1])
            ->filter(fn (array $match) => $match[1] > 0);

        $topScore = (int) ($matches->first()[1] ?? 0);

        return $matches
            ->filter(fn (array $match) => $match[1] === $topScore)
            ->map(fn (array $match) => $match[0])
            ->values();
    }

    /**
     * @param  Collection<int, Articulo>  $articles
     */
    private function articleGroupAvailability(
        Collection $articles,
        ChatbotIntentResult $intent,
        User $user
    ): ChatbotResponse {
        $items = $articles->map(function (Articulo $article) use ($intent, $user): string {
            if ($article->isCantidad()) {
                $inventory = InventarioUnidadArticulo::query()
                    ->where('articulo_id', $article->id)
                    ->when(! $user->isAdministradorGeneral(), fn (Builder $query) => $query->where('unidad_id', $user->unidad_id))
                    ->selectRaw('COALESCE(SUM(cantidad_disponible), 0) AS disponibles')
                    ->selectRaw('COALESCE(SUM(cantidad_asignada), 0) AS asignadas')
                    ->selectRaw('COALESCE(SUM(cantidad_mantenimiento), 0) AS mantenimiento')
                    ->first();

                $value = match ($intent->status) {
                    'asignado' => (float) ($inventory?->asignadas ?? 0),
                    'en_mantenimiento' => (float) ($inventory?->mantenimiento ?? 0),
                    default => (float) ($inventory?->disponibles ?? 0),
                };

                return "{$article->nombre}: {$this->quantity($value)}";
            }

            $query = ArticuloSerie::query()
                ->where('articulo_id', $article->id)
                ->when(! $user->isAdministradorGeneral(), fn (Builder $builder) => $builder->where('unidad_id', $user->unidad_id));

            if ($intent->status) {
                $query->where('estado', $intent->status);
            } else {
                $query->where('estado', 'disponible');
            }

            return "{$article->nombre}: {$query->count()}";
        });

        $label = $intent->status ? $this->statusLabel($intent->status) : 'disponibles';

        return new ChatbotResponse(
            text: 'Encontré varias coincidencias con estado “'.$label.'”'.$this->scopeLabel($user).':',
            items: $items->all(),
        );
    }

    private function isActiveLoan(Operacion $operation): bool
    {
        $returnedQuantities = [];

        foreach ($operation->devoluciones as $return) {
            foreach ($return->detalles as $detail) {
                $returnedQuantities[$detail->articulo_id] = ($returnedQuantities[$detail->articulo_id] ?? 0) + (float) $detail->cantidad;
            }
        }

        foreach ($operation->detalles as $detail) {
            if ($detail->articulo?->isSerializado()) {
                if ($detail->series->contains(fn ($pivot) => $pivot->serie?->operacion_detalle_id_actual === $detail->id)) {
                    return true;
                }

                continue;
            }

            if ((float) $detail->cantidad > ($returnedQuantities[$detail->articulo_id] ?? 0)) {
                return true;
            }
        }

        return false;
    }

    private function quantity(float $quantity): string
    {
        return fmod($quantity, 1.0) === 0.0
            ? number_format($quantity, 0, ',', '.')
            : number_format($quantity, 2, ',', '.');
    }

    private function scopeLabel(User $user): string
    {
        return $user->isAdministradorGeneral()
            ? ' en toda la institución'
            : ' en tu unidad';
    }

    private function opening(): string
    {
        return collect(['Según el inventario actual', 'Revisé el inventario', 'En este momento'])
            ->random();
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'disponible' => 'disponibles',
            'asignado' => 'asignados',
            'en_mantenimiento' => 'en mantenimiento',
            'inoperativo' => 'inoperativos',
            'perdido' => 'perdidos',
            'robado' => 'robados',
            'dado_de_baja' => 'dados de baja',
            default => str_replace('_', ' ', $status),
        };
    }

    private function operationLabel(string $operation): string
    {
        return match ($operation) {
            'asignacion' => 'préstamos o asignaciones',
            'devolucion' => 'devoluciones',
            'consumo' => 'consumos',
            'mantenimiento_salida' => 'salidas a mantenimiento',
            'mantenimiento_retorno' => 'retornos de mantenimiento',
            'ajuste' => 'ajustes de inventario',
            default => 'operaciones',
        };
    }

    private function periodLabel(ChatbotIntentResult $intent): string
    {
        if (! $intent->from || ! $intent->to) {
            return '';
        }

        return " entre {$intent->from->format('d/m/Y')} y {$intent->to->format('d/m/Y')}";
    }
}
