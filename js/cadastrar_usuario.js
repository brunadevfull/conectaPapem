function cadastrarUsuario() {
    var formData = {
        username: document.forms["cadastroForm"]["username"].value,
        senha: document.forms["cadastroForm"]["senha"].value
    };

  
    $.ajax({
        type: 'POST',
        url: 'cadastrar_usuario.php',
        data: formData,
        success: function(response) {
        
            alert(response); 
        },
        error: function(xhr, status, error) {
            console.log('Erro na solicitação AJAX:');
            console.log('Status: ' + status);
            console.log('Erro: ' + error);
            console.log(xhr.responseText); 
        }
    });
}
