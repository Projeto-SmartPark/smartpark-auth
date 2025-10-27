<?php

namespace App\Modules\Usuarios\Controllers;

use App\Modules\Usuarios\Services\ClienteService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Throwable;

class ClienteController extends Controller
{
    private ClienteService $clienteService;

    public function __construct(ClienteService $clienteService)
    {
        $this->clienteService = $clienteService;
    }

    /**
     * @OA\Get(
     *     path="/clientes",
     *     tags={"Clientes"},
     *     summary="Lista todos os clientes",
     *     description="Retorna uma lista com todos os clientes cadastrados no sistema",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Lista de clientes retornada com sucesso",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *
     *                 @OA\Property(property="id_cliente", type="integer", example=1),
     *                 @OA\Property(property="nome", type="string", example="João Silva"),
     *                 @OA\Property(property="email", type="string", example="joao@exemplo.com"),
     *                 @OA\Property(property="usuario_id", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $clientes = $this->clienteService->listarTodos();

        return response()->json($clientes);
    }

    /**
     * @OA\Post(
     *     path="/clientes",
     *     tags={"Clientes"},
     *     summary="Cadastra um novo cliente",
     *     description="Cria um novo cliente no sistema",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"nome", "email", "senha"},
     *
     *             @OA\Property(property="nome", type="string", maxLength=100, example="João Silva"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=100, example="joao@exemplo.com"),
     *             @OA\Property(property="senha", type="string", maxLength=100, example="senha123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Cliente criado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Cliente criado com sucesso."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id_cliente", type="integer", example=1),
     *                 @OA\Property(property="nome", type="string", example="João Silva"),
     *                 @OA\Property(property="email", type="string", example="joao@exemplo.com")
     *             )
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
     *             @OA\Property(property="error", type="string", example="Erro no servidor."),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'nome' => 'required|string|min:3|max:100',
            'email' => 'required|email|max:100',
            'senha' => 'required|string|min:6|max:100',
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
        ]);

        try {
            $cliente = $this->clienteService->criarCliente($dados);

            return response()->json([
                'message' => 'Cliente criado com sucesso.',
                'data' => $cliente,
            ], 201);
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
     *     path="/clientes/{id}",
     *     tags={"Clientes"},
     *     summary="Exibe um cliente específico",
     *     description="Retorna os dados detalhados de um cliente pelo ID",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do cliente",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Dados do cliente retornados com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="id_cliente", type="integer", example=1),
     *             @OA\Property(property="nome", type="string", example="João Silva"),
     *             @OA\Property(property="email", type="string", example="joao@exemplo.com"),
     *             @OA\Property(property="usuario_id", type="integer", example=1)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Cliente não encontrado",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Cliente não encontrado."),
     *             @OA\Property(property="message", type="string", example="O cliente com o ID informado não existe.")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $cliente = $this->clienteService->buscarPorId($id);

            return response()->json($cliente);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Cliente não encontrado.',
                'message' => 'O cliente com o ID informado não existe.',
            ], 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/clientes/{id}",
     *     tags={"Clientes"},
     *     summary="Atualiza dados de um cliente",
     *     description="Atualiza as informações de um cliente existente com validação de email único",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do cliente",
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
     *             @OA\Property(property="senha", type="string", maxLength=100, example="novaSenha123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Cliente atualizado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Cliente atualizado com sucesso.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Cliente não encontrado",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Cliente não encontrado."),
     *             @OA\Property(property="message", type="string", example="O cliente com o ID informado não existe.")
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
     *             @OA\Property(property="message", type="string", example="Já existe outro cliente com este email.")
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
     *             @OA\Property(property="error", type="string", example="Erro ao atualizar cliente."),
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
        ]);

        try {
            $this->clienteService->atualizar($id, $dados);

            return response()->json([
                'message' => 'Cliente atualizado com sucesso.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Cliente não encontrado.',
                'message' => 'O cliente com o ID informado não existe.',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Email já cadastrado.',
                'message' => $e->getMessage(),
            ], 409);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Erro ao atualizar cliente.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/clientes/{id}",
     *     tags={"Clientes"},
     *     summary="Remove um cliente",
     *     description="Deleta um cliente do sistema",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do cliente",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Cliente removido com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Cliente removido com sucesso.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Cliente não encontrado",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Cliente não encontrado."),
     *             @OA\Property(property="message", type="string", example="O cliente com o ID informado não existe.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao remover cliente",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Erro ao remover cliente."),
     *             @OA\Property(property="message", type="string", example="Ocorreu um erro inesperado ao remover o cliente.")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $cliente = $this->clienteService->buscarPorId($id);
            $this->clienteService->remover($id);

            return response()->json([
                'message' => 'Cliente removido com sucesso.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Cliente não encontrado.',
                'message' => 'O cliente com o ID informado não existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Erro ao remover cliente.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
