<?php

namespace App\Modules\Usuarios\Services;

use App\Modules\Usuarios\Models\Gestor;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Throwable;
use Illuminate\Support\Facades\Hash;

class GestorService
{
    /**
     * Lista todos os gestores
     */
    public function listarTodos()
    {
        return Gestor::all();
    }

    /**
     * Cria um novo gestor
     *
     * @throws \Exception
     */
    public function criarGestor(array $dados): Gestor
    {
        // Valida se o email já está cadastrado
        $this->validarEmailUnico($dados['email']);

        DB::beginTransaction();

        try {
            // 1. Cria usuário base
            $usuarioId = DB::table('usuarios')->insertGetId(['perfil' => 'G']);

            // 2. Cria gestor
            $gestor = Gestor::create([
                'id_gestor' => $usuarioId,
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha' => Hash::make($dados['senha']),
                'cnpj' => $dados['cnpj'] ?? '',
            ]);

            DB::commit();

            return $gestor;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Busca um gestor por ID
     *
     * @throws ModelNotFoundException
     */
    public function buscarPorId(int $id): Gestor
    {
        return Gestor::findOrFail($id);
    }

    /**
     * Atualiza os dados de um gestor
     *
     * @throws \Exception
     */
    public function atualizar(int $id, array $dados): Gestor
    {
        $gestor = $this->buscarPorId($id);

        // Valida se o email não está sendo usado por outro gestor
        $this->validarEmailUnico($dados['email'], $id);

        // Criptografa a nova senha antes de salvar
        $dados['senha'] = Hash::make($dados['senha']);

        $gestor->update($dados);

        return $gestor;
    }

    /**
     * Remove um gestor
     */
    public function remover(int $id): bool
    {
        return Gestor::destroy($id) > 0;
    }

    /**
     * Valida se o email já está sendo usado por outro gestor
     *
     * @throws \Exception
     */
    private function validarEmailUnico(string $email, ?int $idExcluir = null): void
    {
        $query = Gestor::where('email', $email);

        if ($idExcluir !== null) {
            $query->where('id_gestor', '!=', $idExcluir);
        }

        if ($query->exists()) {
            throw new \Exception($idExcluir === null
                ? 'Já existe um gestor com este email.'
                : 'Já existe outro gestor com este email.');
        }
    }
}
