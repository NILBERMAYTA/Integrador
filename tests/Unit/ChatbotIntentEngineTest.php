<?php

namespace Tests\Unit;

use App\Services\Chatbot\ChatbotIntent;
use App\Services\Chatbot\ChatbotIntentEngine;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ChatbotIntentEngineTest extends TestCase
{
    #[DataProvider('questions')]
    public function test_it_detects_supported_intents(string $question, ChatbotIntent $expected): void
    {
        $result = (new ChatbotIntentEngine())->detect($question);

        $this->assertSame($expected, $result->intent);
    }

    public function test_it_normalizes_accents_punctuation_and_case(): void
    {
        $engine = new ChatbotIntentEngine();

        $this->assertSame(
            'cuantos cascos estan disponibles',
            $engine->normalize('¿CUÁNTOS cascos están disponibles?')
        );
    }

    public function test_it_extracts_current_month_period(): void
    {
        CarbonImmutable::setTestNow('2026-06-20 15:00:00');

        $result = (new ChatbotIntentEngine())->detect('¿Cuál es el equipo más prestado este mes?');

        $this->assertSame('2026-06-01 00:00:00', $result->from?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-06-30 23:59:59', $result->to?->format('Y-m-d H:i:s'));

        CarbonImmutable::setTestNow();
    }

    public function test_it_extracts_status_operation_and_relative_days(): void
    {
        CarbonImmutable::setTestNow('2026-06-20 15:00:00');

        $status = (new ChatbotIntentEngine())->detect('¿Cuántos escudos están robados?');
        $operation = (new ChatbotIntentEngine())->detect('¿Cuántas devoluciones hubo en los últimos 7 días?');

        $this->assertSame('robado', $status->status);
        $this->assertSame('devolucion', $operation->operation);
        $this->assertSame('2026-06-13', $operation->from?->format('Y-m-d'));
        $this->assertSame('2026-06-20', $operation->to?->format('Y-m-d'));

        CarbonImmutable::setTestNow();
    }

    /**
     * @return array<string, array{string, ChatbotIntent}>
     */
    public static function questions(): array
    {
        return [
            'availability' => [
                '¿Cuántos cascos están disponibles?',
                ChatbotIntent::ArticleAvailability,
            ],
            'maintenance list' => [
                'Muéstrame los artículos en mantenimiento',
                ChatbotIntent::ArticlesInMaintenance,
            ],
            'active loans' => [
                '¿Quién tiene préstamos activos?',
                ChatbotIntent::ActiveLoans,
            ],
            'pending returns' => [
                '¿Cuáles son las devoluciones pendientes?',
                ChatbotIntent::ActiveLoans,
            ],
            'returns incidents' => [
                '¿Cuántas devoluciones tuvieron incidencia este mes?',
                ChatbotIntent::ReturnsWithIncidents,
            ],
            'most borrowed' => [
                '¿Cuál fue el equipo más prestado este mes?',
                ChatbotIntent::MostBorrowed,
            ],
            'maintenance due' => [
                '¿Qué armamento está próximo a mantenimiento?',
                ChatbotIntent::MaintenanceDue,
            ],
            'generic assigned article status' => [
                '¿Cuántos escudos están asignados?',
                ChatbotIntent::ArticleAvailability,
            ],
            'generic stolen status' => [
                'Lista el armamento robado',
                ChatbotIntent::ArticleAvailability,
            ],
            'low stock synonyms' => [
                '¿Qué consumibles están por agotarse?',
                ChatbotIntent::LowStock,
            ],
            'inventory summary' => [
                'Dame un resumen del inventario',
                ChatbotIntent::InventorySummary,
            ],
            'organizational units count' => [
                '¿Cuántas unidades hay?',
                ChatbotIntent::Units,
            ],
            'organizational units list' => [
                'Muéstrame las dependencias registradas',
                ChatbotIntent::Units,
            ],
            'inventory units are not organizational units' => [
                '¿Cuántas unidades de munición están disponibles?',
                ChatbotIntent::ArticleAvailability,
            ],
            'generic incidents' => [
                '¿Cuántas incidencias de cascos hubo este mes?',
                ChatbotIntent::Incidents,
            ],
            'generic operation count' => [
                '¿Cuántas devoluciones se registraron ayer?',
                ChatbotIntent::OperationSummary,
            ],
            'holder by article' => [
                '¿Quién tiene los cascos?',
                ChatbotIntent::ActiveLoans,
            ],
            'maintenance filtered by article' => [
                'Muéstrame los cascos en mantenimiento',
                ChatbotIntent::ArticlesInMaintenance,
            ],
            'maintenance with typo' => [
                'Dame la lista de los artículos en manetnimiento',
                ChatbotIntent::ArticlesInMaintenance,
            ],
            'help' => [
                '¿Qué puedes hacer?',
                ChatbotIntent::Help,
            ],
            'unknown' => [
                'Cuéntame un chiste',
                ChatbotIntent::Unknown,
            ],
        ];
    }
}
