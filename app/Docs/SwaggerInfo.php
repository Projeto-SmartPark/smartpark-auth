<?php

namespace App\Docs;

/**
 * @OA\Info(
 *     title="SmartPark Auth API",
 *     version="1.0.0",
 *     description="API responsável pela autenticação e gerenciamento de usuários do SmartPark.
 *
 * Esta documentação permite autenticar-se via token JWT e testar rotas protegidas
 * (como listagem e atualização de usuários). Para isso, utilize o botão **Authorize**
 * localizado no canto superior direito e insira o token no formato: `Bearer {seu_token}`."
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:9000/api",
 *     description="Servidor Local de Desenvolvimento (Auth Service)"
 * )
 *
 * @OA\Tag(
 *     name="Autenticação",
 *     description="Rotas responsáveis pelo login, logout, refresh e dados do usuário autenticado"
 * )
 * @OA\Tag(
 *     name="Usuários",
 *     description="CRUD de clientes e gestores, utilizados no fluxo de autenticação e controle de acesso"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Insira o token JWT no formato: **Bearer {seu_token}**.
 * Este token é obtido através do endpoint `/api/auth/login`."
 * )
 */
class SwaggerInfo
{
    // Esta classe é usada apenas para armazenar as anotações do Swagger (OpenAPI)
    // e centralizar as informações da documentação pública da API de autenticação.
}
