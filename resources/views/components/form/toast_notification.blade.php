@props([
    'message' => null,
    'title' => null,
    'variant' => 'info',
    'senderName' => null,
    'senderAvatar' => null,
])

<div
    x-data="{
        notifications: [],
        timers: new Map(),
        displayDuration: 4000,

        addNotification({ variant = 'info', sender = null, title = null, message = null }) {
            if (! message && ! title) return;

            const id = `${Date.now()}-${Math.random()}`;

            this.notifications.push({ id, variant, sender, title, message });
            this.startTimer(id);
        },

        startTimer(id) {
            this.stopTimer(id);
            this.timers.set(id, window.setTimeout(() => this.removeNotification(id), this.displayDuration));
        },

        stopTimer(id) {
            const timer = this.timers.get(id);

            if (timer) {
                window.clearTimeout(timer);
                this.timers.delete(id);
            }
        },

        removeNotification(id) {
            this.stopTimer(id);
            this.notifications = this.notifications.filter((notification) => notification.id !== id);
        },

        alertClass(variant) {
            return {
                info: 'alert-info',
                success: 'alert-success',
                warning: 'alert-warning',
                danger: 'alert-error',
                error: 'alert-error',
                message: '',
            }[variant] ?? 'alert-info';
        },
    }"
    x-init="
        @if($message)
            addNotification({
                variant: @js($variant),
                title: @js($title),
                message: @js($message),
                sender: { name: @js($senderName), avatar: @js($senderAvatar) },
            });
        @endif
    "
    x-on:notify.window="addNotification({
        variant: $event.detail.variant,
        sender: $event.detail.sender,
        title: $event.detail.title,
        message: $event.detail.message,
    })"
>
    <div class="toast toast-top toast-end z-[1000] w-full max-w-sm pt-4 sm:w-auto">
        <template x-for="notification in notifications" :key="notification.id">
            <div
                role="alert"
                class="alert alert-soft pointer-events-auto grid-cols-[auto_1fr_auto] shadow-lg"
                x-bind:class="alertClass(notification.variant)"
                x-on:mouseenter="stopTimer(notification.id)"
                x-on:mouseleave="startTimer(notification.id)"
                x-transition:enter="transition duration-200 ease-out"
                x-transition:enter-start="translate-y-2 opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition duration-200 ease-in"
                x-transition:leave-start="translate-y-0 opacity-100"
                x-transition:leave-end="-translate-y-2 opacity-0"
            >
                <div class="flex size-6 items-center justify-center" aria-hidden="true">
                    <template x-if="notification.variant === 'success'">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/>
                        </svg>
                    </template>

                    <template x-if="notification.variant === 'danger' || notification.variant === 'error' || notification.variant === 'warning'">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                        </svg>
                    </template>

                    <template x-if="!['success', 'danger', 'error', 'warning'].includes(notification.variant)">
                        <svg class="size-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd"/>
                        </svg>
                    </template>
                </div>

                <div class="min-w-0">
                    <p x-show="notification.title" class="text-sm font-semibold" x-text="notification.title"></p>
                    <p x-show="notification.message" class="text-pretty text-sm" x-text="notification.message"></p>
                </div>

                <button
                    type="button"
                    class="btn btn-ghost btn-xs btn-circle"
                    x-on:click="removeNotification(notification.id)"
                    aria-label="Cerrar notificación"
                >
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18"></path>
                    </svg>
                </button>
            </div>
        </template>
    </div>
</div>
