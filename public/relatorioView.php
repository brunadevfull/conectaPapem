<?php
session_start();

$tempoLimite = 800; 


require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/controllers/ConsultaController.php';

use App\Models\ConsultaModel;
use App\Controllers\ConsultaController;


if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$nomeUsuario = $_SESSION['username'];
$model = new ConsultaModel();

$perfilUsuario = $model->obterPerfilUsuario($nomeUsuario);
$_SESSION['perfil'] = $perfilUsuario;



$controller = new ConsultaController();
$cpfData = $controller->consultar(); 

$view = new App\Views\ConsultaView();

include 'relatorio.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráfico de Consultas de Usuário e Tentativas de Login Malsucedidas</title>

</head>
<body>
    <h1>Gráfico de Consultas de Usuário e Tentativas de Login Malsucedidas</h1>
    <div style="width: 800px; height: 400px;">
        <canvas id="grafico"></canvas>
    </div>

    <!-- Incluir biblioteca Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
    <script>
        // Código JavaScript para renderizar o gráfico com os dados fornecidos pelo PHP
    </script>
</body>
</html>
