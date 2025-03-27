function consultar() {
    console.log('Função consultar chamada.');
    $('#loadingIndicator').show();
    $('#btn_consult').hide();

    var opcao = $('#consultar').val(); // Recupera a opção selecionada (CPF, CNPJ ou CNIS)
    var fileInput = $('#fileInput')[0].files[0];
    var documento = $('#documento').val(); // Recupera o documento digitado

    var formData = new FormData();
    formData.append('action', 'consultar'); // Define a ação
    formData.append('opcao', opcao); // Inclui a opção selecionada

    if (documento) {
        formData.append('documento', documento); // Adiciona o documento digitado
    } else if (fileInput) {
        formData.append('fileInput', fileInput); // Adiciona o arquivo carregado
    } else {
        console.log('Nenhum documento selecionado.');
        $('#loadingIndicator').hide();
        $('#btn_consult').show();
        return;
    }

    $.ajax({
        type: 'POST',
        url: '../api.php', // URL da API
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'text', // Recebe resposta como texto para análise manual
        success: function(response) {
            try {
                console.log('Resposta bruta da API:', response);
                var jsonResponse;

                // Verifica se a resposta é um array ou objeto JSON
                if (response.startsWith("[")) {
                    jsonResponse = JSON.parse(response);
                } else if (response.startsWith("{")) {
                    jsonResponse = JSON.parse(response);
                } else {
                    // Tenta extrair JSON válido da resposta
                    var jsonString = response.match(/(\{.*\}|\[.*\])/);
                    if (jsonString) {
                        jsonResponse = JSON.parse(jsonString[0]);
                    } else {
                        throw new Error('A resposta não é um JSON válido.');
                    }
                }

                if (jsonResponse.error) {
                    alert(jsonResponse.error);
                    $('#loadingIndicator').hide();
                    $('#btn_consult').show();
                    return;
                }

                console.log('Resposta processada:', jsonResponse);

                var formattedData = formatResponseData(jsonResponse, opcao);

 console.log("Dados formatados para o grid:", formattedData);

                updateGridForData(formattedData, opcao);
                toggleExportButton(formattedData); // Atualiza o botão de exportação
            } catch (e) {
                $('#gridMessage').html('Erro ao analisar a resposta JSON: ' + e.message);
            } finally {
                $('#loadingIndicator').hide();
                $('#btn_consult').show();
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro na requisição AJAX:', status, error);
            $('#loadingIndicator').hide();
            $('#btn_consult').show();
        }
    });
}


function validateAndSubmit() {
    var documentoInput = $('#documento').val().replace(/\D/g, '');
    var fileInput = $('#fileInput')[0].files[0];
    var selectedOption = $('#consultar').val();

    if (fileInput) {
        console.log('Arquivo selecionado, pulando validação de documento.');
        consultar();
        return;
    }

    console.log('Validando documento:', documentoInput);

    if ((selectedOption === 'CPF' && documentoInput.length !== 11) || 
        (selectedOption === 'CNPJ' && documentoInput.length !== 14) ||
        (selectedOption === 'CNIS' && documentoInput.length !== 11)) {
        alert('Por favor, insira um ' + selectedOption + ' válido.');
        return;
    }

    consultar();
}


function exportarXLSX() {
    var data = $("#jqxgrid").jqxGrid('getrows');
    var nomeUsuario = window.nomeUsuario;  // Obtém o nome do usuário da variável global
    var now = new Date();
    var dateTime = now.toLocaleString();

    // Mapeamento de nomes de colunas para CNPJ e CPF
    var columnMapping = {
        'dataSituacaoEspecial': 'Data Situação Especial',
        'ni': 'CNPJ',
        'tipoEstabelecimento': 'Tipo Estabelecimento',
        'nomeEmpresarial': 'Nome Empresarial',
        'nomeFantasia': 'Nome Fantasia',
        'situacaoCadastralCodigo': 'Situação Cadastral Código',
        'situacaoCadastralData': 'Situação Cadastral Data',
        'situacaoCadastralMotivo': 'Situação Cadastral Motivo',
        'naturezaJuridicaDescricao': 'Natureza Jurídica Descrição',
        'dataAbertura': 'Data de Abertura',
        'enderecoTipoLogradouro': 'Endereço Tipo Logradouro',
        'enderecoLogradouro': 'Endereço Logradouro',
        'enderecoNumero': 'Endereço Número',
        'enderecoComplemento': 'Endereço Complemento',
        'enderecoCep': 'Endereço CEP',
        'enderecoBairro': 'Endereço Bairro',
        'enderecoMunicipioCodigo': 'Endereço Município Código',
        'enderecoMunicipioDescricao': 'Endereço Município Descrição',
        'enderecoUf': 'Endereço UF',
        'enderecoPaisCodigo': 'Endereço País Código',
        'enderecoPaisDescricao': 'Endereço País Descrição',
        'municipioJurisdicaoCodigo': 'Município de Jurisdição Código',
        'municipioJurisdicaoDescricao': 'Município de Jurisdição Descrição',
        'telefones': 'Telefone',
        'correioEletronico': 'Correio Eletrônico',
        'capitalSocial': 'Capital Social',
        'situacaoEspecial': 'Situação Especial',
        'CPF': 'CPF',
        'Nome': 'Nome',
        'SituacaoCadastral': 'Situação Cadastral',
        'ResidenteExterior': 'Residente no Exterior',
        'NomeMae': 'Nome da Mãe',
        'DataNascimento': 'Data de Nascimento',
        'Sexo': 'Sexo',
        'NomeNaturezaOcupacao': 'Natureza da Ocupação',
        'UnidadeAdministrativa': 'Unidade Administrativa',
        'TipoLogradouro': 'Tipo de Logradouro',
        'Logradouro': 'Logradouro',
        'NumeroLogradouro': 'Número do Logradouro',
        'Complemento': 'Complemento',
        'Bairro': 'Bairro',
        'CEP': 'CEP',
        'UF': 'UF',
        'Municipio': 'Município',
        'DDD': 'DDD',
        'Telefone': 'Telefone',
        'AnoObito': 'Ano do Óbito',
        'empregador': 'Identificação do empregador responsável pela relação trabalhista',
        'dataAdmissao': 'Data de admissão no vínculo',
        'dataEncerramento': 'Data de encerramento do vínculo',
        'cbo': 'Classificação brasileira de ocupações',
        'ultimaRemuneracao': 'Competência e valor da última remuneração',
        'pendencias': 'Lista de pendências da relação trabalhista'
    };

    var ws_data = [
        ["Data e Hora", dateTime],
        ["Exportado por", nomeUsuario],
        [],
    ];

    if (data.length > 0) {
        // Filtrar e mapear colunas
        var headers = Object.keys(data[0]).filter(function(header) {
            return !['uid', 'boundindex', 'uniqueid', 'visibleindex'].includes(header);
        }).map(function(header) {
            return columnMapping[header] || header;
        });
        ws_data.push(headers);

        data.forEach(function(row) {
            var rowData = [];
            headers.forEach(function(header) {
                // Inverter mapeamento para obter o nome da propriedade original
                var originalHeader = Object.keys(columnMapping).find(key => columnMapping[key] === header) || header;
                rowData.push(row[originalHeader]);
            });
            ws_data.push(rowData);
        });
    }

    var ws = XLSX.utils.aoa_to_sheet(ws_data);
    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Resultados");

    XLSX.writeFile(wb, 'consulta_resultados.xlsx');
}

function formatResponseData(data, option) {
    if (option === 'CNPJ') {
        // Se a resposta não for um array, encapsule-a em um array
        if (!Array.isArray(data)) {
            data = [data];
        }

        return data.map(function(item) {
            return {
                'ni': item.ni || '',
                'tipoEstabelecimento': item.tipoEstabelecimento || '',
                'nomeEmpresarial': item.nomeEmpresarial || '',
                'nomeFantasia': item.nomeFantasia || '',
                'situacaoCadastralCodigo': item.situacaoCadastral ? item.situacaoCadastral.codigo : '',
                'situacaoCadastralData': item.situacaoCadastral ? item.situacaoCadastral.data : '',
                'situacaoCadastralMotivo': item.situacaoCadastral ? item.situacaoCadastral.motivo : '',
                'naturezaJuridicaDescricao': item.naturezaJuridica ? item.naturezaJuridica.descricao : '',
                'dataAbertura': item.dataAbertura || '',
                'enderecoTipoLogradouro': item.endereco ? item.endereco.tipoLogradouro : '',
                'enderecoLogradouro': item.endereco ? item.endereco.logradouro : '',
                'enderecoNumero': item.endereco ? item.endereco.numero : '',
                'enderecoComplemento': item.endereco ? item.endereco.complemento : '',
                'enderecoCep': item.endereco ? item.endereco.cep : '',
                'enderecoBairro': item.endereco ? item.endereco.bairro : '',
                'enderecoMunicipioCodigo': item.endereco && item.endereco.municipio ? item.endereco.municipio.codigo : '',
                'enderecoMunicipioDescricao': item.endereco && item.endereco.municipio ? item.endereco.municipio.descricao : '',
                'enderecoUf': item.endereco ? item.endereco.uf : '',
                'enderecoPaisCodigo': item.endereco && item.endereco.pais ? item.endereco.pais.codigo : '',
                'enderecoPaisDescricao': item.endereco && item.endereco.pais ? item.endereco.pais.descricao : '',
                'municipioJurisdicaoCodigo': item.municipioJurisdicao ? item.municipioJurisdicao.codigo : '',
                'municipioJurisdicaoDescricao': item.municipioJurisdicao ? item.municipioJurisdicao.descricao : '',
                'telefones': item.telefones ? item.telefones.map(function(tel) {
                    return `(${tel.ddd}) ${tel.numero}`;
                }).join(', ') : '',
                'correioEletronico': item.correioEletronico || '',
                'capitalSocial': item.capitalSocial || '',
                'situacaoEspecial': item.situacaoEspecial || '',
                'dataSituacaoEspecial': item.dataSituacaoEspecial || ''
            };
        });
    } else if (option === 'CPF') {
        return data; // Assume que a estrutura dos dados CPF já está correta
    } else

if (option === 'CNIS') {
    if (!data.relacoesTrabalhistas || !Array.isArray(data.relacoesTrabalhistas)) {
        console.error('Formato inesperado para dados do CNIS:', data);
        return [];
    }

    return data.relacoesTrabalhistas.map(function (item) {
    return {
                'tipoInscricao': item.empregador ? item.empregador.tipoInscricao || '' : '',
                'numeroInscricao': item.empregador ? item.empregador.numeroInscricao || '' : '',
                'dataAdmissao': item.dataAdmissao || '',
                'dataEncerramento': item.dataEncerramento || '',
                'cboCodigo': item.cbo ? item.cbo.codigo || '' : '',
                'cboDescricao': item.cbo ? item.cbo.descricao || '' : '',
                'ultimaRemuneracao': item.ultimaRemuneracao ? item.ultimaRemuneracao.competencia || '' : '',
                'pendencias': item.pendencias && item.pendencias.length > 0
                    ? item.pendencias.map(function (pendencia) {
                        return `${pendencia.codigo}: ${pendencia.descricao}`;
                    }).join(', ')
                    : 'Sem pendências'
        };
    });
}
}


function updateGridForData(data, option) {
  console.log("Dados recebidos para o grid:", data);

    if (!Array.isArray(data) || data.length === 0) {
        $('#gridMessage').html('Nenhum dado encontrado ou consulta inválida.');
        clearAndHideGrid();
        return;
    }

    $("#jqxgrid").show();
    var columns = getColumnsForOption(option);
console.log("Colunas geradas para o grid:", columns);

    // Cria uma fonte de dados jqxWidgets
    var source = {
        localdata: data,
        datatype: "json",
        datafields: columns.map(function(column) {
            return { name: column.datafield, type: 'string' }; // Assumimos que todos os campos são strings
        })
    };

    var dataAdapter = new $.jqx.dataAdapter(source);

 

    $("#jqxgrid").jqxGrid({
        width: '1000',
        source: dataAdapter,
        height: 250,
        columnsresize: true,
        autoheight: true,
        pageable: true,
        sortable: true,
        pagerButtonsCount: 10,
        theme: 'energyblue',
        filterable: true,
        columns: columns
    });
   
    $("#jqxgrid").jqxGrid('autoresizecolumns');

    console.log("Grid atualizado com as colunas:", columns);
    toggleExportButton(data); // Passar os dados para a função toggleExportButton
}





function getColumnsForOption(option) {
    if (option === 'CPF') {
        return [
            { text: 'CPF', datafield: 'CPF', width: '10%' },
            { text: 'Nome', datafield: 'Nome', width: '20%' },
            { text: 'Situação Cadastral', datafield: 'SituacaoCadastral', width: '10%' },
            { text: 'Residente Exterior', datafield: 'ResidenteExterior', width: '10%' },
            { text: 'Nome da Mãe', datafield: 'NomeMae', width: '20%' },
            { text: 'Data de Nascimento', datafield: 'DataNascimento', width: '10%' },
            { text: 'Sexo', datafield: 'Sexo', width: '5%' },
            { text: 'Natureza Ocupação', datafield: 'NomeNaturezaOcupacao', width: '15%' },
            { text: 'Unidade Administrativa', datafield: 'UnidadeAdministrativa', width: '15%' },
            { text: 'Tipo Logradouro', datafield: 'TipoLogradouro', width: '10%' },
            { text: 'Logradouro', datafield: 'Logradouro', width: '15%' },
            { text: 'Número Logradouro', datafield: 'NumeroLogradouro', width: '10%' },
            { text: 'Complemento', datafield: 'Complemento', width: '10%' },
            { text: 'Bairro', datafield: 'Bairro', width: '10%' },
            { text: 'CEP', datafield: 'CEP', width: '10%' },
            { text: 'UF', datafield: 'UF', width: '5%' },
            { text: 'Município', datafield: 'Municipio', width: '10%' },
            { text: 'DDD', datafield: 'DDD', width: '5%' },
            { text: 'Telefone', datafield: 'Telefone', width: '10%' },
            { text: 'Ano Óbito', datafield: 'AnoObito', width: '10%' }
        ];
    } else if (option === 'CNPJ') {
        return [
            { text: 'CNPJ', datafield: 'ni', width: '15%' },
            { text: 'Tipo Estabelecimento', datafield: 'tipoEstabelecimento', width: '15%' },
            { text: 'Nome Empresarial', datafield: 'nomeEmpresarial', width: '20%' },
            { text: 'Nome Fantasia', datafield: 'nomeFantasia', width: '20%' },
            { text: 'Situação Cadastral Código', datafield: 'situacaoCadastralCodigo', width: '10%' },
            { text: 'Situação Cadastral Data', datafield: 'situacaoCadastralData', width: '10%' },
            { text: 'Situação Cadastral Motivo', datafield: 'situacaoCadastralMotivo', width: '15%' },
            { text: 'Natureza Jurídica Descrição', datafield: 'naturezaJuridicaDescricao', width: '15%' },
            { text: 'Data de Abertura', datafield: 'dataAbertura', width: '10%' },
            { text: 'Endereço Tipo Logradouro', datafield: 'enderecoTipoLogradouro', width: '10%' },
            { text: 'Endereço Logradouro', datafield: 'enderecoLogradouro', width: '15%' },
            { text: 'Endereço Número', datafield: 'enderecoNumero', width: '10%' },
            { text: 'Endereço Complemento', datafield: 'enderecoComplemento', width: '10%' },
            { text: 'Endereço CEP', datafield: 'enderecoCep', width: '10%' },
            { text: 'Endereço Bairro', datafield: 'enderecoBairro', width: '10%' },
            { text: 'Endereço Município Código', datafield: 'enderecoMunicipioCodigo', width: '10%' },
            { text: 'Endereço Município Descrição', datafield: 'enderecoMunicipioDescricao', width: '15%' },
            { text: 'Endereço UF', datafield: 'enderecoUf', width: '5%' },
            { text: 'Endereço País Código', datafield: 'enderecoPaisCodigo', width: '10%' },
            { text: 'Endereço País Descrição', datafield: 'enderecoPaisDescricao', width: '15%' },
            { text: 'Município de Jurisdição Código', datafield: 'municipioJurisdicaoCodigo', width: '10%' },
            { text: 'Município de Jurisdição Descrição', datafield: 'municipioJurisdicaoDescricao', width: '15%' },
            { text: 'Telefone', datafield: 'telefones', width: '15%' },
            { text: 'Correio Eletrônico', datafield: 'correioEletronico', width: '15%' },
            { text: 'Capital Social', datafield: 'capitalSocial', width: '10%' },
            { text: 'Situação Especial', datafield: 'situacaoEspecial', width: '10%' },
            { text: 'Data Situação Especial', datafield: 'dataSituacaoEspecial', width: '10%' }
        ];
    } else if (option === 'CNIS') {
        return [
            { text: 'Tipo de Inscrição', datafield: 'tipoInscricao', width: '15%' },
            { text: 'Número de Inscrição', datafield: 'numeroInscricao', width: '20%' },
            { text: 'Data de Admissão', datafield: 'dataAdmissao', width: '15%' },
            { text: 'Data de Encerramento', datafield: 'dataEncerramento', width: '15%' },
            { text: 'CBO Código', datafield: 'cboCodigo', width: '10%' },
            { text: 'CBO Descrição', datafield: 'cboDescricao', width: '25%' },
            { text: 'Última Remuneração', datafield: 'ultimaRemuneracao', width: '10%' },
            { text: 'Pendências', datafield: 'pendencias', width: '20%' }
        ];
    }

    return [];
}

