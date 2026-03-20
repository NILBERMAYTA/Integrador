<?php

namespace App\Services;

use App\Models\Operacion;
use App\Models\User;
use App\Models\UserUnidadAsignacion;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UserTransferService
{
    public function transfer(User $actor, User $user, int $unidadDestinoId, ?string $motivo = null): User
    {
        if (! $actor->isAdministradorGeneral()) {
            throw new RuntimeException('Solo un administrador general puede transferir usuarios entre unidades.');
        }

        if ((int) $user->unidad_id === $unidadDestinoId) {
            throw new RuntimeException('El usuario ya pertenece a la unidad seleccionada.');
        }

        if ($this->hasOpenAssignments($user)) {
            throw new RuntimeException('El usuario tiene asignaciones activas y no puede ser transferido.');
        }

        return DB::transaction(function () use ($actor, $user, $unidadDestinoId, $motivo) {
            $origenId = $user->unidad_id;

            $user->forceFill([
                'unidad_id' => $unidadDestinoId,
            ])->save();

            $history = UserUnidadAsignacion::create([
                'user_id' => $user->id,
                'unidad_origen_id' => $origenId,
                'unidad_destino_id' => $unidadDestinoId,
                'transferido_por' => $actor->id,
                'fecha_transferencia' => now(),
                'motivo' => $motivo ?: 'Transferencia de unidad',
            ]);

            activity()
                ->performedOn($user)
                ->causedBy($actor)
                ->withProperties([
                    'unidad_origen_id' => $origenId,
                    'unidad_destino_id' => $unidadDestinoId,
                    'motivo' => $motivo,
                    'historial_id' => $history->id,
                ])
                ->event('user_unit_transfer')
                ->log('Transferencia de usuario entre unidades');

            return $user->refresh();
        });
    }

    public function registerInitialAssignment(User $actor, User $user, int $unidadDestinoId, ?string $motivo = null): UserUnidadAsignacion
    {
        return UserUnidadAsignacion::create([
            'user_id' => $user->id,
            'unidad_origen_id' => null,
            'unidad_destino_id' => $unidadDestinoId,
            'transferido_por' => $actor->id,
            'fecha_transferencia' => $user->created_at ?? now(),
            'motivo' => $motivo ?: 'Asignacion inicial de unidad',
        ]);
    }

    private function hasOpenAssignments(User $user): bool
    {
        return Operacion::query()
            ->where('tipo', 'asignacion')
            ->where('usuario_destino_id', $user->id)
            ->whereDoesntHave('devoluciones')
            ->exists();
    }
}
