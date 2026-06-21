<?php

namespace App\Services\Chatbot;

use Carbon\CarbonImmutable;

readonly class ChatbotIntentResult
{
    public function __construct(
        public ChatbotIntent $intent,
        public string $original,
        public string $normalized,
        public ?CarbonImmutable $from = null,
        public ?CarbonImmutable $to = null,
        public ?string $status = null,
        public ?string $operation = null,
    ) {
    }
}
