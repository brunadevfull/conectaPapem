
function selecionarConsulta() {
    // Limpa todos os campos sempre que a opção for alterada
    document.getElementById("cpfInput").value = "";
    document.getElementById("cnpjInput").value = "";
    document.getElementById("relacaoTrabalhistaInput").value = "";

    var selecionado = document.getElementById("consultar").value;

    // Oculta todos os contêineres
    document.getElementById("cpfContainer").style.display = "none";
    document.getElementById("cnpjContainer").style.display = "none";
    document.getElementById("relacaoTrabalhistaContainer").style.display = "none";

    // Exibe o contêiner correspondente à opção selecionada
    if (selecionado === "CPF") {
        document.getElementById("cpfContainer").style.display = "block";
        // Aplica máscara ao campo de CPF
        $('#cpfInput').inputmask('999.999.999-99');
    } else if (selecionado === "CNPJ") {
        document.getElementById("cnpjContainer").style.display = "block";
        // Aplica máscara ao campo de CNPJ
        $('#cnpjInput').inputmask('99.999.999/9999-99');
    } else if (selecionado === "RelacaoTrabalhista") {
        
        document.getElementById("relacaoTrabalhistaContainer").style.display = "block";
        $('#relacaoTrabalhistaInput').inputmask('999.999.999-99');
        
    }
}

function consultarCPF() {
    var cpf = document.getElementById("cpfInput").value;

    // Implemente a lógica para a chamada à API com o CPF fornecido

    // Exemplo de como imprimir o CPF na console
    console.log("Consultar CPF na API: " + cpf);

    // Limpa o campo de CPF após a consulta ser realizada
    document.getElementById("cpfInput").value = "";
}

function importar(){
    var cpfFile = document.getElementById("cpfFileInput").files[0]; // Pega o arquivo

}

function consultarCPF() {
    var cpf = document.getElementById("cpfInput").value;
    importar()

    console.log("Consultar CPF na API: " + cpf);
    //adicionar a lógica para processar o arquivo.
    
    document.getElementById("cpfInput").value = ""; // Limpa o campo de CPF
    document.getElementById("cpfFileInput").value = ""; // Limpa o campo do arquivo
}

function consultarCNPJ() {
    var cnpj = document.getElementById("cnpjInput").value;

    // Implemente a lógica para a chamada à API com o CNPJ fornecido

    // Exemplo de como imprimir o CNPJ na console
    console.log("Consultar CNPJ na API: " + cnpj);

    // Limpa o campo de CNPJ após a consulta ser realizada
    document.getElementById("cnpjInput").value = "";
}

function consultarRelacaoTrabalhista() {
    var relacaoTrabalhista = document.getElementById("relacaoTrabalhistaInput").value;

    // Implemente a lógica para a chamada à API com a relação trabalhista fornecida

    // Exemplo de como imprimir a relação trabalhista na console
    console.log("Consultar Relação Trabalhista na API: " + relacaoTrabalhista);

    // Limpa o campo de relação trabalhista após a consulta ser realizada
    document.getElementById("relacaoTrabalhistaInput").value = "";
}

/*Tabela_________________________________________________*/


