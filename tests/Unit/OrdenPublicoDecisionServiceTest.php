<?php

namespace Tests\Unit;

use App\Services\OrdenPublicoDecisionService;
use Tests\TestCase;

class OrdenPublicoDecisionServiceTest extends TestCase
{
    public function test_licit_and_peaceful_manifestation_results_in_accompaniment(): void
    {
        $result = app(OrdenPublicoDecisionService::class)->recomendar([
            'situacion' => 'manifestacion',
            'legalidad' => 'licita',
            'conducta' => 'cooperadora',
            'magnitud' => 'alta',
            'planificado' => true,
            'vulnerables' => false,
            'zona_sensible' => false,
        ]);

        $this->assertSame('Acompañamiento y protección de la reunión', $result['respuesta']);
        $this->assertStringContainsString('Facilitar', $result['objetivo']);
    }

    public function test_illicit_but_peaceful_reunion_prioritizes_negotiation(): void
    {
        $result = app(OrdenPublicoDecisionService::class)->recomendar([
            'situacion' => 'bloqueo',
            'legalidad' => 'ilicita',
            'conducta' => 'cooperadora',
            'magnitud' => 'media',
        ]);

        $this->assertSame('Negociación preventiva', $result['nivel']);
        $this->assertStringContainsString('dispersión voluntaria', implode(' ', $result['acciones']));
    }

    public function test_lethal_threat_requires_individualized_response_and_medical_support(): void
    {
        $result = app(OrdenPublicoDecisionService::class)->recomendar([
            'situacion' => 'disturbio',
            'legalidad' => 'ilicita',
            'conducta' => 'agresion_letal',
            'magnitud' => 'masiva',
            'vulnerables' => true,
            'zona_sensible' => true,
        ]);

        $safeguards = implode(' ', $result['salvaguardas']);

        $this->assertSame('Crítico', $result['riesgo']);
        $this->assertStringContainsString('selectiva', $safeguards);
        $this->assertStringContainsString('médica', implode(' ', $result['acciones']));
    }
}
