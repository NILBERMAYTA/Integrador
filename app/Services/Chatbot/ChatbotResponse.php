<?php

namespace App\Services\Chatbot;

readonly class ChatbotResponse
{
    /**
     * @param  array<int, string>  $items
     */
    public function __construct(
        public string $text,
        public array $items = [],
    ) {
    }
}
