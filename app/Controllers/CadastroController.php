<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../models/CadastroModel.php';
require_once __DIR__ . '/../views/CadastroView.php';

use App\Models\CadastroModel;
use App\Views\CadastroView;

class CadastroController {
    public function cadastrar() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = $_POST['username'];
            $senha = $_POST['senha'];
            $perfil = $_POST['perfil'];

            // Instancia o modelo e a visão
            $model = new CadastroModel();
            $view = new CadastroView();

            // Verifica se o usuário já existe
            if ($model->usuarioExiste($username)) {
                // Se o usuário já existir, exibe um alerta e retorna
                $view->exibirMensagemErro("Usuário já existe. Por favor, escolha outro nome de usuário.");
                               return;
            }

            // Valida a senha antes de prosseguir
            if (!$this->validarSenha($senha)) {
                $view->exibirMensagemErro("A senha não atende aos critérios necessários.");
                return;
            }

            // Verifica se o cadastro foi bem-sucedido
            if ($model->cadastrarUsuario($username, $senha, $perfil)) {
                $view->exibirMensagemSucesso("Usuário cadastrado com sucesso!", '/conectaPapem/public/cadastroView.php');
            } else {
                $view->exibirMensagemErro("Erro ao cadastrar usuário.");
            }
        }
    }

    // Função para validar a senha
    private function validarSenha($senha) {
        $regraTamanho = strlen($senha) >= 12;
        $regraMaiuscula = preg_match('/[A-Z]/', $senha);
        $regraMinuscula = preg_match('/[a-z]/', $senha);
        $regraNumero = preg_match('/\d/', $senha);
        $regraEspecial = preg_match('/[^a-zA-Z\d]/', $senha);

        return $regraTamanho && $regraMaiuscula && $regraMinuscula && $regraNumero && $regraEspecial;
    }
}
