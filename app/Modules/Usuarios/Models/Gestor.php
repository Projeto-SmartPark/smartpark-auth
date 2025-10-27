<?php

namespace App\Modules\Usuarios\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;

class Gestor extends Usuario
{
    protected $table = 'gestores';

    protected $primaryKey = 'id_gestor';

     public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_gestor',
        'nome',
        'email',
        'senha',
        'cnpj',
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
            'perfil' => 'G',
            'email' => $this->email,
            'cnpj' => $this->cnpj,
        ];
    }

    /**
     * Relacionamento com Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_gestor', 'id_usuario');
    }

    /**
     * Verifica se o email já existe em outro gestor
     */
    public static function emailJaExiste(string $email, ?int $idExcluir = null): bool
    {
        $query = self::where('email', $email);

        if ($idExcluir) {
            $query->where('id_gestor', '!=', $idExcluir);
        }

        return $query->exists();
    }

    /**
     * Busca gestor pelo email
     */
    public static function buscarPorEmail(string $email): ?Gestor
    {
        return self::where('email', $email)->first();
    }

    /**
     * Busca gestor pelo CNPJ
     */
    public static function buscarPorCnpj(string $cnpj): ?Gestor
    {
        return self::where('cnpj', $cnpj)->first();
    }

    /**
     * Retorna o nome completo formatado
     */
    public function getNomeFormatado(): string
    {
        return ucwords(strtolower($this->nome));
    }

    /**
     * Retorna o CNPJ formatado
     */
    public function getCnpjFormatado(): string
    {
        $cnpj = preg_replace('/\D/', '', $this->cnpj);

        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }
}
