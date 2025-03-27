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
$view = new App\Views\ConsultaView();
$nomeUsuario = $_SESSION['username'];

$model = new ConsultaModel();
$perfilUsuario = $model->obterPerfilUsuario($nomeUsuario);
$_SESSION['perfil'] = $perfilUsuario;
if ($perfilUsuario !== 'admin') {
    header("Location: restrito.php");
    exit();
}

$consultaModel = new App\Models\ConsultaModel();

// Obter dados de login malsucedido
$loginData = $consultaModel->contarTentativasLoginPorMes();

// Obter dados de consulta de usuário
$consultaData = $consultaModel->contarConsultasUsuarioPorMes();

// Mapear os números do mês para seus nomes
$monthNames = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];

// Inicializar arrays para armazenar os dados de CPF e CNPJ
$consultaDataCPF = [];
$consultaDataCNPJ = [];

// Inicializar arrays com os meses
foreach ($monthNames as $month => $monthName) {
    $consultaDataCPF[$monthName] = 0;
    $consultaDataCNPJ[$monthName] = 0;
}

// Processar os resultados para separar por CPF e CNPJ
foreach ($consultaData as $data) {
    $monthName = $monthNames[$data['mes']];
    if ($data['tipo_documento'] === 'CPF') {
        $consultaDataCPF[$monthName] = $data['total'];
    } elseif ($data['tipo_documento'] === 'CNPJ') {
        $consultaDataCNPJ[$monthName] = $data['total'];
    }
}

// Substituir os números do mês pelos nomes e reordenar os dados
$loginDataFormatted = [];
foreach ($monthNames as $month => $monthName) {
    $loginDataFormatted[$monthName] = 0;
}
foreach ($loginData as $data) {
    $loginDataFormatted[$monthNames[$data['mes']]] = $data['total'];
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatísticas de Login e Consulta</title>
    <!-- Adicione links para os arquivos de estilo do Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" type="text/css" href="../css/conecta.css">
    <link rel="stylesheet" type="text/css" href="../css/header.css">
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link rel="stylesheet" type="text/css" href="/conectaPapem/css/login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300&display=swap" rel="stylesheet">
    <script src="../js/pdfmake.min.js"></script>
    <script src="../js/vfs_fonts.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="../js/consultar.js"></script>
    <link rel="shortcut icon" href="https://www.marinha.mil.br/papem/sites/all/themes/contrib/govbr_theme/favicon.ico" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" href="../jqwidgets/jqwidgets/styles/jqx.base.css" />
    <link rel="stylesheet" href="../jqwidgets/jqwidgets/styles/jqx.classic.css" />
    <script type="text/javascript" src="../jqwidgets/jqwidgets/jqxcore.js"></script>
    <script type="text/javascript" src="../jqwidgets/jqwidgets/jqxdata.js"></script>
    <script type="text/javascript" src="../jqwidgets/jqwidgets/jqxbuttons.js"></script>
    <script type="text/javascript" src="../jqwidgets/jqwidgets/jqxscrollbar.js"></script>
    <script type="text/javascript" src="../jqwidgets/jqwidgets/jqxdatatable.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/vfs_fonts.js"></script>
    <script src="../jqwidgets/jqwidgets/jqx-all.js"></script>
    <link rel="stylesheet" href="../css/jqx.energyblue.css" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1234567890=" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
</head>
<body>
    <?php include ('header.php'); ?>
    <div>
        <canvas id="loginChart"></canvas>
    </div>
    <div>
        <canvas id="consultaChartCPF"></canvas>
    </div>
    <div>
        <canvas id="consultaChartCNPJ"></canvas>
    </div>

    <script>
        // Função para buscar os dados de login malsucedido por mês do servidor
        function fetchLoginData() {
            return <?php echo json_encode($loginDataFormatted); ?>;
        }

        // Função para buscar os dados de consulta de usuário por mês do servidor para CPF
        function fetchConsultaDataCPF() {
            return <?php echo json_encode($consultaDataCPF); ?>;
        }

        // Função para buscar os dados de consulta de usuário por mês do servidor para CNPJ
        function fetchConsultaDataCNPJ() {
            return <?php echo json_encode($consultaDataCNPJ); ?>;
        }

        // Opções dos gráficos
        const chartOptions = {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        };

        // Renderização dos gráficos
        function renderCharts() {
            // Buscar os dados de login malsucedido do servidor
            const loginData = fetchLoginData();
            const loginCtx = document.getElementById('loginChart').getContext('2d');
            const loginChart = new Chart(loginCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(loginData),
                    datasets: [{
                        label: 'Login Malsucedido',
                        data: Object.values(loginData),
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }]
                },
                options: chartOptions
            });

            // Buscar os dados de consulta de usuário do servidor para CPF
            const consultaDataCPF = fetchConsultaDataCPF();
            const consultaCtxCPF = document.getElementById('consultaChartCPF').getContext('2d');
            const consultaChartCPF = new Chart(consultaCtxCPF, {
                type: 'bar',
                data: {
                    labels: Object.keys(consultaDataCPF),
                    datasets: [{
                        label: 'Consultas de Usuário (CPF)',
                        data: Object.values(consultaDataCPF),
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: chartOptions
            });

            // Buscar os dados de consulta de usuário do servidor para CNPJ
            const consultaDataCNPJ = fetchConsultaDataCNPJ();
            const consultaCtxCNPJ = document.getElementById('consultaChartCNPJ').getContext('2d');
            const consultaChartCNPJ = new Chart(consultaCtxCNPJ, {
                type: 'bar',
                data: {
                    labels: Object.keys(consultaDataCNPJ),
                    datasets: [{
                        label: 'Consultas de Usuário (CNPJ)',
                        data: Object.values(consultaDataCNPJ),
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: chartOptions
            });
        }

        // Chamar a função para renderizar os gráficos quando a página carregar
        window.onload = renderCharts;
    </script>
</body>
</html>
