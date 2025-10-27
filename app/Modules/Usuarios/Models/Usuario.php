<?php

namespace App\Modules\Usuarios\Models;

use Illuminate\Database\Eloquent\Model;

abstract class Usuario extends Model
{
    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;

    protected $fillable = [
        'perfil',
    ];

    /**
     * Relacionamento: Um usuário pode ser um cliente
     */
    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'id_cliente', 'id_usuario');
    }

    /**
     * Relacionamento: Um usuário pode ser um gestor
     */
    public function gestor()
    {
        return $this->hasOne(Gestor::class, 'id_gestor', 'id_usuario');
    }
}
