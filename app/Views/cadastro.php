<?php
session_start();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: /conectaPapem/public/login.php");
    exit();
}
$nomeUsuario = $_SESSION['username'];

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../controllers/ConsultaController.php';


use App\Controllers\ConsultaController;

$controller = new ConsultaController();
$cpfData = $controller->consultar(); 

$view = new App\Views\ConsultaView();

if (!empty($cpfData)) {
    $view->exibirResultados($cpfData); // Certifique-se de passar os dados corretos aqui
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Usuário</title>
    <meta charset="UTF-8">
    <title>Pesquisa de CPF</title>
    <link rel="stylesheet" type="text/css" href="/conectaPapem/css/style.css">
    <link rel="stylesheet" type="text/css" href="/conectaPapem/css/login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/conectaPapem/js/consultar.js"></script>
    <script>
        // Adicione a função de logout
        function logout() {
            window.location.href = '/conectaPapem/public/logout.php';
        }
    </script>
</head>
<body>
    <?php include 'header.php'; ?>
    <form method="post" action="/../public/cadastro.php">
        <label for="username">Usuário:</label>
        <input type="text" name="username" required><br>

        <label for="senha">Senha:</label>
        <input type="password" name="senha" required><br>

        <button type="submit">Cadastrar</button>
    </form>
    <?php include 'footer.php'; ?>
</body>
</html>
