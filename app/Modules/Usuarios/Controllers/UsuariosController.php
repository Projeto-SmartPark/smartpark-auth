<?php

namespace App\Modules\Usuarios\Controllers;

use App\Modules\Usuarios\Services\UsuarioService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Throwable;

class UsuariosController extends Controller
{
    private UsuarioService $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    /**
     * @OA\Get(
     *     path="/usuarios",
     *     tags={"Usuários"},
     *     summary="Lista todos os usuários",
     *     description="Retorna uma lista com todos os clientes e gestores cadastrados",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuários retornada com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="clientes", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="gestores", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $usuarios = $this->usuarioService->listarTodos();

        return response()->json($usuarios);
    }

    /**
     * @OA\Post(
     *     path="/usuarios",
     *     tags={"Usuários"},
     *     summary="Cadastra um novo usuário",
     *     description="Cria um novo cliente ou gestor no sistema",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"perfil", "nome", "email", "senha"},
     *
     *             @OA\Property(property="perfil", type="string", enum={"C", "G"}, example="C", description="C = Cliente, G = Gestor"),
     *             @OA\Property(property="nome", type="string", maxLength=100, example="João Silva"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=100, example="joao@exemplo.com"),
     *             @OA\Property(property="senha", type="string", maxLength=100, example="senha123"),
     *             @OA\Property(property="cnpj", type="string", maxLength=20, example="12345678000190", description="Apenas números. Obrigatório apenas para gestores")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Usuário criado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Usuário criado com sucesso.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="Email já cadastrado",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Email já cadastrado."),
     *             @OA\Property(property="message", type="string", example="Já existe um cliente com este email.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Dados inválidos",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Dados inválidos."),
     *             @OA\Property(property="message", type="string", example="Os dados fornecidos não são válidos."),
     *             @OA\Property(property="errors", type="object", example={"email": {"O campo email é obrigatório."}})
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erro no servidor",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Erro no servidor."),
     *             @OA\Property(property="message", type="string", example="Ocorreu um erro inesperado ao processar a requisição.")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'perfil' => 'required|in:C,G',
            'nome' => 'required|string|min:3|max:100',
            'email' => 'required|email|max:100',
            'senha' => 'required|string|min:6|max:100',
            'cnpj' => 'nullable|string|min:14|max:20',
        ], [
            'perfil.required' => 'O campo perfil é obrigatório.',
            'perfil.in' => 'O campo perfil deve ser C (Cliente) ou G (Gestor).',
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.string' => 'O campo nome deve ser um texto.',
            'nome.min' => 'O campo nome deve ter no mínimo 3 caracteres.',
            'nome.max' => 'O campo nome não pode ter mais de 100 caracteres.',
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'O campo email deve ser um endereço de email válido.',
            'email.max' => 'O campo email não pode ter mais de 100 caracteres.',
            'senha.required' => 'O campo senha é obrigatório.',
            'senha.string' => 'O campo senha deve ser um texto.',
            'senha.min' => 'O campo senha deve ter no mínimo 6 caracteres.',
            'senha.max' => 'O campo senha não pode ter mais de 100 caracteres.',
            'cnpj.string' => 'O campo CNPJ deve ser um texto.',
            'cnpj.min' => 'O campo CNPJ deve ter no mínimo 14 caracteres.',
            'cnpj.max' => 'O campo CNPJ não pode ter mais de 20 caracteres.',
        ]);

        try {
            $resultado = $this->usuarioService->criarUsuario($dados);

            return response()->json($resultado, 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Email já cadastrado.',
                'message' => $e->getMessage(),
            ], 409);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Erro no servidor.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/usuarios/{id}",
     *     tags={"Usuários"},
     *     summary="Exibe um usuário específico",
     *     description="Retorna os dados detalhados de um usuário (cliente ou gestor)",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do usuário",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Dados do usuário retornados com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="id_usuario", type="integer", example=1),
     *             @OA\Property(property="perfil", type="string", example="C"),
     *             @OA\Property(property="dados", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Usuário não encontrado",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Usuário não encontrado."),
     *             @OA\Property(property="message", type="string", example="O usuário com o ID informado não existe.")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $usuario = $this->usuarioService->buscarPorId($id);

        if (! $usuario) {
            return response()->json([
                'error' => 'Usuário não encontrado.',
                'message' => 'O usuário com o ID informado não existe.',
            ], 404);
        }

        return response()->json($usuario);
    }

    /**
     * @OA\Put(
     *     path="/usuarios/{id}",
     *     tags={"Usuários"},
     *     summary="Atualiza dados de um usuário",
     *     description="Atualiza as informações de um cliente ou gestor existente com validação de email único",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do usuário",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"nome", "email", "senha"},
     *
     *             @OA\Property(property="nome", type="string", maxLength=100, example="João Silva"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=100, example="joao@exemplo.com"),
     *             @OA\Property(property="senha", type="string", maxLength=100, example="novaSenha123"),
     *             @OA\Property(property="cnpj", type="string", maxLength=20, example="12345678000190", description="Apenas números. Obrigatório apenas para gestores")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Usuário atualizado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Usuário atualizado com sucesso.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Usuário não encontrado",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Usuário não encontrado."),
     *             @OA\Property(property="message", type="string", example="O usuário com o ID informado não existe.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="Email já cadastrado",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Email já cadastrado."),
     *             @OA\Property(property="message", type="string", example="Já existe outro usuário com este email.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Dados inválidos",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Dados inválidos."),
     *             @OA\Property(property="message", type="string", example="Os dados fornecidos não são válidos."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erro no servidor",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Erro ao atualizar usuário."),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $dados = $request->validate([
            'nome' => 'required|string|min:3|max:100',
            'email' => 'required|email|max:100',
            'senha' => 'required|string|min:6|max:100',
            'cnpj' => 'nullable|string|min:14|max:20',
        ], [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.string' => 'O campo nome deve ser um texto.',
            'nome.min' => 'O campo nome deve ter no mínimo 3 caracteres.',
            'nome.max' => 'O campo nome não pode ter mais de 100 caracteres.',
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'O campo email deve ser um endereço de email válido.',
            'email.max' => 'O campo email não pode ter mais de 100 caracteres.',
            'senha.required' => 'O campo senha é obrigatório.',
            'senha.string' => 'O campo senha deve ser um texto.',
            'senha.min' => 'O campo senha deve ter no mínimo 6 caracteres.',
            'senha.max' => 'O campo senha não pode ter mais de 100 caracteres.',
            'cnpj.string' => 'O campo CNPJ deve ser um texto.',
            'cnpj.min' => 'O campo CNPJ deve ter no mínimo 14 caracteres.',
            'cnpj.max' => 'O campo CNPJ não pode ter mais de 20 caracteres.',
        ]);

        try {
            $this->usuarioService->atualizarUsuario($id, $dados);

            return response()->json([
                'message' => 'Usuário atualizado com sucesso.',
            ]);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'não encontrado') || str_contains($e->getMessage(), 'não existe')) {
                return response()->json([
                    'error' => 'Usuário não encontrado.',
                    'message' => $e->getMessage(),
                ], 404);
            }

            return response()->json([
                'error' => 'Email já cadastrado.',
                'message' => $e->getMessage(),
            ], 409);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Erro ao atualizar usuário.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/usuarios/{id}",
     *     tags={"Usuários"},
     *     summary="Remove um usuário",
     *     description="Deleta um usuário e todos os seus dados vinculados (cliente ou gestor)",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do usuário",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Usuário removido com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Usuário removido com sucesso.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Usuário não encontrado",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Usuário não encontrado."),
     *             @OA\Property(property="message", type="string", example="O usuário com o ID informado não existe.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao remover usuário",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Erro ao remover usuário."),
     *             @OA\Property(property="message", type="string", example="Ocorreu um erro inesperado ao remover o usuário.")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->usuarioService->remover($id);

            return response()->json([
                'message' => 'Usuário removido com sucesso.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Usuário não encontrado.',
                'message' => $e->getMessage(),
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Erro ao remover usuário.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
