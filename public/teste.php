<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Toggle Switch</title>
<style>

body,html {
    background: #efefef;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100%;
    z-index: -1;
}
.btn1{
    border: 3px solid #1a1a1a;
    display: inline-block;
    padding: 10px;
    position: relative;
    text-align: center;
    transition: background 600ms ease, color 600ms ease;
}

input[type="radio"].toggle {
    display: none;
}

.toggle + label{
    cursor: pointer;
    min-width: 60px;
    display: inline-block;
    padding: 10px;
    text-align: center;
    position: relative;
}

.toggle-left + label:after {
    content: "";
    background: #1a1a1a;
    position: absolute;
    top: 0px;
    right: 0px; /* Inicialmente, a caixa preta começa do lado direito */
    width: 0; /* Inicialmente, a largura é 0 */
    height: 39px; /* Ajuste para a altura do retângulo */
    z-index: -1;
    transition: width 0.4s ease, right 0.4s ease; /* Adiciona transição suave */
}

.toggle-right + label:after {
    content: "";
    background: #1a1a1a;
    position: absolute;
    top: 0px;
    left: 0px; /* Inicialmente, a caixa preta começa do lado esquerdo */
    width: 0px; /* Inicialmente, a largura é 0 */
    height: 39px; /* Ajuste para a altura do retângulo */
    z-index: -1;
    transition: width 0.4s ease, left 0.4s ease; /* Adiciona transição suave */
}

/* Adicione a cor branca aos pseudoelementos dos botões Yes e No */
.toggle-left + label:after,
.toggle-right + label:after {
    color: white; /* Define a cor da letra como branca */
}

input[type="radio"].toggle-left:checked + label:after {
    width: calc(100% - 0px - 0px); /* Ajuste para a posição final do retângulo no botão "Yes" */
}

input[type="radio"].toggle-right:checked + label:after {
    width: calc(100% - 0px - 0px); /* Ajuste para a posição final do retângulo no botão "No" */
}
</style>
</head>
<body>

<input id="toggle-on" class="toggle toggle-left" name="toggle" value="false" type="radio" >
<label for="toggle-on" class="btn1">Yes</label>
<input id="toggle-off" class="toggle toggle-right" name="toggle" value="true" type="radio">
<label for="toggle-off" class="btn1">No</label>

<script>
document.querySelectorAll('.toggle').forEach(function(toggle) {
    toggle.addEventListener('change', function() {
        // Atualiza a aparência dos botões ao alternar entre eles
        document.querySelectorAll('.toggle + label').forEach(function(label) {
            label.style.color = '#1a1a1a'; // Defina a cor do texto como preto para todos os botões
        });
        
        if (this.checked) {
            this.nextElementSibling.style.color = '#fff'; // Defina a cor do texto como branco para o botão selecionado
        }
    });
});
</script>

</body>
</html>