// Simulação de dados da API
const fakeApiData = (page, pageSize) => {
    const data = [];
    for (let i = 0; i < pageSize; i++) {
        data.push({
            cpf: '000.000.000-00',
            nome: 'Nome ' + ((page - 1) * pageSize + i + 1),
            nomeSocial: 'Nome Social ' + ((page - 1) * pageSize + i + 1),
            situacaoCadastral: 'SITUAÇÃO CADASTRAL' + ((page - 1) * pageSize + i + 1),
            residenteExterior: 'RESIDENTE EXTERIOR' + ((page - 1) * pageSize + i + 1),
            codigoPaisExterior: 'CÓDIGO PAÍS EXTERIOR' + ((page - 1) * pageSize + i + 1),
            nomePaisExterior: 'NOME PAÍS EXTERIOR' + ((page - 1) * pageSize + i + 1),
            dataNascimento: 'DATA NASCIMENTO' + ((page - 1) * pageSize + i + 1),
            sexo: 'SEXO' + ((page - 1) * pageSize + i + 1),
            nomeNaturezaOcupacao: 'NOME NATUREZA OCUPAÇÃO' + ((page - 1) * pageSize + i + 1),
            nomeOcupacaoPrincipal: 'NOME OCUPAÇÃO PRINCIPAL' + ((page - 1) * pageSize + i + 1),
            exercicioOcupacao: 'EXERCÍCIO OCUPAÇÃO' + ((page - 1) * pageSize + i + 1),
            nomeUnidadeAdministrativa: 'NOME UNIDADE ADMINISTRATIVA' + ((page - 1) * pageSize + i + 1),
            tipoLogradouro: 'TIPO LOGRADOURO' + ((page - 1) * pageSize + i + 1),
            logradouro: 'LOGRADOURO' + ((page - 1) * pageSize + i + 1),
            numeroLogradouro: 'NÚMERO LOGRADOURO' + ((page - 1) * pageSize + i + 1),
            complemento: 'COMPLEMENTO' + ((page - 1) * pageSize + i + 1),
            bairro: 'BAIRRO' + ((page - 1) * pageSize + i + 1),
            cep: 'CEP' + ((page - 1) * pageSize + i + 1),
            uf: 'UF' + ((page - 1) * pageSize + i + 1),
            municipio: 'MUNICÍPIO' + ((page - 1) * pageSize + i + 1),
            ddd: 'DDD' + ((page - 1) * pageSize + i + 1),
            telefone: 'TELEFONE' + ((page - 1) * pageSize + i + 1),
            anoObito: 'ANO ÓBITO' + ((page - 1) * pageSize + i + 1),
        });
    }
    return data;
};

        let pageSize = 10; // Tamanho padrão da página
        let totalResults = 3; // Substitua pelo total de resultados da sua API

        document.getElementById('recordsPerPage').addEventListener('change', function() {
            pageSize = this.value;
            createPagination();
            loadData(1); // Recarregar com o novo tamanho de página
        });

        function createPagination() {
            const paginationDiv = document.getElementById('pagination');
            paginationDiv.innerHTML = ''; // Limpar botões existentes
            const totalPages = Math.ceil(totalResults / pageSize);

            for (let i = 1; i <= totalPages; i++) {
                const button = document.createElement('button');
                button.textContent = i;
                button.addEventListener('click', function() {
                    loadData(i);
                });
                paginationDiv.appendChild(button);
            }
        }

        function loadData(page) {
    const data = fakeApiData(page, pageSize); // Substitua por fetchData(page)
    const tbody = document.getElementById('data-table').getElementsByTagName('tbody')[0];
    tbody.innerHTML = '';
    data.forEach(item => {
        const row = tbody.insertRow();
        row.insertCell(0).textContent = item.cpf;
        row.insertCell(1).textContent = item.nome;
        row.insertCell(2).textContent = item.nomeSocial;
        row.insertCell(3).textContent = item.situacaoCadastral;
        row.insertCell(4).textContent = item.residenteExterior;
        row.insertCell(5).textContent = item.codigoPaisExterior;
        row.insertCell(6).textContent = item.nomePaisExterior;
        row.insertCell(7).textContent = item.dataNascimento;
        row.insertCell(8).textContent = item.sexo;
        row.insertCell(9).textContent = item.nomeNaturezaOcupacao;
        row.insertCell(10).textContent = item.nomeOcupacaoPrincipal;
        row.insertCell(11).textContent = item.exercicioOcupacao;
        row.insertCell(12).textContent = item.nomeUnidadeAdministrativa;
        row.insertCell(13).textContent = item.tipoLogradouro;
        row.insertCell(14).textContent = item.logradouro;
        row.insertCell(15).textContent = item.numeroLogradouro;
        row.insertCell(16).textContent = item.complemento;
        row.insertCell(17).textContent = item.bairro;
        row.insertCell(18).textContent = item.cep;
        row.insertCell(19).textContent = item.uf;
        row.insertCell(20).textContent = item.municipio;
        row.insertCell(21).textContent = item.ddd;
        row.insertCell(22).textContent = item.telefone;
        row.insertCell(23).textContent = item.anoObito;

        // Continue adicionando células para os demais campos
    });
}
        createPagination();
        loadData(1);


    document.getElementById('exportButton').addEventListener('click', exportTableToExcel);

    function exportTableToExcel() {
        let workbook = XLSX.utils.table_to_book(document.getElementById('data-table'));
        XLSX.writeFile(workbook, 'DadosExportados.xlsx');
    }

   
