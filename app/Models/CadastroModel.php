<?php

namespace App\Models;
require_once __DIR__ . '/../libs/phpdotenv/src/Dotenv.php'
class CadastroModel {
    private $conexao;

   public function __construct() {
        // Carregar o .env
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        try {
            $this->conexao = new \PDO(
                sprintf(
                    'pgsql:host=%s;port=%s;dbname=%s',
                    $_ENV['DB_HOST'],
                    $_ENV['DB_PORT'],
                    $_ENV['DB_DATABASE']
                ),
                $_ENV['DB_USERNAME'],
                $_ENV['DB_PASSWORD']
            );

            $this->conexao->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }

    public function getConexao() {
        return $this->conexao;
    }
}
    public function usuarioExiste($username) {
        try {
            // Verifica se o usuário já existe
            $query = "SELECT COUNT(*) FROM usuario WHERE username = :username";
            $stmt = $this->conexao->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

           
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
          
            return false;
        }
    }
    
    private function validarSenha($senha) {
        $regraTamanho = strlen($senha) >= 12;
        $regraMaiuscula = preg_match('/[A-Z]/', $senha);
        $regraMinuscula = preg_match('/[a-z]/', $senha);
        $regraNumero = preg_match('/\d/', $senha);
        $regraEspecial = preg_match('/[^a-zA-Z\d]/', $senha);

        return $regraTamanho && $regraMaiuscula && $regraMinuscula && $regraNumero && $regraEspecial;
    }




    private function limparNIP($nip) {
        // Remove todos os caracteres não numéricos
        $nipLimpo = preg_replace('/\D/', '', $nip);
        // Verifica se o NIP limpo tem exatamente 8 dígitos
        return (strlen($nipLimpo) === 8) ? $nipLimpo : false;
    }
    
    public function cadastrarUsuario($username, $senha, $perfil) {
        try {
            // Limpa e valida o NIP
            $nipLimpo = $this->limparNIP($username);
            if (!$nipLimpo) {
                return "NIP deve conter exatamente 8 dígitos.";
            }
    
            // Verifica se o usuário já existe
            if ($this->usuarioExiste($nipLimpo)) {
                return "Usuário já existe.";
            }
    
            // Verifica se a senha atende aos critérios
            if (!$this->validarSenha($senha)) {
                return "Senha não atende aos critérios.";
            }
    
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    
            $query = "INSERT INTO usuario (username, senha, perfil) VALUES (:username, :senha, :perfil)";
            $stmt = $this->conexao->prepare($query);
            $stmt->bindParam(':username', $nipLimpo);
            $stmt->bindParam(':senha', $senhaHash);
            $stmt->bindParam(':perfil', $perfil);
            $stmt->execute();
    
            return "Usuário cadastrado com sucesso.";
        } catch (\PDOException $e) {
            return "Erro ao cadastrar usuário: " . $e->getMessage();
        }
    }
    
    
}
