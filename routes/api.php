<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Usuarios\UsuariosController;

Route::get('/usuarios', [UsuariosController::class, 'index']);
