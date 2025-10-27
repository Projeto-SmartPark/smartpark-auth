<?php

namespace App\Modules\Usuarios\Services;

use App\Modules\Usuarios\Models\Cliente;
use App\Modules\Usuarios\Models\Gestor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UsuarioService
{
    /**
     * Lista todos os usuários (clientes e gestores)
     */
    public function listarTodos(): array
    {
        return [
            'clientes' => Cliente::all(),
            'gestores' => Gestor::all(),
        ];
    }

    /**
     * Cria um novo usuário (cliente ou gestor)
     *
     * @throws \Exception
     */
    public function criarUsuario(array $dados): array
    {
        // Verifica se email já existe
        $this->validarEmailUnico($dados['email'], $dados['perfil']);

        DB::beginTransaction();

        try {
            // Cria usuário base
            $usuarioId = $this->criarUsuarioBase($dados['perfil']);

            // Cria o tipo específico (Cliente ou Gestor)
            $this->criarTipoEspecifico($dados, $usuarioId);

            DB::commit();

            return [
                'message' => 'Usuário criado com sucesso.',
                'id_usuario' => $usuarioId,
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Busca um usuário por ID
     */
    public function buscarPorId(int $id): ?array
    {
        $dadosUsuario = DB::table('usuarios')->where('id_usuario', $id)->first();

        if (! $dadosUsuario) {
            return null;
        }

        return $this->montarDadosUsuario($dadosUsuario);
    }

    /**
     * Atualiza um usuário (cliente ou gestor)
     *
     * @throws \Exception
     */
    public function atualizarUsuario(int $id, array $dados): bool
    {
        $dadosUsuario = DB::table('usuarios')->where('id_usuario', $id)->first();

        if (! $dadosUsuario) {
            throw new \Exception('Usuário não encontrado.');
        }

        DB::beginTransaction();

        try {
            // Atualiza o tipo específico (Cliente ou Gestor)
            $this->atualizarTipoEspecifico($dadosUsuario->perfil, $id, $dados);

            DB::commit();

            return true;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove um usuário
     *
     * @throws \Exception
     */
    public function remover(int $id): bool
    {
        $dadosUsuario = DB::table('usuarios')->where('id_usuario', $id)->first();

        if (! $dadosUsuario) {
            throw new \Exception('Usuário não encontrado.');
        }

        DB::beginTransaction();

        try {
            $this->removerTipoEspecifico($dadosUsuario->perfil, $id);
            DB::table('usuarios')->where('id_usuario', $id)->delete();

            DB::commit();

            return true;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Valida se o email já está cadastrado
     *
     * @throws \Exception
     */
    private function validarEmailUnico(string $email, string $perfil): void
    {
        if ($perfil === 'C') {
            if (Cliente::where('email', $email)->exists()) {
                throw new \Exception('Já existe um cliente com este email.');
            }
        } else {
            if (Gestor::where('email', $email)->exists()) {
                throw new \Exception('Já existe um gestor com este email.');
            }
        }
    }

    /**
     * Cria o registro base na tabela usuarios
     */
    private function criarUsuarioBase(string $perfil): int
    {
        return DB::table('usuarios')->insertGetId(['perfil' => $perfil]);
    }

    /**
     * Cria o tipo específico (Cliente ou Gestor)
     */
    private function criarTipoEspecifico(array $dados, int $usuarioId): void
    {
        if ($dados['perfil'] === 'C') {
            Cliente::create([
                'id_cliente' => $usuarioId,
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha' => Hash::make($dados['senha']),
            ]);
        } else {
            Gestor::create([
                'id_gestor' => $usuarioId,
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha' => Hash::make($dados['senha']),
                'cnpj' => $dados['cnpj'] ?? '',
            ]);
        }
    }

    /**
     * Monta os dados completos do usuário
     */
    private function montarDadosUsuario(object $dadosUsuario): array
    {
        $dados = null;

        if ($dadosUsuario->perfil === 'C') {
            $dados = Cliente::where('id_cliente', $dadosUsuario->id_usuario)->first();
        } elseif ($dadosUsuario->perfil === 'G') {
            $dados = Gestor::where('id_gestor', $dadosUsuario->id_usuario)->first();
        }

        return [
            'id_usuario' => $dadosUsuario->id_usuario,
            'perfil' => $dadosUsuario->perfil,
            'dados' => $dados,
        ];
    }

    /**
     * Remove o tipo específico (Cliente ou Gestor)
     */
    private function removerTipoEspecifico(string $perfil, int $usuarioId): void
    {
        if ($perfil === 'C') {
            Cliente::where('id_cliente', $usuarioId)->delete();
        } elseif ($perfil === 'G') {
            Gestor::where('id_gestor', $usuarioId)->delete();
        }
    }

    /**
     * Atualiza o tipo específico (Cliente ou Gestor)
     *
     * @throws \Exception
     */
    private function atualizarTipoEspecifico(string $perfil, int $usuarioId, array $dados): void
    {
        if ($perfil === 'C') {
            $cliente = Cliente::where('id_cliente', $usuarioId)->first();

            if (! $cliente) {
                throw new \Exception('Cliente não encontrado.');
            }

            // Valida email único (exceto o próprio)
            if (Cliente::where('email', $dados['email'])
                ->where('id_cliente', '!=', $usuarioId)
                ->exists()) {
                throw new \Exception('Já existe outro cliente com este email.');
            }

            $cliente->update([
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha' => Hash::make($dados['senha']),
            ]);
        } elseif ($perfil === 'G') {
            $gestor = Gestor::where('id_gestor', $usuarioId)->first();

            if (! $gestor) {
                throw new \Exception('Gestor não encontrado.');
            }

            // Valida email único (exceto o próprio)
            if (Gestor::where('email', $dados['email'])
                ->where('id_gestor', '!=', $usuarioId)
                ->exists()) {
                throw new \Exception('Já existe outro gestor com este email.');
            }

            $gestor->update([
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha' => Hash::make($dados['senha']),
                'cnpj' => $dados['cnpj'] ?? $gestor->cnpj,
            ]);
        }
    }
}
