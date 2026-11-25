<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombres', 'apellidos', 'dni', 'telefono', 'direccion'])]
class Cliente extends Model
{
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }
}