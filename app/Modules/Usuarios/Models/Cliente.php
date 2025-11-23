<?php

namespace App\Modules\Usuarios\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;

class Cliente extends Usuario implements JWTSubject
{
    protected $table = 'clientes';

    protected $primaryKey = 'id_cliente';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'nome',
        'email',
        'senha',
    ];

    protected $hidden = [
        'senha',
    ];

    /**
     * Métodos exigidos pelo JWT
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // retorna o ID do cliente
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'perfil' => 'C',
            'email' => $this->email,
            'nome' => $this->nome,
        ];
    }

    /**
     * Relacionamento com Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_cliente', 'id_usuario');
    }

    /**
     * Verifica se o email já existe em outro cliente
     */
    public static function emailJaExiste(string $email, ?int $idExcluir = null): bool
    {
        $query = self::where('email', $email);

        if ($idExcluir) {
            $query->where('id_cliente', '!=', $idExcluir);
        }

        return $query->exists();
    }

    /**
     * Busca cliente pelo email
     */
    public static function buscarPorEmail(string $email): ?Cliente
    {
        return self::where('email', $email)->first();
    }

    /**
     * Retorna o nome completo formatado
     */
    public function getNomeFormatado(): string
    {
        return ucwords(strtolower($this->nome));
    }
}
