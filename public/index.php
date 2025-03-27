<?php
session_start();

$tempoLimite = 10000; 

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Controllers/ConsultaController.php';

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


$view = new App\Views\ConsultaView();

if (!empty($cpfData)) {
    $view->exibirResultados($cpfData); 
    echo "<script>toggleExportButton();</script>"; 
    
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Conecta Papem</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/conecta.css">
    <link rel="stylesheet" type="text/css" href="../css/header.css">
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link rel="stylesheet" type="text/css" href="../css/login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="https://www.marinha.mil.br/papem/sites/all/themes/contrib/govbr_theme/favicon.ico" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" href="../jqwidgets/jqwidgets/styles/jqx.base.css" />
    <link rel="stylesheet" href="../jqwidgets/jqwidgets/styles/jqx.classic.css" />
    <link rel="stylesheet" href="https://jqwidgets.com/public/jqwidgets/styles/jqx.base.css" type="text/css" />
    <link rel="stylesheet" href="../css/jqx.energyblue.css" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1234567890=" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
    <script>
    var nomeUsuario = "<?php echo $_SESSION['username']; ?>"; // Definindo a variável JavaScript com o valor da sessão PHP
</script>
</head>
<body>
    <?php include ('header.php'); ?> 
    <main>
        <div class="main-container">
            <form id="formConsulta" class="fade-in" enctype="multipart/form-data" method="post">
                <div id="digenv">
                    <input id="toggle-on" name="opcao" value="digitar" class="toggle toggle-left" onchange="mostrarOpcao('digitar')" type="radio">
                    <label for="toggle-on" class="btn1">Digitar Dados<img src="../img/digitando3.png" alt="Ícone" style="width: 30px; height: 30px;"></label>
                    <img src="../img/seta.png" alt="Ícone" style="width: 60px; height:30px;">
                    <input id="toggle-off" name="opcao" class="toggle toggle-right" value="arquivo" onchange="mostrarOpcao('arquivo')" type="radio">
                    <label for="toggle-off" class="btn1">Enviar Arquivo<img src="../img/arquivo2.png" alt="Ícone" style="width: 30px; height: 30px;">
                </div>


                <div>
                    <div class="select-container">
                        <select name="consultar" id="consultar" class="custom-select combobox-customizada" onchange="changeOption()">
                            <option class="custom-option" value="CPF">CPF</option>
                            <option class="custom-option" value="CNPJ">CNPJ</option>
                            <option class="custom-option" value="CNIS">CNIS</option>
                        </select>
                        <input type="hidden" id="opcao" name="opcao" value="">
                    </div>
                    <div id="cpfInput" style="display: none;">
                        <input id="documento" name="documento" type="text" placeholder="Insira o CPF">
                    </div>
                    <div id="arquivoInput" style="display: none;">
                        <label for="fileInput" class="custom-file-upload">
                            <img src="../img/xlsx.jpg" alt="Ícone" style="width:40px; height:40px; margin-right: 5px;">
                            <span id="fileLabelText">Importe seu arquivo</span>
                            <input type="file" name="fileInput" id="fileInput" accept=".xls, .xlsx">
                        </label>
                    </div>
                    <div class="file-button-container">
                        <label id="labelRemoveFileButton" style="display: none;" onclick="removeFile()">
                            Remover
                            <img id="removeFileButton" style="display: none;" src="../img/btn_x.png" alt="Remover">
                        </label>
                        <label id="labelChangeFileButton" style="display: none;" onclick="changeFile()">
                            Trocar
                            <img id="changeFileButton" style="display: none;" src="../img/btn_e.png" alt="Editar">
                        </label>
                    </div>
                    <div id="loadingIndicator" style="display: none;">
                        <span>Carregando</span>
                        <div class="dots">
                            <div class="dot"></div>
                            <div class="dot"></div>
                            <div class="dot"></div>
                        </div>
                    </div>
                    <div id="gridContainer"></div>
                    <div class="input-container"></div>
                    <button id="btn_consult" class="fade-in custom-button" style="display: none;" type="button" onclick="validateAndSubmit()">Consultar</button>
                  
                </div>
            </form>

            <div id="jq">
                <table id="resultTable" class="display">
                    <thead>
                        <tr id="tableHeader"></tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <div id="jqxgrid">
             
                    <table id="tabelaResultado"></table>
                </div>
             
            <button id="btn_exportar_xlsx" style="display: none;" onclick="exportarXLSX()">Exportar para XLSX</button>
        </div>
    </main>
</body>
</html>


<!-- JavaScript Libraries -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>

<!-- jqxWidgets Scripts -->
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxcore.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxdata.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxbuttons.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxscrollbar.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxmenu.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxgrid.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxgrid.selection.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxgrid.columnsresize.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxlistbox.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxdropdownlist.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxgrid.pager.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxgrid.sort.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxgrid.filter.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxgrid.storage.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxgrid.grouping.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxgrid.columnsreorder.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxgrid.aggregates.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxdata.export.js"></script>
<script type="text/javascript" src="https://jqwidgets.com/public/jqwidgets/jqxgrid.export.js"></script>

<!-- Custom Script -->
<script src="../js/consultar.js"></script>
<script src="../js/utils.js"></script>
 
