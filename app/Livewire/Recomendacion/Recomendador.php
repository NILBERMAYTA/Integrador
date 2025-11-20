<?php

namespace App\Livewire\Recomendacion;

use Livewire\Component;

class Recomendador extends Component
{
    // Inputs
    public $tipo_conflicto = '';
    public $agresividad = '';
    public $cantidad_personas = '';

    // Output
    public $resultado = null;

    // Opciones para los selects
    public $tipos = [
        'manifestacion' => 'Manifestación Pacífica',
        'bloqueo'       => 'Bloqueo de Ruta',
        'motin'         => 'Motín / Disturbio Civil',
        'operativo'     => 'Allanamiento / Operativo Específico'
    ];

    // Escuchar cambios en los inputs
    public function updated()
    {
        if ($this->tipo_conflicto && $this->agresividad && $this->cantidad_personas) {
            $this->calcularFuerza();
        }
    }

    public function calcularFuerza()
    {
        // Lógica base: 1 policía por cada X manifestantes según agresividad
        $ratio = match($this->agresividad) {
            'bajo' => 0.10,  // 1 poli por cada 10 personas
            'medio' => 0.20, // 1 poli por cada 5 personas
            'alto' => 0.50,  // 1 poli por cada 2 personas
            default => 0.10
        };

        // Cálculo base de personal
        $estimado_personas = (int) $this->cantidad_personas; // Asumiendo que el value es el número max del rango
        $personal_necesario = ceil($estimado_personas * $ratio);
        
        // Asegurar mínimo de patrulla
        if ($personal_necesario < 4) $personal_necesario = 4;

        // Equipo recomendado según tipo y agresividad
        $equipo = [];
        
        if ($this->tipo_conflicto == 'manifestacion') {
            $equipo[] = 'Radios de comunicación';
            if ($this->agresividad == 'alto') {
                $equipo[] = 'Escudos (100% del personal)';
                $equipo[] = 'Cascos (100% del personal)';
            }
        } elseif ($this->tipo_conflicto == 'bloqueo' || $this->tipo_conflicto == 'motin') {
            $equipo[] = 'Equipo Antidisturbios Completo (Casco, Escudo, Rodilleras)';
            $equipo[] = 'Agentes Químicos (Gas)';
            if ($this->agresividad == 'alto') {
                $equipo[] = 'Vehículo Neptuno/Carro Hidrante';
                $equipo[] = 'Munición no letal (Goma)';
            }
        } elseif ($this->tipo_conflicto == 'operativo') {
            $equipo[] = 'Chalecos Balísticos (Obligatorio)';
            $equipo[] = 'Armamento Letal (Reglamentario)';
            $equipo[] = 'Vehículo Blindado';
        }

        $this->resultado = [
            'personal' => $personal_necesario,
            'equipo' => $equipo,
            'alerta' => $this->agresividad == 'alto' ? 'ALTO RIESGO' : 'RIESGO CONTROLADO'
        ];
    }

    public function render()
    {
        return view('livewire.recomendacion.recomendador');
    }
}