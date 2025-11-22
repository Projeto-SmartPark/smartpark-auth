<?php

namespace App\Modules\Usuarios\Controllers;

use App\Modules\Usuarios\Models\Cliente;
use App\Modules\Usuarios\Models\Gestor;
use App\Modules\Usuarios\Services\UsuarioService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    private UsuarioService $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"Autenticação"},
     *     summary="Cadastra um novo usuário",
     *     description="Cria um novo cliente ou gestor no sistema e retorna o token JWT para acesso autenticado",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"perfil", "nome", "email", "senha"},
     *
     *             @OA\Property(property="perfil", type="string", enum={"C", "G"}, example="C", description="C = Cliente, G = Gestor"),
     *             @OA\Property(property="nome", type="string", minLength=3, maxLength=100, example="João da Silva"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=120, example="joao@teste.com"),
     *             @OA\Property(property="senha", type="string", minLength=6, maxLength=100, example="senha123"),
     *             @OA\Property(property="cnpj", type="string", minLength=14, maxLength=20, example="12345678000190", description="Apenas números. Obrigatório apenas para gestores")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Usuário criado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Usuário criado com sucesso."),
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
     *             @OA\Property(property="usuario", type="object",
     *                 @OA\Property(property="id_usuario", type="integer", example=1),
     *                 @OA\Property(property="perfil", type="string", example="C")
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
    public function register(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'perfil' => 'required|in:C,G',
            'nome' => 'required|string|min:3|max:100',
            'email' => 'required|email|max:120',
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
            'email.max' => 'O campo email não pode ter mais de 120 caracteres.',
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

            // Gera token JWT automaticamente
            $usuario = $dados['perfil'] === 'C'
                ? Cliente::find($resultado['id_usuario'])
                : Gestor::find($resultado['id_usuario']);

            $token = JWTAuth::fromUser($usuario);

            return response()->json([
                'message' => 'Usuário criado com sucesso.',
                'token' => $token,
                'usuario' => $resultado,
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
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Autenticação"},
     *     summary="Realiza login e gera token JWT",
     *     description="Autentica um usuário (cliente ou gestor) e retorna o token JWT para acesso autenticado",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email", "senha"},
     *
     *             @OA\Property(property="email", type="string", format="email", maxLength=120, example="joao@teste.com"),
     *             @OA\Property(property="senha", type="string", minLength=6, maxLength=100, example="senha123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login realizado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Login realizado com sucesso."),
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
     *             @OA\Property(property="usuario", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nome", type="string", example="João da Silva"),
     *                 @OA\Property(property="email", type="string", example="joao@teste.com"),
     *                 @OA\Property(property="perfil", type="string", example="C")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Credenciais inválidas",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Credenciais inválidas."),
     *             @OA\Property(property="message", type="string", example="Email ou senha incorretos.")
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
     *         description="Erro ao criar token",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Erro ao criar token."),
     *             @OA\Property(property="message", type="string", example="Não foi possível criar o token.")
     *         )
     *     )
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'email' => 'required|email|max:120',
            'senha' => 'required|string|min:6|max:100',
            'perfil' => 'required|in:C,G',
        ], [
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'O campo email deve ser um endereço de email válido.',
            'email.max' => 'O campo email não pode ter mais de 120 caracteres.',
            'senha.required' => 'O campo senha é obrigatório.',
            'senha.string' => 'O campo senha deve ser um texto.',
            'senha.min' => 'O campo senha deve ter no mínimo 6 caracteres.',
            'senha.max' => 'O campo senha não pode ter mais de 100 caracteres.',
            'perfil.required' => 'O campo perfil é obrigatório.',
            'perfil.in' => 'O campo perfil deve ser C (Cliente) ou G (Gestor).',
        ]);

        // Busca apenas na tabela específica conforme o perfil informado
        $usuario = $dados['perfil'] === 'C'
            ? Cliente::where('email', $dados['email'])->first()
            : Gestor::where('email', $dados['email'])->first();

        if (! $usuario || ! Hash::check($dados['senha'], $usuario->senha)) {
            return response()->json([
                'error' => 'Credenciais inválidas.',
                'message' => 'Email ou senha incorretos.',
            ], 401);
        }

        try {
            $token = JWTAuth::fromUser($usuario);

            return response()->json([
                'message' => 'Login realizado com sucesso.',
                'token' => $token,
                'usuario' => [
                    'id' => $usuario->getKey(),
                    'nome' => $usuario->nome,
                    'email' => $usuario->email,
                    'perfil' => $usuario instanceof Cliente ? 'C' : 'G',
                ],
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Erro ao criar token.',
                'message' => 'Não foi possível criar o token.',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/auth/me",
     *     tags={"Autenticação"},
     *     summary="Retorna o usuário autenticado",
     *     description="Obtém os dados do usuário autenticado com base no token JWT",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Usuário autenticado retornado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="nome", type="string", example="João da Silva"),
     *             @OA\Property(property="email", type="string", example="joao@teste.com"),
     *             @OA\Property(property="perfil", type="string", example="C")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido ou expirado",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Token inválido ou expirado."),
     *             @OA\Property(property="message", type="string", example="O token fornecido é inválido ou expirou.")
     *         )
     *     )
     * )
     */
    public function me(): JsonResponse
    {
        try {
            $usuario = JWTAuth::parseToken()->authenticate();

            return response()->json([
                'id' => $usuario->getKey(),
                'nome' => $usuario->nome,
                'email' => $usuario->email,
                'perfil' => $usuario instanceof Cliente ? 'C' : 'G',
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Token inválido ou expirado.',
                'message' => 'O token fornecido é inválido ou expirou.',
            ], 401);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     tags={"Autenticação"},
     *     summary="Realiza logout e invalida o token",
     *     description="Invalida o token JWT atual, encerrando a sessão do usuário",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logout realizado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Logout realizado com sucesso.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao invalidar o token",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Erro ao invalidar token."),
     *             @OA\Property(property="message", type="string", example="Não foi possível invalidar o token.")
     *         )
     *     )
     * )
     */
    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'message' => 'Logout realizado com sucesso.',
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Erro ao invalidar token.',
                'message' => 'Não foi possível invalidar o token.',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/refresh",
     *     tags={"Autenticação"},
     *     summary="Renova o token JWT",
     *     description="Gera um novo token JWT a partir de um token válido próximo da expiração",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Token renovado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Token renovado com sucesso."),
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Token inválido ou expirado",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Token inválido ou expirado."),
     *             @OA\Property(property="message", type="string", example="O token fornecido é inválido ou expirou.")
     *         )
     *     )
     * )
     */
    public function refresh(): JsonResponse
    {
        try {
            $novoToken = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'message' => 'Token renovado com sucesso.',
                'token' => $novoToken,
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Token inválido ou expirado.',
                'message' => 'O token fornecido é inválido ou expirou.',
            ], 401);
        }
    }
}
