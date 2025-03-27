<?php

namespace App\Controllers;

ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\ConsultaModel;
use App\Views\ConsultaView;

require __DIR__ . '/../Models/ConsultaModel.php';
require __DIR__ . '/../Views/ConsultaView.php';

function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        throw new Exception(".env file not found at: $filePath");
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Ignorar comentários

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Definir variável de ambiente
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

// **Carregar .env no início do projeto**
loadEnv(__DIR__ . '/../../.env');



class ConsultaController {

    public function autenticar() {
        session_start();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $model = new ConsultaModel();
            $view = new ConsultaView();

            $username = $_POST['username'];
            $senha = $_POST['senha'];

            $statusBloqueio = $model->verificarStatusBloqueioWrapper($username);

            if ($statusBloqueio === 'bloqueado') {
                $view->exibirMensagemErro("Sua conta está bloqueada. Aguarde 24 horas para o desbloqueio.");
                return;
            }

            if ($model->autenticarUsuario($username, $senha)) {
                $model->registrarLogin($username);
                $_SESSION['username'] = $username;
                header("Location: index.php");
                exit();
            }
        }
    }
    
    private function validarDocumento($documento, $tipo) {
        $documento = preg_replace('/[^0-9]/', '', $documento);

        if ($tipo === 'CPF' && strlen($documento) === 11) {
            return $this->validarCPF($documento);
        } elseif ($tipo === 'CNPJ' && strlen($documento) === 14) {
            return $this->validarCNPJ($documento);
        } elseif ($tipo === 'CNIS' && strlen($documento) === 11) {
            return $this->validarCNIS($documento);
        }

        return false;
    }

    private function validarCNPJ($cnpj) {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) return false;

        $soma = 0;
        for ($i = 0, $pos = 5; $i < 12; $i++) {
            $soma += $cnpj[$i] * $pos--;
            if ($pos < 2) $pos = 9;
        }
        $digito1 = $soma % 11 < 2 ? 0 : 11 - $soma % 11;

        $soma = 0;
        for ($i = 0, $pos = 6; $i < 13; $i++) {
            $soma += $cnpj[$i] * $pos--;
            if ($pos < 2) $pos = 9;
        }
        $digito2 = $soma % 11 < 2 ? 0 : 11 - $soma % 11;

        return $cnpj[12] == $digito1 && $cnpj[13] == $digito2;
    }

    private function validarCNIS($cnis) {
        $cnis = preg_replace('/[^0-9]/', '', $cnis);
        if (strlen($cnis) != 11 || preg_match('/(\d)\1{10}/', $cnis)) return false;

        for ($i = 9; $i < 11; $i++) {
            $sum = 0;
            for ($j = 0; $j < $i; $j++) {
                $sum += $cnis[$j] * (($i + 1) - $j);
            }
            $digit = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);
            if ($digit != $cnis[$i]) return false;
        }

        return true;
    }

    private function validarCPF($cpf) {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;

        for ($i = 9; $i < 11; $i++) {
            $sum = 0;
            for ($j = 0; $j < $i; $j++) {
                $sum += $cpf[$j] * (($i + 1) - $j);
            }
            $digit = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);
            if ($digit != $cpf[$i]) return false;
        }

        return true;
    }

    public function consultar() {
        $model = new ConsultaModel();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $username = $_SESSION['username'] ?? 'guest';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $opcao = $_POST['opcao'] ?? null;
            if (!$opcao) {
                echo json_encode(['error' => 'Opção inválida.']);
                exit;
            }

            $documento = $_POST['documento'] ?? null;
            $fileInput = $_FILES['fileInput'] ?? null;

             if (!empty($_POST['documento'])) {
                $documento = preg_replace('/[^0-9]/', '', $documento);

                if (($opcao === 'CPF' && !$this->validarCPF($documento)) ||
                    ($opcao === 'CNPJ' && !$this->validarCNPJ($documento)) ||
                    ($opcao === 'CNIS' && !$this->validarCNIS($documento))) {
                    echo json_encode(['error' => 'Documento inválido.']);
                    return;
                }
                
                                
                $documentArray = [$documento];


            } elseif ($fileInput && $fileInput['tmp_name']) {
                $documentArray = $model->lerDocumentosDoExcel($fileInput['tmp_name']);
                foreach ($documentArray as $doc) {
                    if (empty($doc)) {
                        error_log("Documento vazio encontrado e ignorado.");
                        continue;
                    }
    
                    error_log("Validando documento: $doc");
    
                    if ($opcao === 'CPF' && !$this->validarCPF($doc)) {
                        echo json_encode(['error' => "CPF inválido no arquivo: $doc"]);
                        return;
                    } elseif ($opcao === 'CNPJ' && !$this->validarCNPJ($doc)) {
                        echo json_encode(['error' => "CNPJ inválido no arquivo: $doc"]);
                        return;
                    }
                }
            } else {
                echo json_encode(['error' => "Nenhum documento fornecido."]);
                return;
            
    
                
            }
              foreach ($documentArray as $documento) {
                if (empty($documento)) {
                    continue;
                }
                error_log("Registrando consulta para o documento: " . $documento);
                $model->registrarConsultaUsuario($username, $documento, $opcao);
            }

            $tokenUrl = getenv("API_{$opcao}_URL");
            $clientId = getenv("API_{$opcao}_CLIENT_ID");
            $clientSecret = getenv("API_{$opcao}_CLIENT_SECRET");

            $oauthToken = $model->obterTokenOAuth2($tokenUrl, $clientId, $clientSecret, $opcao);

            $consultaUrls = [
                'CPF' => 'https://apigateway.conectagov.estaleiro.serpro.gov.br/api-cpf-light/v2/consulta/cpf',
                'CNPJ' => "https://apigateway.conectagov.estaleiro.serpro.gov.br/api-cnpj-basica/v2/basica/{$documento}",
                'CNIS' => "https://apigateway.conectagov.estaleiro.serpro.gov.br/api-relacao-trabalhista/v1/relacoes-trabalhistas?cpf={$documento}",
            ];

            if (!isset($consultaUrls[$opcao])) {
                echo json_encode(['error' => 'Opção inválida.']);
                exit;
            }

            // **CNIS e CNPJ usam GET, CPF usa POST**
            if ($opcao === 'CPF') {
                $response = $model->realizarConsulta($consultaUrls[$opcao], [$documento], $oauthToken);
            } else {
                $response = $model->realizarConsultaGET($consultaUrls[$opcao], $oauthToken);
            }

            echo json_encode($response);
            exit;
        }
    }
}

