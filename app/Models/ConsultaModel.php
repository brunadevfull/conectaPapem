<?php

namespace App\Models;
 require_once __DIR__ . '/../../vendor/autoload.php';
date_default_timezone_set('America/Sao_Paulo');

use Dotenv\Dotenv;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;


class ConsultaModel {

  private $conexao;

public function __construct() {
        // Carregar o autoload do Composer
        require_once __DIR__ . '/../../vendor/autoload.php';

        // Carregar o .env
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
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

    public function autenticarUsuario($username, $senha) {
        try {
            $statusBloqueio = $this->verificarStatusBloqueio($username);
    
            if ($statusBloqueio === 'bloqueado') {
                return false;
            }
    
            $tentativasRestantes = 3 - $this->obterTentativas($username);
            if ($this->excedeuLimiteTentativas($username, 3)) {
                $this->bloquearUsuario($username);
                return false;
            }
            
            echo " DEBUG: Tentando buscar usuário: $username<br>";
    
            $query = "SELECT * FROM usuario WHERE username = :username";
            $stmt = $this->conexao->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
    
            if ($stmt->rowCount() == 1) {
                $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);
    
                if (password_verify($senha, $usuario['senha'])) {
                    $this->registrarLogin($username);
                    $this->resetarTentativas($username);
                    return true;
                } else {
                    $this->registrarTentativaLoginMalSucedido($username);
                    if ($this->obterTentativas($username) >= 2) {
                        echo "<script>alert('Credenciais inválidas. Sua conta será bloqueada após mais uma tentativa mal-sucedida.')</script>";
                    } else {
                        echo "<script>alert('Credenciais inválidas. Tentativas restantes: " . $tentativasRestantes . "')</script>";
                        echo "<script>document.getElementById('senha').value = '';</script>";
                    }
                    return false;
                }
            } else {
                echo "<script>alert('Nome de usuário não encontrado.')</script>";
                return false;
            }
        } catch (\PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }
    
    
    
    public function verificarStatusBloqueioWrapper($username) {
        return $this->verificarStatusBloqueio($username);
    }

    private function obterTentativas($username) {
        try {
            $query = "SELECT COUNT(*) FROM tentativas_login_malsucedido WHERE username = :username AND status = 'ativa'";
            $stmt = $this->conexao->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
    
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }
    
    
    private function registrarTentativaLoginMalSucedido($username) {
        try {
            $query = "INSERT INTO tentativas_login_malsucedido (username, data_tentativa, status) VALUES (:username, NOW(), 'ativa')";
            $stmt = $this->conexao->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Erro de conexão: " . $e->getMessage(), 0);
            die("Erro de conexão: " . $e->getMessage());
        }
    }
    


    
    private function resetarTentativas($username) {
        try {
            $query = "UPDATE tentativas_login_malsucedido SET status = 'resetada' WHERE username = :username AND status = 'ativa'";
            $stmt = $this->conexao->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
        } catch (\PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }
    
    private function excedeuLimiteTentativas($username, $limite) {
        return $this->obterTentativas($username) >= $limite;
    }

    private function bloquearUsuario($username) {
        try {
            $this->registrarTentativaLoginMalSucedido($username);
    
          
            $dataBloqueio = new \DateTimeImmutable('+24 hours');
            $query = "UPDATE usuario SET status_bloqueio = 'bloqueado', data_bloqueio = :dataBloqueio WHERE username = :username";
            $stmt = $this->conexao->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindValue(':dataBloqueio', $dataBloqueio->format('Y-m-d H:i:s.uO'));
            $stmt->execute();
    
            echo "<script>alert('Sua conta foi bloqueada devido a múltiplas tentativas de login mal-sucedidas. Aguarde 24 horas para o desbloqueio.')</script>";
        } catch (\PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }
    
    private function desbloquearUsuario($username) {
        try {
         
            $query = "UPDATE usuario SET status_bloqueio = 'ativo', data_bloqueio = null WHERE username = :username";
            $stmt = $this->conexao->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
    
            $this->resetarTentativas($username);
        } catch (\PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }
    
    protected function verificarStatusBloqueio($username) {
        try {
            $query = "SELECT status_bloqueio, data_bloqueio FROM usuario WHERE username = :username";
            $stmt = $this->conexao->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
    
            $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
    
            if ($resultado && $resultado['status_bloqueio'] === 'bloqueado') {
                $dataBloqueio = new \DateTimeImmutable($resultado['data_bloqueio']);
                $agora = new \DateTimeImmutable();
    
                // Se a data de bloqueio já passou, desbloquear o usuário
                if ($agora > $dataBloqueio->modify('+24 hours')) {
                    $this->desbloquearUsuario($username);
                    return 'ativo';
                } else {
                    return 'bloqueado';
                }
            }
    
            return 'ativo'; 
        } catch (\PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }
    
   

    public function obterPerfilUsuario($username) {
        try {
            $query = "SELECT perfil FROM usuario WHERE username = :username";
            $stmt = $this->conexao->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
 
            $perfil = $stmt->fetchColumn();
            return $perfil !== false ? $perfil : null;
        } catch (\PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }

    public function registrarLogin($username) {
        try {
            $query = "INSERT INTO logins_history (username) VALUES (:username)";
            $stmt = $this->conexao->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
        } catch (\PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }

    private function obterLoginsHistoryDireto($username) {
        try {
            $query = "SELECT logins_history FROM usuario WHERE username = :username";
            $stmt = $this->conexao->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            $loginsHistory = $stmt->fetchColumn();
            return $loginsHistory ? json_decode($loginsHistory, true) : [];
        } catch (\PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }

    public function atualizarUltimoLogin($username) {
        try {
            $query = "UPDATE usuario SET last_login = NOW() WHERE username = :username";
            $stmt = $this->conexao->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
        } catch (\PDOException $e) {
            die("Erro de conexão: " . $e->getMessage());
        }
    }

    private function saveTokenToFile($tokenFilePath, $tokenData) {
        // Salvar o novo token no arquivo de cache
        file_put_contents($tokenFilePath, json_encode($tokenData));
    }


    private function isTokenExpired($tokenFilePath) {
        if (file_exists($tokenFilePath)) {
            $tokenCache = json_decode(file_get_contents($tokenFilePath), true);
            
            // Adicione uma mensagem de log para depuração
     
            
            // Adicione uma mensagem de log para verificar o valor atual de time()
          
            
            // Verificar se o token ainda é válido
            $isExpired = isset($tokenCache['expiration_time']) && time() >= $tokenCache['expiration_time'];
            
         
            
            return $isExpired;
        }
        return true;
    }
    
    
public function obterTokenOAuth2($tokenUrl, $clientId, $clientSecret, $opcao) {
    // Obter token diretamente sem cache
    $authorization = base64_encode("$clientId:$clientSecret");
    $headers = [
        "Content-Type: application/x-www-form-urlencoded",
        "Authorization: Basic $authorization"
    ];

    $data = http_build_query(["grant_type" => "client_credentials"]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new \Exception("Erro ao obter token OAuth2: " . curl_error($ch));
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new \Exception("Falha ao obter token. Código HTTP: $httpCode. Resposta: $response");
    }

    $responseData = json_decode($response, true);
    return $responseData['access_token'];
}


    
    public function registrarConsultaUsuario($username, $documento, $tipoDocumento) {
        /*error_log("Chamando registrarConsultaUsuario com $username, $documento, $opcao");
        try {
            $query = "INSERT INTO consultas_usuario (username, " . ($tipoDocumento === 'CPF' ? 'cpf' : 'cnpj') . ", data_consulta, tipo_documento) VALUES (:username, :documento, CURRENT_TIMESTAMP, :tipo_documento)";
            $stmt = $this->conexao->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':documento', $documento);
            $stmt->bindParam(':tipo_documento', $tipoDocumento);
            $stmt->execute();
        } catch (\PDOException $e) {
            die("Erro ao registrar consulta do usuário: " . $e->getMessage());
        }*/
    }
      //-------------------------------------------------------
      //essa funçao cria o token de acordo com as credenciais

        private function fetchNewToken($tokenUrl, $clientId, $clientSecret) {
            $authorization = self::base64EncodedBasicAuthentication($clientId, $clientSecret);
        
            $curl_token = curl_init();
        
            curl_setopt_array($curl_token, array(
                CURLOPT_URL => $tokenUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded',
                    'Authorization: ' . $authorization
                ),
            ));
        
            $response_token = curl_exec($curl_token);
        
            curl_close($curl_token);
        
            // Decodificar a resposta JSON para obter o token
            $token_data = json_decode($response_token, true);
        
            return $token_data;
        }
        
        // Função para codificar o cliente e o segredo do cliente em Base64
        private static function base64EncodedBasicAuthentication($clientId, $clientSecret) {
            $authorization = $clientId . ':' . $clientSecret;
            $authorization = base64_encode($authorization);
            return 'Basic ' . $authorization;
        }
    

        public function realizarConsultaCNPJ($consultaUrl, $oauthToken) {
            // Inicialize uma solicitação cURL
            $curl = curl_init();
        
            curl_setopt_array($curl, array(
                CURLOPT_URL => $consultaUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $oauthToken,
                    'x-cpf-usuario: 00000000191' // Substitua pelo CPF válido do usuário
                ),
            ));
        
            $response = curl_exec($curl);
        
            if ($response === false) {
                $error = curl_error($curl);
                return ['error' => 'Erro na solicitação: ' . $error];
            }
        
            curl_close($curl);
        
            // Decodificar a resposta JSON
            $data = json_decode($response, true);
        
            // Se a resposta não for um array, encapsule-a em um array
            if (!is_array($data)) {
                $data = [$data];
            }
        
            return $data;
        }
        
        public function realizarConsultaGET($consultaUrl, $oauthToken) {
    // Inicialize uma solicitação cURL
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $consultaUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $oauthToken,
                    'x-cpf-usuario: 00000000191' // Substitua pelo CPF válido do usuário
                ),
            ));

    $response = curl_exec($curl);

    if ($response === false) {
        $error = curl_error($curl);
        curl_close($curl);
        return ['error' => 'Erro na solicitação: ' . $error];
    }

    curl_close($curl);

    // Decodificar a resposta JSON
    $data = json_decode($response, true);

    return $data;
}


    public function realizarConsulta($consultaUrl, $listaCpf, $oauthToken) {
        $curl_cpf = curl_init();

        curl_setopt_array($curl_cpf, array(
            CURLOPT_URL => $consultaUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(['listaCpf' => $listaCpf]),
            CURLOPT_HTTPHEADER => array(
                'x-cpf-usuario: 00000000191',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $oauthToken,
            ),
        ));

        $response_cpf = curl_exec($curl_cpf);

        curl_close($curl_cpf);

        // Decodificar a resposta JSON
        $cpf_data = json_decode($response_cpf, true);

        // Retornar os dados de CPF
     return $cpf_data;
    }

    public function lerCPFsDoExcel($filePath) {
        $cpfArray = [];
    
        try {
            // Verifica se o arquivo existe
            if (!file_exists($filePath)) {
                throw new \Exception('Arquivo não encontrado: ' . $filePath);
            }
    
            // Carrega a planilha
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
    
            foreach ($worksheet->getRowIterator() as $row) {
                // Obtém o valor da célula na primeira coluna da linha atual
                $cellValue = $worksheet->getCellByColumnAndRow(1, $row->getRowIndex())->getValue();
    
                if (!empty($cellValue)) {
                    // Remove pontos e traços do CPF
                    $cpfSemPontos = str_replace(['.', '-'], '', $cellValue);
                    // Verifica se o CPF é válido
                    if (preg_match('/^\d{11}$/', $cpfSemPontos)) {
                        $cpfArray[] = $cpfSemPontos;
                    } else {
                        throw new \Exception('CPF inválido encontrado: ' . $cpfSemPontos);
                    }
                }
            }
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            // Tratamento específico para exceções de leitura da planilha
            echo 'Erro ao ler a planilha: ', $e->getMessage();
        } catch (\Exception $e) {
            // Tratamento geral de exceções
            echo 'Erro: ', $e->getMessage();
        }
    
        return $cpfArray;
    }
    public function lerDocumentosDoExcel($filePath) {
        $documentArray = [];
    
        try {
            // Verifica se o arquivo existe
            if (!file_exists($filePath)) {
                throw new \Exception('Arquivo não encontrado: ' . $filePath);
            }
    
            // Carrega a planilha
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
    
            foreach ($worksheet->getRowIterator() as $row) {
                // Obtém o valor da célula na primeira coluna da linha atual
                $cellValue = $worksheet->getCellByColumnAndRow(1, $row->getRowIndex())->getValue();
    
                if (!empty($cellValue)) {
                    // Remove pontos, traços e barras do documento
                    $documentoLimpo = str_replace(['.', '-', '/'], '', $cellValue);
                    // Verifica se o CPF ou CNPJ é válido
                    if (preg_match('/^\d{11}$/', $documentoLimpo)) {
                        $documentArray[] = $documentoLimpo;
                    } else {
                        error_log('CPF inválido encontrado: ' . $documentoLimpo);
                        throw new \Exception('CPF inválido encontrado: ' . $documentoLimpo);
                    }
                }
            }
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            // Tratamento específico para exceções de leitura da planilha
            echo 'Erro ao ler a planilha: ', $e->getMessage();
        } catch (\Exception $e) {
            // Tratamento geral de exceções
            echo 'Erro: ', $e->getMessage();
        }
    
        return $documentArray;
    }
    

    public function contarTentativasLoginPorMes() {
        try {
            $query = "SELECT COUNT(*) AS total, EXTRACT(MONTH FROM data_tentativa) AS mes 
                      FROM tentativas_login_malsucedido 
                      GROUP BY EXTRACT(MONTH FROM data_tentativa)";
            $stmt = $this->conexao->prepare($query);
            $stmt->execute();
    
            $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
            // Log para verificar os resultados
            error_log("Tentativas de login por mês: " . print_r($resultados, true));
    
            return $resultados;
        } catch (\PDOException $e) {
            die("Erro ao contar tentativas de login malsucedidas por mês: " . $e->getMessage());
        }
    }
    
    
    public function contarConsultasUsuarioPorMes() {
        try {
            $query = "SELECT COUNT(*) AS total, EXTRACT(MONTH FROM data_consulta) AS mes, tipo_documento 
                      FROM consultas_usuario 
                      GROUP BY EXTRACT(MONTH FROM data_consulta), tipo_documento";
            $stmt = $this->conexao->prepare($query);
            $stmt->execute();
    
            $resultados = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $resultados;
        } catch (\PDOException $e) {
            die("Erro ao contar consultas de usuário por mês: " . $e->getMessage());
        }
    }
    

}
