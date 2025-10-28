<?php

namespace App\Modules\Usuarios\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

abstract class Usuario extends Authenticatable implements JWTSubject
{
    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;

    protected $fillable = [
        'perfil',
    ];

    /**
     * Implementação dos métodos exigidos pelo JWTSubject
     */
    public function getJWTIdentifier()
    {
        // Retorna o identificador primário do usuário (ex: id_usuario)
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        // Inclui informações adicionais no token JWT
        return [
            'perfil' => $this->perfil ?? null,
            'email' => $this->email ?? null,
        ];
    }

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
