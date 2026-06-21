<div
    x-data="{ open: false, pending: '' }"
    x-on:keydown.escape.window="open = false"
    x-on:chatbot-message-added.window="pending = ''; $nextTick(() => { $refs.messages.scrollTo({ top: $refs.messages.scrollHeight, behavior: 'smooth' }) })"
>
    <section
        x-cloak
        x-show="open"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="translate-y-3 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-3 opacity-0"
        class="fixed inset-x-3 bottom-24 z-[998] flex max-h-[min(650px,calc(100dvh-7rem))] flex-col overflow-hidden rounded-2xl border border-base-300 bg-base-100 text-base-content shadow-2xl sm:inset-x-auto sm:right-4 sm:w-[400px]"
        role="dialog"
        aria-label="Chat del asistente ARMUTOP"
    >
        <header class="flex items-center gap-3 border-b border-base-300 bg-base-200 px-4 py-3">
            <div class="avatar">
                <div class="w-10 rounded-full bg-white p-1.5 ring-1 ring-base-300">
                    <img src="{{ asset('chatbot.png') }}" alt="Asistente ARMUTOP" class="object-contain">
                </div>
            </div>

            <div class="min-w-0 flex-1">
                <h2 class="truncate text-sm font-bold">Asistente ARMUTOP</h2>
                <p class="flex items-center gap-1.5 text-xs opacity-65">
                    <span class="status status-success"></span>
                    Conectado a datos internos
                </p>
            </div>

            <button
                type="button"
                wire:click="clearConversation"
                class="btn btn-ghost btn-sm btn-circle"
                aria-label="Limpiar conversación"
                title="Limpiar conversación"
            >
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v5M14 11v5"></path>
                </svg>
            </button>

            <button
                type="button"
                class="btn btn-ghost btn-sm btn-circle"
                x-on:click="open = false"
                aria-label="Cerrar chat"
            >
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18"></path>
                </svg>
            </button>
        </header>

        <div x-ref="messages" class="min-h-72 flex-1 overflow-y-auto bg-base-100 px-4 pb-3 pt-0">
            @foreach($messages as $message)
                <div wire:key="chat-message-{{ $message['id'] }}" class="chat chatbot-message-enter {{ $message['from'] === 'user' ? 'chat-end' : 'chat-start' }}">
                    @if($message['from'] === 'bot')
                        <div class="chat-image avatar">
                            <div class="w-9 rounded-full bg-white p-1.5 ring-1 ring-base-300">
                                <img src="{{ asset('chatbot.png') }}" alt="" class="object-contain">
                            </div>
                        </div>
                    @endif

                    <div @class([
                        'chat-bubble max-w-[84%] text-sm leading-5',
                        'chat-bubble-primary' => $message['from'] === 'user',
                    ])>
                        <p class="whitespace-pre-line">{{ $message['text'] }}</p>

                        @if($message['items'] ?? [])
                            <ul class="list mt-2 gap-1 rounded-xl bg-base-200/55 p-1">
                                @foreach($message['items'] as $item)
                                    <li class="list-row min-h-0 grid-cols-[auto_1fr] gap-2 px-2 py-1.5 text-xs">
                                        <span class="status status-primary status-xs mt-1"></span>
                                        <span class="list-col-grow leading-4">{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endforeach

            <div wire:loading.flex wire:target="send,ask" class="flex-col">
                <div x-cloak x-show="pending" class="chat chat-end chatbot-message-enter w-full">
                    <div class="chat-bubble chat-bubble-primary max-w-[84%] text-sm leading-5" x-text="pending"></div>
                </div>

                <div class="chat chat-start chatbot-typing-enter w-full">
                    <div class="chat-image avatar">
                        <div class="w-9 rounded-full bg-white p-1.5 ring-1 ring-base-300">
                            <img src="{{ asset('chatbot.png') }}" alt="" class="object-contain">
                        </div>
                    </div>
                    <div class="chat-bubble flex items-center gap-2" aria-label="El asistente está escribiendo">
                        <span class="loading loading-dots loading-sm"></span>
                        <span class="text-xs opacity-70">Escribiendo</span>
                    </div>
                </div>
            </div>

        </div>

        <form
            wire:submit="send"
            x-on:submit="pending = $refs.chatInput.value.trim(); $nextTick(() => { $refs.messages.scrollTo({ top: $refs.messages.scrollHeight, behavior: 'smooth' }) })"
            class="border-t border-base-300 bg-base-200 p-3"
        >
            <div class="flex gap-2">
                <input
                    type="text"
                    wire:model="draft"
                    x-ref="chatInput"
                    class="input input-bordered min-w-0 flex-1 bg-base-100"
                    placeholder="Pregunta sobre inventario u operaciones..."
                    maxlength="500"
                    autocomplete="off"
                    aria-label="Mensaje para el asistente"
                >
                <button
                    type="submit"
                    class="btn btn-primary btn-square"
                    wire:loading.attr="disabled"
                    wire:target="send,ask"
                    aria-label="Enviar mensaje"
                >
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="m22 2-7 20-4-9-9-4Z"></path>
                        <path d="M22 2 11 13"></path>
                    </svg>
                </button>
            </div>
            @error('draft')
                <p class="mt-1 text-xs text-error">{{ $message }}</p>
            @enderror
        </form>
    </section>

    <div class="fab">
        <button
            type="button"
            class="btn btn-lg btn-circle btn-primary shadow-xl transition-transform duration-200 hover:scale-105 active:scale-95"
            x-on:click="open = ! open; if (open) $nextTick(() => { $refs.messages.scrollTop = $refs.messages.scrollHeight })"
            x-bind:aria-expanded="open"
            aria-label="Abrir chat de ARMUTOP"
        >
            <img
                x-show="! open"
                src="{{ asset('chatbot.png') }}"
                alt=""
                class="size-8 object-contain"
            >
            <svg x-cloak x-show="open" class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M6 6l12 12M18 6 6 18"></path>
            </svg>
        </button>
    </div>
</div>
