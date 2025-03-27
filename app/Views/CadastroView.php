<?php

namespace App\Views;

class CadastroView {
     public function exibirMensagemSucesso($mensagem, $redirectUrl) {
        echo "$mensagem";
        exit(); 
    }
    public function exibirMensagemErro($mensagem) {
        echo "$mensagem";
    }
}
