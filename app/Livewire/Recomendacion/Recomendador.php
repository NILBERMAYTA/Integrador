<?php

namespace App\Livewire\Recomendacion;

use Livewire\Component;

class Recomendador extends Component
{
    public $tipo_conflicto = '';
    public $agresividad = '';
    public $cantidad_personas = '';
    public $resultado = null;

    public $tipos = [
        'manifestacion' => 'Manifestacion pacifica',
        'bloqueo'       => 'Bloqueo de ruta',
        'motin'         => 'Motin / disturbio civil',
        'operativo'     => 'Allanamiento / operativo especifico',
    ];

    public $agresividades = [
        'bajo' => 'Bajo (pacifico)',
        'medio' => 'Medio (gritos/insultos)',
        'alto' => 'Alto (armas/piedras)',
    ];

    public $rangosPersonas = [
        '50' => '1 - 50',
        '200' => '50 - 200',
        '500' => '200 - 500',
        '1000' => 'Masivo (+1000)',
    ];

    public function updated()
    {
        if ($this->tipo_conflicto && $this->agresividad && $this->cantidad_personas) {
            $this->calcularFuerza();
        }
    }

    public function calcularFuerza()
    {
        $ratio = match($this->agresividad) {
            'bajo' => 0.10,
            'medio' => 0.20,
            'alto' => 0.50,
            default => 0.10
        };

        $estimado_personas = (int) $this->cantidad_personas;
        $personal_necesario = ceil($estimado_personas * $ratio);
        if ($personal_necesario < 4) {
            $personal_necesario = 4;
        }

        $equipo = [];

        if ($this->tipo_conflicto === 'manifestacion') {
            $equipo[] = 'Radios de comunicacion';
            if ($this->agresividad === 'alto') {
                $equipo[] = 'Escudos (100% del personal)';
                $equipo[] = 'Cascos (100% del personal)';
            }
        } elseif (in_array($this->tipo_conflicto, ['bloqueo', 'motin'])) {
            $equipo[] = 'Equipo antidisturbios completo (casco, escudo, rodilleras)';
            $equipo[] = 'Agentes quimicos (gas)';
            if ($this->agresividad === 'alto') {
                $equipo[] = 'Vehiculo Neptuno/Carro hidrante';
                $equipo[] = 'Municion no letal (goma)';
            }
        } elseif ($this->tipo_conflicto === 'operativo') {
            $equipo[] = 'Chalecos balisticos (obligatorio)';
            $equipo[] = 'Armamento letal (reglamentario)';
            $equipo[] = 'Vehiculo blindado';
        }

        $this->resultado = [
            'personal' => $personal_necesario,
            'equipo' => $equipo,
            'alerta' => $this->agresividad === 'alto' ? 'ALTO RIESGO' : 'RIESGO CONTROLADO',
        ];
    }

    public function resetForm()
    {
        $this->reset(['tipo_conflicto', 'agresividad', 'cantidad_personas', 'resultado']);
    }

    public function render()
    {
        return view('livewire.recomendacion.recomendador');
    }
}
