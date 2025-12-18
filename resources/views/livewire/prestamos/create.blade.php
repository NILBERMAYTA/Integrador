<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-[var(--color-on-surface-strong)] dark:text-[var(--color-on-surface-dark-strong)]">Registro de Asignacion</h1>
            <p class="mt-1 text-sm text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] opacity-70">
                Captura prestamos a personal usando el mismo look de Penguin UI.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('prestamos.index') }}" wire:navigate class="inline-flex items-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] border border-[var(--color-outline)] dark:border-[var(--color-outline-dark)] px-5 py-2 text-sm font-medium text-[var(--color-on-surface)] dark:text-[var(--color-on-surface-dark)] transition hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)]">
                Volver
            </a>
            <button type="button" wire:click="save" class="inline-flex items-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] bg-[var(--color-primary)] border border-[var(--color-primary)] px-5 py-2 text-sm font-medium text-[var(--color-on-primary)] hover:opacity-90 transition">
                Guardar asignacion
            </button>
        </div>
    </div>

    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] p-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-[var(--color-on-surface)]">Evento / Operativo</label>
                <select wire:model="evento_id" class="mt-1 block w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 bg-[var(--color-surface)] text-[var(--color-on-surface)]">
                    <option value="">-- Seleccione --</option>
                    @foreach($eventos as $evento)
                        <option value="{{ $evento->id }}">{{ $evento->nombre ?? 'ID '.$evento->id }}</option>
                    @endforeach
                </select>
                @error('evento_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-[var(--color-on-surface)]">Policia que recibe</label>
                <select wire:model="policia_id" class="mt-1 block w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 bg-[var(--color-surface)] text-[var(--color-on-surface)]">
                    <option value="">-- Seleccione --</option>
                    @foreach($policias as $policia)
                        <option value="{{ $policia->id }}">{{ $policia->name }}</option>
                    @endforeach
                </select>
                @error('policia_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-[var(--color-on-surface)]">Fecha de Prestamo</label>
                <input type="text" disabled value="{{ now()->format('Y-m-d H:i') }}" class="mt-1 block w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 bg-transparent text-[var(--color-on-surface)]" />
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium text-[var(--color-on-surface)]">Observaciones (opcional)</label>
            <textarea wire:model="observaciones" rows="2" class="mt-1 block w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 bg-[var(--color-surface)] text-[var(--color-on-surface)]" placeholder="Ej. Prestamo turno noche, unidad UTOP"></textarea>
        </div>
    </div>

    <div class="bg-[var(--color-surface)] dark:bg-[var(--color-surface-dark)] rounded-[var(--radius-radius)] shadow-sm border border-[var(--color-outline)] overflow-hidden">
        <div class="p-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-[var(--color-on-surface-strong)]">Articulos asignados</h3>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="addRow" class="inline-flex items-center gap-2 whitespace-nowrap rounded-[var(--radius-radius)] bg-[var(--color-surface)] border border-[var(--color-outline)] px-4 py-2 text-sm">
                    + Agregar articulo
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[var(--color-surface-alt)] dark:bg-[var(--color-surface-dark-alt)] border-b border-[var(--color-outline)]">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)]">Articulo (catalogo)</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] text-center">Cantidad</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] text-center">Seguimiento</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)]">Series (si aplica)</th>
                        <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface)] text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[var(--color-outline)] dark:divide-[var(--color-outline-dark)] bg-[var(--color-surface)]">
                    @forelse($items as $i => $row)
                        <tr class="hover:bg-[var(--color-surface-alt)] dark:hover:bg-[var(--color-surface-dark-alt)] transition-colors">
                            <td class="px-6 py-3">
                                <select wire:model.live="items.{{ $i }}.articulo_id" class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 bg-[var(--color-surface)]">
                                    <option value="">-- Seleccione articulo --</option>
                                    @foreach($articulos as $articulo)
                                        <option value="{{ $articulo->id }}">{{ $articulo->nombre }}</option>
                                    @endforeach
                                </select>
                                @error("items.{$i}.articulo_id") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </td>
                            <td class="px-6 py-3 text-center">
                                <input type="number" min="1" wire:model.lazy="items.{{ $i }}.cantidad" class="w-20 rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-2 py-1 text-center" />
                                @error("items.{$i}.cantidad") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </td>
                            <td class="px-6 py-3 text-center">
                                @if($row['seguimiento'] === 'serie')
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-bold">SERIE</span>
                                @elseif($row['seguimiento'] === 'cantidad')
                                    <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs font-bold">CANTIDAD</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                                @error("items.{$i}.seguimiento") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </td>
                            <td class="px-6 py-3">
                                @if($row['seguimiento'] === 'serie')
                                    <div class="space-y-1">
                                        @php $series = collect($seriesDisponibles[$row['articulo_id']] ?? []); @endphp
                                        @if($series->count())
                                            <select multiple wire:model="items.{{ $i }}.series" class="w-full rounded-[var(--radius-radius)] border border-[var(--color-outline)] px-3 py-2 bg-[var(--color-surface)] h-24">
                                                @foreach($series as $serie)
                                                    <option value="{{ $serie['id'] }}">{{ $serie['codigo_serie'] }}</option>
                                                @endforeach
                                            </select>
                                            <p class="text-xs text-[var(--color-on-surface)] opacity-70">
                                                Selecciona {{ $row['cantidad'] }} serie(s).
                                            </p>
                                        @else
                                            <p class="text-xs text-[var(--color-on-surface)] opacity-70">No hay series disponibles para este articulo.</p>
                                        @endif
                                        @error("items.{$i}.series") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        @error("items.{$i}.series.*") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                @else
                                    <p class="text-xs text-[var(--color-on-surface)] opacity-60">No requiere series.</p>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                <button type="button" wire:click="removeRow({{ $i }})" class="inline-flex items-center gap-2 rounded-[var(--radius-radius)] border border-[var(--color-danger)] px-3 py-1 text-sm text-[var(--color-danger)]">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-10 w-10 text-[var(--color-on-surface)] opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <p class="mt-4 text-[var(--color-on-surface)] font-medium">No hay articulos agregados</p>
                                <p class="mt-1 text-sm text-[var(--color-on-surface)] opacity-60">Agrega articulos manualmente.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div 
            x-data="yoloCamComponent()"
            x-init="init()"
            class="mt-4"
        >
            <h3 class="font-semibold mb-2">Escáner de artículos (YOLO)</h3>

            <div class="flex gap-4 items-start">
                <video x-ref="video" autoplay playsinline class="border rounded-md w-80 h-56 bg-black"></video>

                <div class="flex flex-col gap-2">
                    <button type="button" class="btn btn-secondary" @click="startCamera()">Iniciar cámara</button>
                    <button type="button" class="btn btn-secondary" @click="stopCamera()">Detener cámara</button>
                    <p class="text-sm text-gray-500" x-text="status"></p>
                    @if(!empty($yoloErrores))
                        <div class="mt-1 text-xs text-red-600 space-y-1">
                            @foreach($yoloErrores as $err)
                                <p>{{ $err }}</p>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <canvas x-ref="canvas" class="hidden"></canvas>
        </div>

        <script>
        function yoloCamComponent() {
            return {
                stream: null,
                processing: false,
                intervalId: null,
                status: 'Cámara detenida',

                init() {
                    // nada por ahora
                },

                startCamera() {
                    if (this.stream) return;

                    navigator.mediaDevices.getUserMedia({ video: true })
                        .then(stream => {
                            this.stream = stream;
                            this.$refs.video.srcObject = stream;
                            this.status = 'Cámara encendida, analizando cada 5s...';
                            this.startLoop();
                        })
                        .catch(err => {
                            console.error(err);
                            this.status = 'No se pudo acceder a la cámara';
                        });
                },

                stopCamera() {
                    if (this.stream) {
                        this.stream.getTracks().forEach(t => t.stop());
                        this.stream = null;
                    }
                    if (this.intervalId) {
                        clearInterval(this.intervalId);
                        this.intervalId = null;
                    }
                    this.status = 'Cámara detenida';
                },

                startLoop() {
                    // captura un frame cada 5000 ms
                    this.intervalId = setInterval(() => {
                        if (!this.processing && this.stream) {
                            this.captureAndSend();
                        }
                    }, 5000);
                },

                captureAndSend() {
                    this.processing = true;

                    const video = this.$refs.video;
                    const canvas = this.$refs.canvas;
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0);

                    canvas.toBlob(blob => {
                        if (!blob) {
                            this.processing = false;
                            return;
                        }
                        const formData = new FormData();
                        formData.append('imagen', blob, 'frame.jpg');

                        // Usamos URL absoluta al endpoint de API (evita error de ruta no definida)
                        const endpoint = '{{ url('/api/detecciones') }}';

                        fetch(endpoint, {
                            method: 'POST',
                            body: formData,
                        })
                        .then(async res => {
                            if (!res.ok) {
                                const text = await res.text();
                                throw new Error(`HTTP ${res.status} - ${text}`);
                            }
                            return res.json();
                        })
                        .then(data => {
                            // data = [{label, count}, ...] o {summary: {...}, detections: [...]}
                            Livewire.find(@this.__instance.id).call('agregarDesdeYolo', { detecciones: data });
                            this.status = 'Detecciones recibidas';
                        })
                        .catch(err => {
                            console.error(err);
                            this.status = `Error al procesar detecciones: ${err.message}`;
                        })
                        .finally(() => {
                            this.processing = false;
                        });
                    }, 'image/jpeg', 0.8);
                }
            }
        }
        </script>

        <div class="px-6 py-4 border-t border-[var(--color-outline)] bg-[var(--color-surface-alt)] flex justify-end gap-3">
            <button type="button" wire:click.prevent="save" class="inline-flex items-center px-4 py-2 rounded-[var(--radius-radius)] bg-primary text-on-primary">
                Guardar asignacion
            </button>
        </div>
    </div>
</div>
