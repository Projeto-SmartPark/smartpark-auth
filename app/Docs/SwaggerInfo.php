<?php

namespace App\Docs;

/**
 * @OA\Info(
 *     title="SmartPark API - Autenticação",
 *     version="1.0.0",
 *     description="Documentação da API de autenticação do SmartPark.
 * Esta documentação permite autenticar-se via token JWT e testar rotas protegidas (como listagem e atualização de usuários). Para isso, utilize o botão **Authorize** localizado no canto superior direito e insira o token."
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:9000/api"
 * )
 *
 * @OA\Tag(
 *     name="Autenticação",
 *     description="Rotas responsáveis pelo login, logout, refresh e dados do usuário autenticado"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Insira o token JWT.
 * Este token é obtido através do endpoint `/api/auth/login`."
 * )
 */
class SwaggerInfo
{
    // Esta classe é usada apenas para armazenar as anotações do Swagger (OpenAPI)
    // e centralizar as informações da documentação pública da API de autenticação.
}
