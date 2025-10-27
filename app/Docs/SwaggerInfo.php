<?php

namespace App\Docs;

/**
 * @OA\Info(
 *     title="SmartPark Auth API",
 *     version="1.0.0",
 *     description="API responsável pela autenticação e gerenciamento de usuários do SmartPark."
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8000/api",
 *     description="Servidor Local de Desenvolvimento"
 * )
 *
 * @OA\Tag(
 *     name="Autenticação",
 *     description="Rotas de login, logout, refresh e dados do usuário autenticado"
 * )
 * @OA\Tag(
 *     name="Usuários",
 *     description="CRUD de clientes e gestores, usados no fluxo de autenticação"
 * )
 */
class SwaggerInfo
{
    // Classe utilizada apenas para armazenar anotações do Swagger
    // Não contém implementação, serve apenas como ponto central da documentação da API
}
