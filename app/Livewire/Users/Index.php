<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.users.index', [
            //'users' => User::orderByApellidos()->simplePaginate(8),
            'users' => User::latest()->simplePaginate(8),
        ]);
    }
}
