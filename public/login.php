<?php
require_once __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../app/Controllers/ConsultaController.php';
use App\Controllers\ConsultaController;

$controller = new ConsultaController();
$controller->autenticar();
?>

<?php
$perfilUsuario = ''; 
$nomeUsuario = '';   



// Verifique se a página de redirecionamento ocorreu devido à inatividade
if (isset($_GET['timeout']) && $_GET['timeout'] == 'true') {
    // Destrua a sessão
    session_unset();
    session_destroy();
}
?><!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="/conectaPapem/css/style.css">
    <link rel="stylesheet" type="text/css" href="/conectaPapem/css/header.css">
    <link rel="stylesheet" type="text/css" href="/conectaPapem/css/login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="../js/utils.js"></script>

    <style>
        .form-input {
            background: #e3e3e3;
            padding: 10px 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            padding-left: 35px; /* Adicione espaçamento à esquerda para acomodar os ícones */
        }
        .form-input:focus {
            border-color: #007bff;
            outline: none;
        }
        .login-btn {
            background: linear-gradient(to bottom, #0f74ea, #2f7ab7);
            border: none;
            color: white;
            padding: 10px 15px;
            border-radius: 20px;
            font-size: 14px;
            cursor: pointer;
        }
        .login-btn:hover {
            background: linear-gradient(to bottom, #5f6c7b, #8e9eab);
        }
        .input-group i {
            position: absolute;
            font-size: 18px;
            color: #555;
        }
        .login-box .text-2xl i {
            margin-right: 10px;
        }
        .loader {
            display: none;
            border: 16px solid #f3f3f3;
            border-top: 16px solid #3498db;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            animation: spin 2s linear infinite;
            position: fixed;
            top: 50%;
            left: 50%;
            z-index: 9999;
        }
        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
        .login-box-smaller {
            height: 350px; /* Novo height para a página de login */
        }
        .login-container-smaller {
            height: 400px;
            margin-top: 100px;
        }
    </style>

    <script>
        $(document).ready(function() {
            // Aplicar máscara ao campo de username
            $('input[name="username"]').mask('00.0000.00'); // Formato 11.1110.62

            $('form').submit(function(e) {
                e.preventDefault();

                var usernameValue = $('input[name="username"]').val().replace(/\./g, ''); // Remover os pontos
                var senhaValue = $('input[name="senha"]').val();

                // Cria um formulário oculto para envio dos dados sem os pontos
                var form = $('<form>', {
                    'method': 'POST',
                    'action': $(this).attr('action')
                }).append($('<input>', {
                    'type': 'hidden',
                    'name': 'username',
                    'value': usernameValue
                })).append($('<input>', {
                    'type': 'hidden',
                    'name': 'senha',
                    'value': senhaValue
                }));

                $('body').append(form);
                form.submit();
            });

            var loader = document.getElementById('loader');
            document.querySelector('form').addEventListener('submit', function () {
                loader.style.display = 'block';
            });
            window.addEventListener('load', function () {
                loader.style.display = 'none';
            });
        });
    </script>
</head>
<body>
    <?php include('header.php'); ?>
    <main class="login-container login-container-smaller">
        <section class="login-box login-box-smaller">
            <h2 class="text-2xl font-bold mb-2 flex items-center justify-center">
                <i class="fas fa-user-circle text-blue-500 mr-2"></i>LOGIN
            </h2>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="bg-white p-8 rounded-lg shadow-md">
                <div class="input-group mb-4">
                    <!-- Ícone de usuário -->
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Insira seu NIP" class="form-input" required>
                </div>
                <div class="input-group mb-4">
                    <!-- Ícone de cadeado -->
                    <i class="fas fa-lock"></i>
                    <input type="password" name="senha" placeholder="Insira sua senha" class="form-input" required>
                </div>
                <button type="submit" class="login-btn w-full">Entrar</button>
                <div id="loader" class="loader"></div>
            </form>
        </section>
    </main>
</body>
</html>

