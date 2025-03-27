<header>
    <div class="brasao-container">
        <img src="/conectaPapem/img/brasao.png" alt="Brasão PAPEM" width="90"/>
        <div class="titulo-container">
            <span class="span-subtitulo-marinha">Marinha do Brasil</span>
            <h1>Pagadoria de Pessoal da Marinha</h1>
            <span class="span-subtitulo-lema">"ORDEM, PRONTIDÃO E REGULARIDADE"</span>
        </div>
        <div id="bem-vindo">
            <?php
            // Verificar se o usuário está logado
            if (isset($nomeUsuario)) {
                echo 'Bem-vindo, ' . $nomeUsuario . '!';
                echo '<button type="button" onclick="logout()">Logout</button>';
            }
            ?>
        </div>
    </div>
</header>