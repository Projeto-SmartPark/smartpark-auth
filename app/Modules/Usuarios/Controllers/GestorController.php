<?php

namespace App\Modules\Usuarios\Controllers;

use App\Modules\Usuarios\Services\GestorService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Throwable;

class GestorController extends Controller
{
    private GestorService $gestorService;

    public function __construct(GestorService $gestorService)
    {
        $this->gestorService = $gestorService;
    }

    /**
     * @OA\Get(
     *     path="/gestores",
     *     tags={"Gestores"},
     *     summary="Lista todos os gestores",
     *     description="Retorna uma lista com todos os gestores cadastrados no sistema",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Lista de gestores retornada com sucesso",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *
     *                 @OA\Property(property="id_gestor", type="integer", example=1),
     *                 @OA\Property(property="nome", type="string", example="Maria Santos"),
     *                 @OA\Property(property="email", type="string", example="maria@empresa.com"),
     *                 @OA\Property(property="cnpj", type="string", example="12345678000190"),
     *                 @OA\Property(property="usuario_id", type="integer", example=2)
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $gestores = $this->gestorService->listarTodos();

        return response()->json($gestores);
    }

    /**
     * @OA\Post(
     *     path="/gestores",
     *     tags={"Gestores"},
     *     summary="Cadastra um novo gestor",
     *     description="Cria um novo gestor no sistema",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"nome", "email", "senha", "cnpj"},
     *
     *             @OA\Property(property="nome", type="string", maxLength=100, example="Maria Santos"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=100, example="maria@empresa.com"),
     *             @OA\Property(property="senha", type="string", maxLength=100, example="senha123"),
     *             @OA\Property(property="cnpj", type="string", maxLength=20, example="12345678000190", description="Apenas números")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Gestor criado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Gestor criado com sucesso."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id_gestor", type="integer", example=1),
     *                 @OA\Property(property="nome", type="string", example="Maria Santos"),
     *                 @OA\Property(property="email", type="string", example="maria@empresa.com"),
     *                 @OA\Property(property="cnpj", type="string", example="12345678000190")
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
     *             @OA\Property(property="message", type="string", example="Já existe um gestor com este email.")
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
            'cnpj' => 'required|string|min:14|max:20',
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
            'cnpj.required' => 'O campo CNPJ é obrigatório.',
            'cnpj.string' => 'O campo CNPJ deve ser um texto.',
            'cnpj.min' => 'O campo CNPJ deve ter no mínimo 14 caracteres.',
            'cnpj.max' => 'O campo CNPJ não pode ter mais de 20 caracteres.',
        ]);

        try {
            $gestor = $this->gestorService->criarGestor($dados);

            return response()->json([
                'message' => 'Gestor criado com sucesso.',
                'data' => $gestor,
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
     *     path="/gestores/{id}",
     *     tags={"Gestores"},
     *     summary="Exibe um gestor específico",
     *     description="Retorna os dados detalhados de um gestor pelo ID",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do gestor",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Dados do gestor retornados com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="id_gestor", type="integer", example=1),
     *             @OA\Property(property="nome", type="string", example="Maria Santos"),
     *             @OA\Property(property="email", type="string", example="maria@empresa.com"),
     *             @OA\Property(property="cnpj", type="string", example="12345678000190"),
     *             @OA\Property(property="usuario_id", type="integer", example=2)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Gestor não encontrado",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Gestor não encontrado."),
     *             @OA\Property(property="message", type="string", example="O gestor com o ID informado não existe.")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $gestor = $this->gestorService->buscarPorId($id);

            return response()->json($gestor);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Gestor não encontrado.',
                'message' => 'O gestor com o ID informado não existe.',
            ], 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/gestores/{id}",
     *     tags={"Gestores"},
     *     summary="Atualiza dados de um gestor",
     *     description="Atualiza as informações de um gestor existente com validação de email único",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do gestor",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"nome", "email", "senha", "cnpj"},
     *
     *             @OA\Property(property="nome", type="string", maxLength=100, example="Maria Santos"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=100, example="maria@empresa.com"),
     *             @OA\Property(property="senha", type="string", maxLength=100, example="novaSenha123"),
     *             @OA\Property(property="cnpj", type="string", maxLength=20, example="12345678000190", description="Apenas números")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Gestor atualizado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Gestor atualizado com sucesso.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Gestor não encontrado",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Gestor não encontrado."),
     *             @OA\Property(property="message", type="string", example="O gestor com o ID informado não existe.")
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
     *             @OA\Property(property="message", type="string", example="Já existe outro gestor com este email.")
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
     *             @OA\Property(property="error", type="string", example="Erro ao atualizar gestor."),
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
            'cnpj' => 'required|string|min:14|max:20',
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
            'cnpj.required' => 'O campo CNPJ é obrigatório.',
            'cnpj.string' => 'O campo CNPJ deve ser um texto.',
            'cnpj.min' => 'O campo CNPJ deve ter no mínimo 14 caracteres.',
            'cnpj.max' => 'O campo CNPJ não pode ter mais de 20 caracteres.',
        ]);

        try {
            $this->gestorService->atualizar($id, $dados);

            return response()->json([
                'message' => 'Gestor atualizado com sucesso.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Gestor não encontrado.',
                'message' => 'O gestor com o ID informado não existe.',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Email já cadastrado.',
                'message' => $e->getMessage(),
            ], 409);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Erro ao atualizar gestor.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/gestores/{id}",
     *     tags={"Gestores"},
     *     summary="Remove um gestor",
     *     description="Deleta um gestor do sistema",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do gestor",
     *         required=true,
     *
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Gestor removido com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Gestor removido com sucesso.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Gestor não encontrado",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Gestor não encontrado."),
     *             @OA\Property(property="message", type="string", example="O gestor com o ID informado não existe.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao remover gestor",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Erro ao remover gestor."),
     *             @OA\Property(property="message", type="string", example="Ocorreu um erro inesperado ao remover o gestor.")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $gestor = $this->gestorService->buscarPorId($id);
            $this->gestorService->remover($id);

            return response()->json([
                'message' => 'Gestor removido com sucesso.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Gestor não encontrado.',
                'message' => 'O gestor com o ID informado não existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Erro ao remover gestor.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
