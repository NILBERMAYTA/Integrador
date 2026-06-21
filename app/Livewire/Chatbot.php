<?php

namespace App\Livewire;

use App\Services\Chatbot\ChatbotService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Throwable;

class Chatbot extends Component
{
    public string $draft = '';

    /**
     * @var array<int, array{id: string, from: string, text: string, items: array<int, string>}>
     */
    public array $messages = [];

    public function mount(): void
    {
        $this->messages = [
            $this->message(
                'bot',
                'Hola, soy el asistente de ARMUTOP. Puedo consultar información operativa respetando tu unidad y permisos.'
            ),
        ];

    }

    public function send(ChatbotService $chatbot): void
    {
        $data = $this->validate([
            'draft' => ['required', 'string', 'max:500'],
        ], [
            'draft.required' => 'Escribe una pregunta.',
            'draft.max' => 'La pregunta no puede superar los 500 caracteres.',
        ]);

        $question = trim($data['draft']);
        $this->draft = '';
        $this->messages[] = $this->message('user', $question);

        try {
            usleep(500_000);

            $response = $chatbot->answer($question, auth()->user());
            $this->messages[] = $this->message('bot', $response->text, $response->items);
        } catch (Throwable $exception) {
            Log::error('Chatbot query failed', [
                'user_id' => auth()->id(),
                'question' => $question,
                'exception' => $exception,
            ]);

            $this->messages[] = $this->message(
                'bot',
                'No pude consultar la información en este momento. Inténtalo nuevamente en unos segundos.'
            );
        }

        $this->messages = array_slice($this->messages, -30);
        $this->dispatch('chatbot-message-added');
    }

    public function ask(string $question, ChatbotService $chatbot): void
    {
        $this->draft = $question;
        $this->send($chatbot);
    }

    public function clearConversation(): void
    {
        $this->reset(['draft', 'messages']);
        $this->mount();
        $this->dispatch('chatbot-message-added');
    }

    public function render()
    {
        return view('livewire.chatbot');
    }

    /**
     * @param  array<int, string>  $items
     * @return array{id: string, from: string, text: string, items: array<int, string>}
     */
    private function message(string $from, string $text, array $items = []): array
    {
        return [
            'id' => (string) str()->uuid(),
            'from' => $from,
            'text' => $text,
            'items' => $items,
        ];
    }
}
