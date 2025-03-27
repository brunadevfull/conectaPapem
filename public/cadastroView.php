<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$tempoLimite = 6000; 


if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $tempoLimite)) {
   
    session_unset();     
    session_destroy();   
    header("Location: login.php"); 
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/controllers/ConsultaController.php';

use App\Controllers\ConsultaController;

$nomeUsuario = $_SESSION['username'];
$model = new App\Models\ConsultaModel();
$perfilUsuario = $model->obterPerfilUsuario($nomeUsuario);
$_SESSION['perfil'] = $perfilUsuario;


if ($perfilUsuario !== 'admin') {
    header("Location: restrito.php"); 
    exit();
}


$view = new App\Views\ConsultaView();


?><!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Usuário</title>
   
    <title>Pesquisa de CPF</title>
    <link rel="stylesheet" type="text/css" href="../css/conecta.css">
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link rel="stylesheet" type="text/css" href="../css/login.css">
    <link rel="stylesheet" type="text/css" href="../css/header.css">

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
   
    <script>
        function logout() {
            window.location.href = 'logout.php';
        }
    </script>
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="login-container">
        <section class="login-box">
            <form id='cadastro' method="post" action="">
                <label style="padding-left: 45px;">
                    <img src="../img/add.png" alt="Ícone" style="width:40px; height:40px; margin-right: 5px;"> Cadastro de Usuário
                </label>

                <label for="username">Usuário:</label>
                <div class="input-group mb-4">
                    <i class="fas fa-user icon"></i>
                    <input type="text" name="username" id="username" class="form-input-icon" placeholder="NIP" required>
                </div>

                <label for="senha">Senha: </label>
                <div class="input-group mb-4">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" name="senha" id="senha" class="form-input-icon" placeholder="Senha" required>
                </div>
                <div class="password-requirements">
                    <ul>
                        <li id="length"><span class="fa-li"></span>12 caracteres</li>
                        <li id="uppercase"><span class="fa-li"></span>Uma letra maiúscula</li>
                        <li id="lowercase"><span class="fa-li"></span>Uma letra minúscula</li>
                        <li id="number"><span class="fa-li"></span>Um número</li>
                        <li id="special"><span class="fa-li"></span>Um caractere especial</li>
                    </ul>
                </div>

                <label for="confsenha">Confirme a Senha: </label>
                <div class="input-group mb-4">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" name="conf" placeholder="Confirme sua Senha" id="confsenha" class="form-input-icon" required>
                </div>
                <label>Perfil:</label>
                <select name="perfil" class="form-input" required>
                    <option value="admin">Administrador</option>
                    <option value="usuario">Usuário Comum</option>
                </select><br><br>
                
                <button type="submit">Cadastrar</button>
                <div><br></div>
            </form>
            <br>
        </section>
    </main>

    <script>
        $(document).ready(function() {
            // Aplicar máscara ao campo de username
            $('#username').mask('00.0000.00'); // Formato 11.1110.62

            $("form").submit(function(e) {
                e.preventDefault();

                var usernameValue = $("input[name='username']").val().replace(/\./g, ''); // Remover os pontos
                var senhaValue = $("input[name='senha']").val();
                var confsenhaValue = $("#confsenha").val();

                if (usernameValue.length !== 8) {
                    alert("O usuário deve ter 8 dígitos.");
                    return false;
                }

                if (senhaValue !== confsenhaValue) {
                    alert("As senhas não coincidem. Por favor, verifique.");
                    return false;
                }

                // Enviar os dados para o servidor sem os pontos no username
                $.ajax({
                    type: 'POST',
                    url: 'cadastro.php',
                    data: {
                        username: usernameValue,
                        senha: senhaValue,
                        conf: confsenhaValue,
                        perfil: $("select[name='perfil']").val()
                    },
                    success: function(response) {
                        alert(response);
                        console.log(response);

                        if (response.toLowerCase().includes("usuário cadastrado com sucesso")) {
                            $("input[name='username']").val('');
                            $("input[name='senha']").val('');
                            $("#confsenha").val('');
                            resetPasswordRequirements();
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao processar o formulário. Tente novamente.');
                        $("input[name='username']").val(usernameValue);
                        $("#senha").val(senhaValue);
                        $("#confsenha").val(confsenhaValue);
                    }
                });
            });

            $('#senha').on('keyup', function() {
                var val = $(this).val();
                var length = val.length >= 12;
                var uppercase = /[A-Z]/.test(val);
                var lowercase = /[a-z]/.test(val);
                var number = /[0-9]/.test(val);
                var special = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/.test(val);

                updateRequirement('#length', length);
                updateRequirement('#uppercase', uppercase);
                updateRequirement('#lowercase', lowercase);
                updateRequirement('#number', number);
                updateRequirement('#special', special);
            });

            function updateRequirement(selector, isValid) {
                var li = $(selector);
                li.find('.fa-check').remove(); // Remove any existing checks
                if (isValid) {
                    li.addClass('valid').removeClass('invalid');
                    li.append('<i class="fas fa-check"></i>'); // Add a checkmark
                } else {
                    li.removeClass('valid').addClass('invalid');
                }
            }
        });

        function resetPasswordRequirements() {
            $('#length').removeClass('valid').addClass('invalid');
            $('#uppercase').removeClass('valid').addClass('invalid');
            $('#lowercase').removeClass('valid').addClass('invalid');
            $('#number').removeClass('valid').addClass('invalid');
            $('#special').removeClass('valid').addClass('invalid');
            $('.password-requirements li .fa-check').hide(); // Ocultar os ícones de check
        }
    </script>
</body>
</html>
