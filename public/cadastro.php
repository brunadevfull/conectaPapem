<?php
// Arquivo para processar o cadastro de usuários

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/controllers/CadastroController.php';
use App\Controllers\CadastroController;

$controller = new CadastroController();
$controller->cadastrar();
