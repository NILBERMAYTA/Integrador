<?php

namespace Tests\Unit;

use App\Services\SyntheticArmamentHistoryPlanner;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class SyntheticArmamentHistoryPlannerTest extends TestCase
{
    public function test_plan_is_deterministic_and_preserves_final_condition(): void
    {
        $planner = new SyntheticArmamentHistoryPlanner;
        $serie = [
            'id' => 100,
            'codigo_serie' => 'TEST-U1-A001-00001',
            'estado' => 'inoperativo',
            'condicion_actual' => 'inoperativo',
        ];
        $reference = CarbonImmutable::parse('2026-06-22 12:00:00');

        $first = $planner->plan($serie, 20260622, 4, 7, 24, $reference);
        $second = $planner->plan($serie, 20260622, 4, 7, 24, $reference);

        $this->assertEquals($first, $second);
        $this->assertSame(
            'inoperativo',
            $first['inspections'][array_key_last($first['inspections'])]['resultado'],
        );
        $this->assertGreaterThanOrEqual(4, count($first['inspections']));
        $this->assertLessThanOrEqual(7, count($first['inspections']));
    }

    public function test_dates_are_chronological_and_maintenance_is_coherent(): void
    {
        $planner = new SyntheticArmamentHistoryPlanner;
        $plan = $planner->plan([
            'id' => 200,
            'codigo_serie' => 'TEST-U2-A010-00020',
            'estado' => 'en_mantenimiento',
            'condicion_actual' => 'malo',
        ], 20260622, 5, 5, 18, CarbonImmutable::parse('2026-06-22 12:00:00'));

        $inspectionDates = array_map(
            fn (array $inspection) => $inspection['fecha']->timestamp,
            $plan['inspections'],
        );

        $this->assertSame($inspectionDates, collect($inspectionDates)->sort()->values()->all());
        $this->assertSame('observado', $plan['inspections'][4]['resultado']);
        $this->assertNull($plan['maintenances'][array_key_last($plan['maintenances'])]['fecha_fin']);

        foreach ($plan['operations'] as $operation) {
            $this->assertTrue($operation['devolucion']->greaterThanOrEqualTo($operation['asignacion']));
        }
    }
}
