<?php

namespace App\Modules\Usuarios\Controllers;

use App\Modules\Usuarios\Models\Cliente;
use App\Modules\Usuarios\Models\Gestor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Autenticação"},
     *     summary="Realiza login e gera token JWT",
     *     description="Autentica um usuário (cliente ou gestor) e retorna o token JWT para acesso autenticado.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email","senha"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="joao@teste.com"),
     *             @OA\Property(property="senha", type="string", example="123456")
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
     *             @OA\Property(property="error", type="string", example="Credenciais inválidas.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao criar token",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Não foi possível criar o token.")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $dados = $request->validate([
            'email' => 'required|email',
            'senha' => 'required|string',
        ]);

        $usuario = Cliente::where('email', $dados['email'])->first()
                  ?? Gestor::where('email', $dados['email'])->first();

        if (! $usuario || ! Hash::check($dados['senha'], $usuario->senha)) {
            return response()->json(['error' => 'Credenciais inválidas.'], 401);
        }

        try {
            $token = JWTAuth::fromUser($usuario);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Não foi possível criar o token.'], 500);
        }

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
    }

    /**
     * @OA\Get(
     *     path="/auth/me",
     *     tags={"Autenticação"},
     *     summary="Retorna o usuário autenticado",
     *     description="Obtém os dados do usuário autenticado com base no token JWT.",
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
     *             @OA\Property(property="error", type="string", example="Token inválido ou expirado.")
     *         )
     *     )
     * )
     */
    public function me()
    {
        try {
            $usuario = JWTAuth::parseToken()->authenticate();

            return response()->json($usuario);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token inválido ou expirado.'], 401);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     tags={"Autenticação"},
     *     summary="Realiza logout e invalida o token",
     *     description="Invalida o token JWT atual, encerrando a sessão do usuário.",
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
     *             @OA\Property(property="error", type="string", example="Não foi possível invalidar o token.")
     *         )
     *     )
     * )
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json(['message' => 'Logout realizado com sucesso.']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Não foi possível invalidar o token.'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/refresh",
     *     tags={"Autenticação"},
     *     summary="Renova o token JWT",
     *     description="Gera um novo token JWT a partir de um token válido próximo da expiração.",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Token renovado com sucesso",
     *
     *         @OA\JsonContent(
     *
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
     *             @OA\Property(property="error", type="string", example="Token inválido ou expirado.")
     *         )
     *     )
     * )
     */
    public function refresh()
    {
        try {
            $novoToken = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json(['token' => $novoToken]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token inválido ou expirado.'], 401);
        }
    }
}
