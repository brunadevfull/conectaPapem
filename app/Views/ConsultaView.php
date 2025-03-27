<?php

namespace App\Views;

class ConsultaView { 
    
    public function exibirMensagemErro($mensagem) {
        echo "<script>alert(" . json_encode($mensagem) . ")</script>";
    }

    public function exibirResultados($cpfData) {
         
      
        if (!empty($cpfData)) {
            foreach ($cpfData as $cpfEntry) {
                
                foreach ($cpfEntry as $key => $value) {
                    echo '<div>';
                    echo '<strong>' . $key . ':</strong> ' . $value . '<br>';
                    echo '</div>';
                }
               
            }
        } else {
            echo "Nenhum dado de CPF encontrado";
        }

        echo '</div>';
    }
}