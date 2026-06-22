<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use SplFileObject;
use Throwable;

class ImportPersonalCommand extends Command
{
    protected $signature = 'personal:import
        {directory : Directorio que contiene unidades.csv, destinos.csv y usuarios.csv}
        {--dry-run : Valida los archivos sin escribir en la base de datos}
        {--chunk=250 : Cantidad de usuarios por lote}
        {--workers=8 : Procesos paralelos para usuarios}
        {--bcrypt-cost=10 : Costo bcrypt de la contraseña inicial}';

    protected $description = 'Importa unidades, destinos y personal limpiados desde el XLS de abril de 2026';

    public function handle(): int
    {
        $directory = base_path((string) $this->argument('directory'));
        if (str_starts_with((string) $this->argument('directory'), '/')) {
            $directory = (string) $this->argument('directory');
        }

        $paths = [
            'unidades' => $directory.'/unidades.csv',
            'destinos' => $directory.'/destinos.csv',
            'usuarios' => $directory.'/usuarios.csv',
        ];

        foreach ($paths as $name => $path) {
            if (! is_file($path)) {
                $this->error("No existe {$name}.csv en {$directory}");

                return self::FAILURE;
            }
        }

        $counts = [
            'unidades' => $this->countCsvRows($paths['unidades']),
            'destinos' => $this->countCsvRows($paths['destinos']),
            'usuarios' => $this->countCsvRows($paths['usuarios']),
        ];

        $this->table(['Archivo', 'Registros'], [
            ['unidades.csv', $counts['unidades']],
            ['destinos.csv', $counts['destinos']],
            ['usuarios.csv', $counts['usuarios']],
        ]);

        if ((bool) $this->option('dry-run')) {
            $this->info('Validación completada. No se modificó la base de datos.');

            return self::SUCCESS;
        }

        $chunkSize = max(50, (int) $this->option('chunk'));
        $workers = max(1, min(16, (int) $this->option('workers')));
        $bcryptCost = max(10, min(14, (int) $this->option('bcrypt-cost')));

        try {
            DB::transaction(function () use ($paths): void {
                $this->importUnits($paths['unidades']);
                $this->importDestinations($paths['destinos']);
            });

            $this->info('Unidades y destinos importados.');

            if ($workers > 1 && function_exists('pcntl_fork')) {
                $result = $this->importUsersInParallel(
                    $paths['usuarios'],
                    $chunkSize,
                    $workers,
                    $bcryptCost,
                );
            } else {
                $result = $this->importUsers(
                    $paths['usuarios'],
                    $chunkSize,
                    0,
                    1,
                    $bcryptCost,
                );
            }

            if ($result !== self::SUCCESS) {
                return $result;
            }
        } catch (Throwable $exception) {
            report($exception);
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Importación completada correctamente.');
        $this->line('La contraseña inicial es NUM ESC. y quedó almacenada con bcrypt.');

        return self::SUCCESS;
    }

    private function importUnits(string $path): void
    {
        foreach ($this->csvRows($path) as $row) {
            $existing = DB::table('unidades')
                ->where('codigo_externo', $row['codigo_externo'])
                ->orWhere('nombre', $row['nombre'])
                ->first();

            $values = [
                'nombre' => $row['nombre'],
                'codigo_externo' => $row['codigo_externo'],
                'sigla' => $this->nullIfEmpty($row['sigla']),
                'descripcion' => $this->nullIfEmpty($row['descripcion']),
                'deleted_at' => null,
                'updated_at' => now(),
            ];

            if ($existing) {
                DB::table('unidades')->where('id', $existing->id)->update($values);
            } else {
                DB::table('unidades')->insert($values + ['created_at' => now()]);
            }
        }
    }

    private function importDestinations(string $path): void
    {
        $units = DB::table('unidades')->pluck('id', 'codigo_externo');

        foreach ($this->csvRows($path) as $row) {
            $unitId = $units->get($row['unidad_codigo_externo']);
            if (! $unitId) {
                throw new RuntimeException(
                    "No existe la unidad {$row['unidad_codigo_externo']} para el destino {$row['nombre']}."
                );
            }

            DB::table('destinos')->updateOrInsert(
                [
                    'unidad_id' => $unitId,
                    'nombre' => $row['nombre'],
                ],
                [
                    'descripcion' => $this->nullIfEmpty($row['descripcion']),
                    'deleted_at' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }

    private function importUsersInParallel(
        string $path,
        int $chunkSize,
        int $workers,
        int $bcryptCost,
    ): int {
        $this->info("Importando usuarios con {$workers} procesos...");
        $children = [];

        DB::disconnect();

        for ($worker = 0; $worker < $workers; $worker++) {
            $pid = pcntl_fork();

            if ($pid === -1) {
                throw new RuntimeException('No se pudo crear un proceso de importación.');
            }

            if ($pid === 0) {
                DB::purge();
                DB::reconnect();

                try {
                    $status = $this->importUsers(
                        $path,
                        $chunkSize,
                        $worker,
                        $workers,
                        $bcryptCost,
                    );
                } catch (Throwable $exception) {
                    fwrite(STDERR, "Trabajador {$worker}: {$exception->getMessage()}".PHP_EOL);
                    $status = self::FAILURE;
                }

                exit($status);
            }

            $children[$pid] = $worker;
        }

        $failed = false;
        foreach ($children as $pid => $worker) {
            pcntl_waitpid($pid, $status);
            if (! pcntl_wifexited($status) || pcntl_wexitstatus($status) !== self::SUCCESS) {
                $this->error("El trabajador {$worker} terminó con errores.");
                $failed = true;
            }
        }

        DB::purge();
        DB::reconnect();

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function importUsers(
        string $path,
        int $chunkSize,
        int $worker,
        int $workers,
        int $bcryptCost,
    ): int {
        $units = DB::table('unidades')->pluck('id', 'codigo_externo');
        $destinations = DB::table('destinos')
            ->join('unidades', 'unidades.id', '=', 'destinos.unidad_id')
            ->get([
                'destinos.id',
                'destinos.nombre',
                'unidades.codigo_externo',
            ])
            ->mapWithKeys(fn ($item) => [
                $item->codigo_externo."\x1F".$item->nombre => $item->id,
            ]);

        $roleId = DB::table('roles')->where('name', 'policia')->value('id');
        if (! $roleId) {
            throw new RuntimeException('No existe el rol policia. Ejecute primero la configuración de roles.');
        }

        $batch = [];
        $processed = 0;
        $rowNumber = 0;

        foreach ($this->csvRows($path) as $row) {
            if (($rowNumber++ % $workers) !== $worker) {
                continue;
            }

            $unitId = $units->get($row['unidad_codigo_externo']);
            $destinationId = $destinations->get(
                $row['unidad_codigo_externo']."\x1F".$row['destino_nombre']
            );

            if (! $unitId || ! $destinationId) {
                throw new RuntimeException("Unidad o destino inválido para la cédula {$row['cedula']}.");
            }

            $email = $this->resolveEmail($row['email'], $row['cedula']);
            $password = $this->nullIfEmpty($row['numero_escalafon']);

            $batch[] = [
                'cedula' => $row['cedula'],
                'nivel' => $this->nullIfEmpty($row['nivel']),
                'rango' => $this->nullIfEmpty($row['rango']),
                'rango_codigo' => $this->nullIfEmpty($row['rango_codigo']),
                'grado_codigo' => $this->nullIfEmpty($row['grado_codigo']),
                'apellido_paterno' => $this->nullIfEmpty($row['apellido_paterno']),
                'apellido_materno' => $this->nullIfEmpty($row['apellido_materno']),
                'name' => $row['name'],
                'cargo' => $this->nullIfEmpty($row['cargo']),
                'unidad_id' => $unitId,
                'destino_id' => $destinationId,
                'post_grado_codigo_1' => $this->nullIfEmpty($row['post_grado_codigo_1']),
                'fecha_nacimiento' => $this->nullIfEmpty($row['fecha_nacimiento']),
                'fecha_ingreso' => $this->nullIfEmpty($row['fecha_ingreso']),
                'categoria_codigo' => $this->nullIfEmpty($row['categoria_codigo']),
                'post_grado_codigo_2' => $this->nullIfEmpty($row['post_grado_codigo_2']),
                'marca' => $this->nullIfEmpty($row['marca']),
                'expedido' => $this->nullIfEmpty($row['expedido']),
                'sexo' => $this->nullIfEmpty($row['sexo']),
                'promocion' => $this->nullIfEmpty($row['promocion']),
                'numero_escalafon' => $this->nullIfEmpty($row['numero_escalafon']),
                'celular' => $this->nullIfEmpty($row['celular']),
                'sigep' => $this->nullIfEmpty($row['sigep']),
                'salida_haberes_codigo' => $this->nullIfEmpty($row['salida_haberes_codigo']),
                'email' => $email,
                'password' => $password === null
                    ? null
                    : password_hash($password, PASSWORD_BCRYPT, ['cost' => $bcryptCost]),
                'role' => 'policia',
                'can_login' => $password !== null,
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $chunkSize) {
                $this->persistUserBatch($batch, (int) $roleId);
                $processed += count($batch);
                $batch = [];
                fwrite(STDOUT, "Trabajador {$worker}: {$processed} usuarios".PHP_EOL);
            }
        }

        if ($batch !== []) {
            $this->persistUserBatch($batch, (int) $roleId);
            $processed += count($batch);
        }

        fwrite(STDOUT, "Trabajador {$worker} finalizó: {$processed} usuarios".PHP_EOL);

        return self::SUCCESS;
    }

    private function persistUserBatch(array $batch, int $roleId): void
    {
        DB::transaction(function () use ($batch, $roleId): void {
            $updateColumns = array_values(array_diff(
                array_keys($batch[0]),
                ['cedula', 'created_at'],
            ));

            DB::table('users')->upsert($batch, ['cedula'], $updateColumns);

            $cedulas = array_column($batch, 'cedula');
            $users = DB::table('users')
                ->whereIn('cedula', $cedulas)
                ->get(['id', 'unidad_id']);

            DB::table('model_has_roles')->insertOrIgnore(
                $users->map(fn ($user) => [
                    'role_id' => $roleId,
                    'model_type' => User::class,
                    'model_id' => $user->id,
                ])->all()
            );

            $usersWithHistory = DB::table('user_unidad_asignaciones')
                ->whereIn('user_id', $users->pluck('id'))
                ->pluck('user_id')
                ->all();
            $historyLookup = array_fill_keys($usersWithHistory, true);
            $now = now();

            $historyRows = $users
                ->reject(fn ($user) => isset($historyLookup[$user->id]))
                ->map(fn ($user) => [
                    'user_id' => $user->id,
                    'unidad_origen_id' => null,
                    'unidad_destino_id' => $user->unidad_id,
                    'transferido_por' => $user->id,
                    'fecha_transferencia' => $now,
                    'motivo' => 'Importación inicial de personal abril 2026',
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all();

            if ($historyRows !== []) {
                DB::table('user_unidad_asignaciones')->insert($historyRows);
            }
        }, 3);
    }

    private function resolveEmail(string $email, string $cedula): string
    {
        $owner = DB::table('users')
            ->where('email', $email)
            ->first(['cedula']);

        if ($owner === null || $owner->cedula === $cedula) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $candidate = "{$local}.{$cedula}@{$domain}";
        $counter = 2;

        while (DB::table('users')->where('email', $candidate)->exists()) {
            $candidate = "{$local}.{$cedula}.{$counter}@{$domain}";
            $counter++;
        }

        return $candidate;
    }

    private function csvRows(string $path): iterable
    {
        $file = new SplFileObject($path, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);
        $headers = null;

        foreach ($file as $row) {
            if ($row === false || $row === [null]) {
                continue;
            }

            if ($headers === null) {
                $headers = array_map(
                    fn ($value) => ltrim((string) $value, "\xEF\xBB\xBF"),
                    $row,
                );
                continue;
            }

            if (count($row) !== count($headers)) {
                throw new RuntimeException("Fila CSV inválida en {$path}.");
            }

            yield array_combine($headers, $row);
        }
    }

    private function countCsvRows(string $path): int
    {
        $count = 0;
        foreach ($this->csvRows($path) as $_row) {
            $count++;
        }

        return $count;
    }

    private function nullIfEmpty(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
