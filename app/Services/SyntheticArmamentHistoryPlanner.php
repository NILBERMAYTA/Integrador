<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class SyntheticArmamentHistoryPlanner
{
    public function plan(
        array $serie,
        int $seed,
        int $minInspections,
        int $maxInspections,
        int $months,
        ?CarbonImmutable $referenceDate = null,
    ): array {
        $referenceDate ??= CarbonImmutable::now();
        $key = "{$seed}:{$serie['id']}:{$serie['codigo_serie']}";
        $profile = $this->profileFor(
            (string) $serie['condicion_actual'],
            $this->fraction("{$key}:profile"),
        );
        $inspectionCount = $this->integer(
            "{$key}:inspections",
            $minInspections,
            $maxInspections,
        );
        $latestDaysAgo = $this->integer("{$key}:latest", 4, 30);
        $oldestDaysAgo = max(
            $latestDaysAgo + (($inspectionCount - 1) * 35),
            ($months * 30) - $this->integer("{$key}:oldest-jitter", 0, 45),
        );
        $template = $this->inspectionTemplate($profile);
        $inspections = [];

        for ($index = 0; $index < $inspectionCount; $index++) {
            $progress = $inspectionCount === 1 ? 1 : $index / ($inspectionCount - 1);
            $templateIndex = (int) round($progress * (count($template) - 1));
            $daysAgo = (int) round(
                $oldestDaysAgo - (($oldestDaysAgo - $latestDaysAgo) * $progress)
            );
            $inspections[] = [
                'sequence' => $index + 1,
                'resultado' => $template[$templateIndex],
                'fecha' => $referenceDate
                    ->subDays($daysAgo)
                    ->setTime($this->integer("{$key}:inspection-hour:{$index}", 8, 16), 0),
            ];
        }

        $inspections[array_key_last($inspections)]['resultado'] =
            $this->finalInspectionResult((string) $serie['condicion_actual']);

        return [
            'profile' => $profile,
            'inspections' => $inspections,
            'operations' => $this->operations(
                $key,
                $profile,
                $inspections,
                (string) $serie['condicion_actual'],
            ),
            'maintenances' => $this->maintenances(
                $key,
                $profile,
                $inspections,
                (string) $serie['estado'],
            ),
            'incidents' => $this->incidents($key, $profile, $inspections),
        ];
    }

    public function selectionScore(int $serieId, string $code, int $seed): int
    {
        return $this->hash("{$seed}:selection:{$serieId}:{$code}");
    }

    private function profileFor(string $condition, float $fraction): string
    {
        return match ($condition) {
            'bueno' => match (true) {
                $fraction < 0.55 => 'estable',
                $fraction < 0.82 => 'preventivo_exitoso',
                default => 'recuperado',
            },
            'con_defectos' => match (true) {
                $fraction < 0.58 => 'desgaste_gradual',
                $fraction < 0.82 => 'reincidente',
                default => 'posterior_incidente',
            },
            'malo' => match (true) {
                $fraction < 0.55 => 'deterioro_avanzado',
                $fraction < 0.82 => 'mantenimiento_pendiente',
                default => 'deterioro_acelerado',
            },
            'inoperativo' => $fraction < 0.72
                ? 'falla_gradual'
                : 'falla_repentina',
            default => 'estable',
        };
    }

    private function inspectionTemplate(string $profile): array
    {
        return match ($profile) {
            'estable' => ['apto', 'apto', 'apto', 'apto'],
            'preventivo_exitoso' => ['apto', 'observado', 'apto', 'apto'],
            'recuperado' => ['observado', 'apto', 'observado', 'apto'],
            'desgaste_gradual' => ['apto', 'apto', 'observado', 'observado'],
            'reincidente' => ['apto', 'observado', 'apto', 'observado'],
            'posterior_incidente' => ['apto', 'apto', 'observado', 'observado'],
            'deterioro_avanzado' => ['apto', 'observado', 'observado', 'observado'],
            'mantenimiento_pendiente' => ['observado', 'apto', 'observado', 'observado'],
            'deterioro_acelerado' => ['apto', 'apto', 'observado', 'observado'],
            'falla_gradual' => ['apto', 'observado', 'observado', 'inoperativo'],
            'falla_repentina' => ['apto', 'apto', 'apto', 'inoperativo'],
            default => ['apto', 'apto', 'apto', 'apto'],
        };
    }

    private function operations(
        string $key,
        string $profile,
        array $inspections,
        string $condition,
    ): array {
        $minimum = in_array($profile, ['estable', 'preventivo_exitoso'], true) ? 2 : 3;
        $maximum = in_array($profile, ['falla_gradual', 'deterioro_acelerado'], true) ? 6 : 5;
        $count = $this->integer("{$key}:operation-count", $minimum, $maximum);
        $oldest = $inspections[0]['fecha'];
        $latest = $inspections[array_key_last($inspections)]['fecha']->subDays(8);
        $spanDays = max(30, $oldest->diffInDays($latest));
        $operations = [];

        for ($index = 0; $index < $count; $index++) {
            $progress = ($index + 1) / ($count + 1);
            $assignment = $oldest
                ->addDays((int) round($spanDays * $progress))
                ->setTime($this->integer("{$key}:operation-hour:{$index}", 7, 12), 0);
            $return = $assignment->addDays(
                $this->integer("{$key}:operation-duration:{$index}", 1, 12)
            );
            $late = $index >= $count - 2;
            $returnCondition = match (true) {
                ! $late => 'bueno',
                $condition === 'inoperativo' && $index === $count - 1 => 'inoperativo',
                $condition === 'malo' && $index === $count - 1 => 'danado',
                $condition === 'con_defectos' => 'regular',
                default => $this->fraction("{$key}:operation-condition:{$index}") < 0.15
                    ? 'regular'
                    : 'bueno',
            };

            $operations[] = [
                'sequence' => $index + 1,
                'asignacion' => $assignment,
                'devolucion' => min($return, $latest),
                'condicion_devolucion' => $returnCondition,
            ];
        }

        return $operations;
    }

    private function maintenances(
        string $key,
        string $profile,
        array $inspections,
        string $state,
    ): array {
        $count = match ($profile) {
            'estable' => $this->fraction("{$key}:maintenance") < 0.35 ? 1 : 0,
            'preventivo_exitoso', 'recuperado', 'desgaste_gradual',
            'reincidente', 'posterior_incidente' => 1,
            default => 2,
        };
        $maintenances = [];
        $lastInspection = $inspections[array_key_last($inspections)]['fecha'];

        for ($index = 0; $index < $count; $index++) {
            $inspectionIndex = min(
                count($inspections) - 2,
                max(1, count($inspections) - $count - 1 + $index),
            );
            $start = $inspections[$inspectionIndex]['fecha']->addDays(
                $this->integer("{$key}:maintenance-delay:{$index}", 2, 10)
            );
            $open = $index === $count - 1 && $state === 'en_mantenimiento';
            $type = in_array(
                $profile,
                ['deterioro_avanzado', 'mantenimiento_pendiente', 'deterioro_acelerado', 'falla_gradual', 'falla_repentina'],
                true,
            ) ? 'correctivo' : 'preventivo';

            $maintenances[] = [
                'sequence' => $index + 1,
                'tipo' => $type,
                'fecha_inicio' => min($start, $lastInspection->subDays(1)),
                'fecha_fin' => $open
                    ? null
                    : min(
                        $start->addDays(
                            $this->integer("{$key}:maintenance-duration:{$index}", 1, 8)
                        ),
                        $lastInspection,
                    ),
                'costo' => $type === 'correctivo'
                    ? $this->integer("{$key}:maintenance-cost:{$index}", 180, 900)
                    : $this->integer("{$key}:maintenance-cost:{$index}", 60, 300),
            ];
        }

        return $maintenances;
    }

    private function incidents(string $key, string $profile, array $inspections): array
    {
        $count = match ($profile) {
            'estable', 'preventivo_exitoso' => 0,
            'recuperado', 'desgaste_gradual', 'reincidente' => 1,
            'posterior_incidente', 'deterioro_avanzado',
            'mantenimiento_pendiente', 'deterioro_acelerado' => 2,
            'falla_gradual', 'falla_repentina' => 3,
            default => 0,
        };
        $incidents = [];
        $first = $inspections[0]['fecha'];
        $last = $inspections[array_key_last($inspections)]['fecha']->subDays(2);
        $spanDays = max(10, $first->diffInDays($last));

        for ($index = 0; $index < $count; $index++) {
            $progress = ($index + 1) / ($count + 1);
            $incidents[] = [
                'sequence' => $index + 1,
                'fecha' => $first
                    ->addDays((int) round($spanDays * $progress))
                    ->setTime($this->integer("{$key}:incident-hour:{$index}", 9, 20), 0),
                'severity' => min(5, 2 + $index + ($profile === 'falla_repentina' ? 1 : 0)),
            ];
        }

        return $incidents;
    }

    private function finalInspectionResult(string $condition): string
    {
        return match ($condition) {
            'inoperativo' => 'inoperativo',
            'con_defectos', 'malo' => 'observado',
            default => 'apto',
        };
    }

    private function integer(string $key, int $minimum, int $maximum): int
    {
        if ($maximum <= $minimum) {
            return $minimum;
        }

        return $minimum + ($this->hash($key) % (($maximum - $minimum) + 1));
    }

    private function fraction(string $key): float
    {
        return $this->hash($key) / 4294967295;
    }

    private function hash(string $value): int
    {
        return (int) sprintf('%u', crc32($value));
    }
}
